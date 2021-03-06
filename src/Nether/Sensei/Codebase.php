<?php

namespace Nether\Sensei;

use Nether;
use Nether\Sensei\Meta;

use JsonSerializable;
use Nether\Object\Datastore;
use Nether\Sensei\Inspectors\AbstractInspector;
use Nether\Sensei\Inspectors\NamespaceInspector;
use Nether\Sensei\Inspectors\ClassInspector;

#[Meta\Info('Describes the entire codebase that was scanned.')]
class Codebase
extends Nether\Object\Prototype
implements JsonSerializable {

	#[Nether\Object\Meta\PropertyObjectify]
	public Nether\Object\Datastore
	$Namespaces;

	#[Nether\Object\Meta\PropertyObjectify]
	public Nether\Object\Datastore
	$Interfaces;

	#[Nether\Object\Meta\PropertyObjectify]
	public Nether\Object\Datastore
	$Traits;

	#[Nether\Object\Meta\PropertyObjectify]
	public Nether\Object\Datastore
	$Classes;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	JsonSerialize():
	string {

		return json_encode($this);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Render(string $Dir):
	static {

		$Theme = new Theme('themes/default');
		$Old = NULL;
		$New = NULL;

		$this
		->RenderIndex($Theme, $Dir)
		->RenderNamespaces($Theme, $Dir)
		->RenderClasses($Theme, $Dir);

		////////

		$Share = [
			'share/bootstrap.min.css'     => "{$Dir}/share/bootstrap.min.css",
			'share/theme.css'             => "{$Dir}/share/theme.css",
			'share/bootstrap-icons.css'   => "{$Dir}/share/bootstrap-icons.css",
			'share/bootstrap-icons.woff'  => "{$Dir}/share/bootstrap-icons.woff",
			'share/bootstrap-icons.woff2' => "{$Dir}/share/bootstrap-icons.woff2"
		];

		Nether\Sensei\Util::MkDir("{$Dir}/share");

		foreach($Share as $Old => $New)
		copy($Theme->GetPath($Old),$New);

		////////

		return $this;
	}

	protected function
	RenderIndex(Theme $Theme, string $Dir):
	static {

		$Outfile = join(
			DIRECTORY_SEPARATOR,
			[ $Dir, "index.html" ]
		);

		(new ThemeEngine($Theme))
		->Set('PageDepth',1)
		->Area(
			"types/index",
			Codebase: $this
		)
		->Render($Outfile);

		return $this;
	}

	protected function
	RenderNamespaces(Theme $Theme, string $Dir):
	static {

		$NS = NULL;

		foreach($this->Namespaces as $NS) {
			$Outfile = join(
				DIRECTORY_SEPARATOR,
				[ $Dir, Util::GetNamespaceName("{$NS->Name}/index.html") ]
			);

			(new ThemeEngine($Theme))
			->Set('PageDepth',(substr_count($NS->Name,'\\')+1))
			->Area(
				"types/namespace",
				Namespace: $NS,
				Codebase: $this
			)
			->Render($Outfile);
		}


		return $this;
	}

	protected function
	RenderClasses(Theme $Theme, string $Dir):
	static {

		($this->Classes)
		->Each(function(ClassInspector $Class) use($Theme,$Dir){
			$Outfile = join(
				DIRECTORY_SEPARATOR,
				[ $Dir, $Class->GetURI('.class.html') ]
			);

			(new ThemeEngine($Theme))
			->Set('PageDepth',substr_count($Class->Name,'\\'))
			->Area(
				"types/{$Class->InspectorType}",
				Class: $Class,
				Codebase: $this
			)
			->Render($Outfile);

			return;
		});

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	BakeClasses(Datastore $Classes):
	static{

		($this->Classes)
		->MergeRight(
			$Classes
			->MapKeys(
				fn($Key, $Val)
				=> [ $Val->GetNiceName() => $Val ]
			)
			->GetData()
		);

		return $this;
	}

	public function
	BakeNamespaces():
	static {

		// note: namespaces are not really real in php so baking the
		// namespaces must be done after baking other real things like
		// classes, traits, and interfaces.

		$Data = [];
		$Class = NULL;
		$Key = NULL;

		// notice the namespaces the classes live in.

		foreach($this->Classes as $Class) {
			$Key = $Class->GetDirName();

			if(!isset($Data[$Key]))
			$Data[$Key] = new NamespaceInspector($Key);

			$Data[$Key]->Classes->Push($Class, $Class->GetNiceName());
		}

		// notice any namespaces that existed but contained nothing
		// within them to cause them to be indexed. like your top level
		// vendor namespace.

		$NS = NULL;  // NamespaceInspector
		$NSE = NULL; // array(Namespace Name Exploded)
		$NSS = NULL; // string(Namespace basename)
		$NSP = '';   // string(Namespace build path)
		$NSK = NULL; // string(Namespace full generated path)

		foreach($Data as $Key => $NS) {
			$NSE = explode('\\', Util::GetNamespaceName($NS->Name));
			$NSP = '';

			//var_dump($NS->Name, $NSE);

			foreach($NSE as $NSS) {
				$NSK = "{$NSP}{$NSS}";

				if(!isset($Data[$NSK]))
				$Data[$NSK] = new NamespaceInspector($NSK);

				$NSP .= "{$NSS}\\";
			}
		}

		//print_r($Data['Nether']->Namespaces->GetData());

		($this->Namespaces)
		->SetData($Data)
		->Sort(
			fn(NamespaceInspector $A, NamespaceInspector $B)
			=> ($A->Name <=> $B->Name)
		);

		// notice the namespaces about their structures. nesting the direct
		// children into eachother.

		($this->Namespaces)
		->Each(function($NS) {

			$Dir = $NS->GetDirName();
			$NSS = NULL;

			if($Dir && $this->Namespaces->HasKey($Dir)) {
				$NSS = $this->Namespaces->Get($Dir);

				if(!$NSS->Namespaces->HasKey($NS->Name))
				$NSS->Namespaces->Shove(
					$NS->Name,
					$NS
				);
			}

			return;
		});

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasClasslike(string $Key):
	bool {

		if(!str_starts_with($Key,'\\'))
		$Key = "\\{$Key}";

		$Output = (
			FALSE
			?: $this->Classes->HasKey($Key)
			?: $this->Interfaces->HasKey($Key)
			?: $this->Traits->HasKey($Key)
		);

		return $Output;
	}

	public function
	GetClasslike(string $Key):
	?ClassInspector {

		$Output = (
			NULL
			?? $this->Classes->Get($Key)
			?? $this->Interfaces->Get($Key)
			?? $this->Traits->Get($Key)
		);

		return $Output;
	}

}

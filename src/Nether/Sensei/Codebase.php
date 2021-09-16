<?php

namespace Nether\Sensei;

use Nether;

use JsonSerializable;
use Nether\Sensei\Inspectors\NamespaceInspector;
use Nether\Sensei\Inspectors\ClassInspector;
use Nether\Object\Datastore;

class Codebase
extends Nether\Object\Prototype
implements JsonSerializable {

	const
	SourceFresh = 1,
	SourceCache = 2;

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
			'share/bootstrap.min.css' => "{$Dir}/share/bootstrap.min.css",
			'share/theme.css'         => "{$Dir}/share/theme.css"
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
				[ $Dir, $Class->GetNamespacedPath('.class.html') ]
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
		->MergeRight($Classes->GetData());

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
			$Key = "\\{$Class->GetNamespaceName()}";

			if(!isset($Data[$Key]))
			$Data[$Key] = new NamespaceInspector($Key);

			$Data[$Key]->Classes->Push($Class,$Class->Name);
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
			$NSE = explode('\\',Util::GetNamespaceName($NS->Name));
			$NSP = '';

			foreach($NSE as $NSS) {
				$NSK = "{$NSP}\\{$NSS}";

				if(!isset($Data[$NSK]))
				$Data[$NSK] = new NamespaceInspector($NSK);

				$NSP .= "\\{$NSS}";
			}
		}

		($this->Namespaces)
		->SetData($Data)
		->Sort(
			fn(NamespaceInspector $A, NamespaceInspector $B)
			=> ($A->Name <=> $B->Name)
		);

		return $this;
	}

}

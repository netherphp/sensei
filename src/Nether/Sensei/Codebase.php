<?php

namespace Nether\Sensei;

use Nether;

use JsonSerializable;
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
			[ $Dir, "index.phtml" ]
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

		$Namespaces = $this->BakeNamespaces();
		$NS = NULL;

		foreach($Namespaces as $NS) {
			$Outfile = join(
				DIRECTORY_SEPARATOR,
				[ $Dir, Util::GetNamespaceName("{$NS}/index.html") ]
			);

			(new ThemeEngine($Theme))
			->Set('PageDepth',(substr_count($NS,'\\')+1))
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
	BakeNamespaces():
	Datastore {

		$Data = [];
		$Class = NULL;

		foreach($this->Classes as $Class)
		$Data["\\{$Class->GetNamespaceName()}"] = TRUE;

		$Output = new Datastore(array_keys($Data));
		$Output->Sort(fn($A,$B)=> ($A <=> $B));

		return $Output;
	}

}

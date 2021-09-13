<?php

namespace Nether\Sensei;

use Nether;

use JsonSerializable;
use Nether\Sensei\Inspectors\ClassInspector;

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

		$this->RenderClasses($Theme,$Dir);

		$Share = [
			'share/theme.css' => "{$Dir}/share/theme.css",
			'share/bootstrap.min.css' => "{$Dir}/share/bootstrap.min.css"
		];

		Nether\Sensei\Util::MkDir("{$Dir}/share");

		foreach($Share as $Old => $New)
		copy($Theme->GetPath($Old),$New);

		return $this;
	}

	protected function
	RenderClasses(Theme $Theme, string $Dir):
	static {

		($this->Classes)
		->Each(function(ClassInspector $Class) use($Theme,$Dir){
			$Outfile = join(
				DIRECTORY_SEPARATOR,
				[ $Dir, $Class->GetNamespacedPath('.html') ]
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

}

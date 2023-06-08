<?php

namespace Nether\Sensei;

use Nether;

use SplFileInfo;
use Exception;

class Theme
extends Nether\Common\Prototype {

	public string
	$Path;

	public function
	__Construct(string $Path) {

		$this->Path = (new SplFileInfo($Path))->GetRealPath();

		if(!file_exists($this->Path))
		throw new Exception("{$this->Path} not found");

		return;
	}

	public function
	GetPath(string $To):
	string {

		return join(DIRECTORY_SEPARATOR,[$this->Path,$To]);
	}

	public function
	GetTypePath(string $To):
	string {

		return join(
			DIRECTORY_SEPARATOR,
			[ $this->Path, 'types', "{$To}.phtml" ]
		);
	}

}

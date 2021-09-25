<?php

namespace Nether\Sensei\Inspectors;

use Nether;

abstract class AbstractInspector
extends Nether\Object\Prototype {

	public string
	$Name;

	public string
	$InterfaceType;

	public string
	$FileExt;

	public ?Nether\Sensei\Meta\Info
	$Info = NULL;

	//abstract protected function
	//Inspect():
	//static;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Nether\Sensei\Meta\Info('like basename() but for namespaced paths that works regardless of os.')]
	public function
	GetBaseName():
	string {

		$Sploded = explode('\\',$this->Name);

		end($Sploded);

		return current($Sploded);
	}

	#[Nether\Sensei\Meta\Info('like dirname() but for namespaced paths that works regardless of os.')]
	public function
	GetDirName():
	?string {

		$Sploded = explode('\\',$this->Name);

		if(count($Sploded) === 1)
		return NULL;

		array_pop($Sploded);
		return join('\\',$Sploded);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetFinalURI(?string $Bookmark=NULL):
	string {

		return sprintf(
			'%s%s%s',
			str_replace('\\', '/', $this->Name),
			$this->FileExt,
			($Bookmark ? "#{$Bookmark}" : '')
		);
	}

}

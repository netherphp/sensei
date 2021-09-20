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

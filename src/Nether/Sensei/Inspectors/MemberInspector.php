<?php

namespace Nether\Sensei\Inspectors;

use Nether;

use ReflectionProperty;

class MemberInspector
extends Nether\Object\Prototype {

	public string
	$Name;

	public bool
	$Static = FALSE;

	public bool
	$Public = TRUE;

	public bool
	$Protected = FALSE;

	public bool
	$Private = FALSE;

	public bool
	$Inherited = FALSE;

	public string
	$Type;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Name) {
		parent::__Construct();

		$this->Name = $Name;

		$this->Inspect();
		return;
	}

	protected function
	Inspect():
	static {

		return $this;
	}

	public function
	GetAccessWord():
	string {

		return match(TRUE){
			$this->Public    => 'public',
			$this->Protected => 'protected',
			$this->Private   => 'private'
		};
	}

	public function
	GetAccessSortable():
	string {

		$Sort = match(TRUE){
			$this->Public    => 1,
			$this->Protected => 2,
			$this->Private   => 3
		};

		if($this->Static)
		$Sort += 3;

		//if($this->Inherited)
		//$Sort += 6;

		return $Sort;
	}

}

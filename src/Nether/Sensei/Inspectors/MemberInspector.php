<?php

namespace Nether\Sensei\Inspectors;

use Nether;

use Nether\Sensei\Inspectors\ClassInspector;
use Nether\Sensei\Util;

class MemberInspector
extends AbstractInspector {

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

	public string
	$Type;

	public ?string
	$Inherited = NULL;

	public ?string
	$Implement = NULL;

	public ?string
	$Override = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Name, ClassInspector $Class) {
		parent::__Construct();

		$this->Name = $Name;

		$this->Inspect($Class);
		return;
	}

	protected function
	Inspect(ClassInspector $Class):
	static {

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

	public function
	GetCallBase(bool $Verbose=FALSE):
	string {

		if($Verbose) {
			if($this->Override)
			return Util::GetNamespaceName($this->Override);
		}

		if($this->Static)
		return 'static';

		return '$this';
	}

	public function
	GetCallDelim():
	string {

		if($this->Static)
		return '::';

		return '->';
	}

	public function
	GetCallMock(bool $Verbose=FALSE):
	string {

		return sprintf(
			'%s%s%s',
			$this->GetCallBase($Verbose),
			$this->GetCallDelim(),
			$this->GetName()
		);
	}

	public function
	GetCallMockInterface(bool $Verbose=FALSE):
	string {

		return sprintf(
			'%s%s%s',
			$this->Implement ? $this->Implement : $this->GetCallBase(FALSE),
			$this->GetCallDelim(),
			$this->GetName()
		);
	}

	public function
	GetCallMockOverride(bool $Verbose=FALSE):
	string {

		return sprintf(
			'%s%s%s',
			$this->Override ? $this->Override : $this->GetCallBase(FALSE),
			$this->GetCallDelim(),
			$this->GetName()
		);
	}

	public function
	GetName():
	string {

		if(str_contains($this->Name,'::'))
		return explode('::',$this->Name)[1];

		return $this->Name;
	}

	public function
	GetNamespace():
	string {

		if(str_contains($this->Name,'::'))
		return explode('::',$this->Name)[0];

		return $this->Name;
	}

	public function
	IsInherited():
	bool {

		return $this->Inherited ? TRUE : FALSE;
	}

	public function
	IsDefinedHere():
	bool {

		return $this->Inherited === NULL;
	}

}

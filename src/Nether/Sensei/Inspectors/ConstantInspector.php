<?php

namespace Nether\Sensei\Inspectors;

use ReflectionClassConstant;

class ConstantInspector
extends MemberInspector {

	public function
	GetName():
	string {

		if(str_contains($this->Name,'::'))
		return explode('::',$this->Name)[1];

		return $this->Name;
	}

	protected function
	Inspect():
	static {

		$Info = new ReflectionClassConstant(...explode('::',$this->Name));

		$this->Static = TRUE;
		$this->Public = $Info->IsPublic();
		$this->Protected = $Info->IsProtected();
		$this->Private = $Info->IsPrivate();
		$this->Type = gettype($Info->GetValue());

		return $this;
	}

}

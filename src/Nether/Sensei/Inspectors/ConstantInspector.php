<?php

namespace Nether\Sensei\Inspectors;

use ReflectionClassConstant;

class ConstantInspector
extends MemberInspector {

	protected function
	Inspect(ClassInspector $Class):
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

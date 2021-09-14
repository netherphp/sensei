<?php

namespace Nether\Sensei\Inspectors;

use ReflectionProperty;

class PropertyInspector
extends MemberInspector {

	protected function
	Inspect():
	static {

		list($Class,$PName) = explode('::',$this->Name);

		$Info = new ReflectionProperty($Class,$PName);
		$Type = $Info->GetType();

		$this->Inherited = $Info->GetDeclaringClass()->GetName() !== ltrim($Class,'\\');
		$this->Static = $Info->IsStatic();
		$this->Public = $Info->IsPublic();
		$this->Protected = $Info->IsProtected();
		$this->Private = $Info->IsPrivate();
		$this->Type = $Type ? $Type->GetName() : 'mixed';

		return $this;
	}

	public function
	GetName():
	string {

		if(str_contains($this->Name,'::'))
		return explode('::',$this->Name)[1];

		return $this->Name;
	}

}

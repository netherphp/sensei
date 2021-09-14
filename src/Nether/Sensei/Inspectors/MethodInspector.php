<?php

namespace Nether\Sensei\Inspectors;

use Nether;

use ReflectionMethod;
use Nether\Object\Datastore;

class MethodInspector
extends MemberInspector {

	public bool
	$Final = FALSE;

	public bool
	$Abstract = FALSE;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Args;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	Inspect():
	static {

		list($Class,$PName) = explode('::',$this->Name);

		$Info = new ReflectionMethod($Class,$PName);
		$Return = $Info->GetReturnType();
		$Arg = NULL;

		$this->Inherited = $Info->GetDeclaringClass()->GetName() !== ltrim($Class,'\\');
		$this->Final = $Info->IsFinal();
		$this->Abstract = $Info->IsAbstract();
		$this->Static = $Info->IsStatic();
		$this->Public = $Info->IsPublic();
		$this->Protected = $Info->IsProtected();
		$this->Private = $Info->IsPrivate();
		$this->Type = $Return ? $Return->GetName() : 'mixed';

		foreach($Info->GetParameters() as $Arg)
		$this->Args->Push(new ArgumentInspector(
			$this->Name,
			$Arg->GetName()
		));

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

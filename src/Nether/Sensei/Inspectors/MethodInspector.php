<?php

namespace Nether\Sensei\Inspectors;

use Nether;
use Nether\Sensei\Meta;

use ReflectionMethod;
use Nether\Object\Datastore;
use Nether\Sensei\Inspectors\ClassInspector;
use Nether\Sensei\Util;

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
	Inspect(ClassInspector $Class):
	static {

		list($CName,$PName) = explode('::',$this->Name);

		$Info = new ReflectionMethod($CName,$PName);
		$Return = $Info->GetReturnType();
		$Arg = NULL;

		$this->Inherited = "\\{$Info->GetDeclaringClass()->GetName()}";
		$this->Final = $Info->IsFinal();
		$this->Abstract = $Info->IsAbstract();
		$this->Static = $Info->IsStatic();
		$this->Public = $Info->IsPublic();
		$this->Protected = $Info->IsProtected();
		$this->Private = $Info->IsPrivate();
		$this->Type = $Return ? $Return->GetName() : 'mixed';

		if($this->Inherited === $this->GetNamespace())
		$this->Inherited = NULL;

		foreach($Info->GetAttributes(Meta\Info::class) as $Arg)
		$this->Info = $Arg->NewInstance();

		// prepare argument list.

		foreach($Info->GetParameters() as $Arg)
		$this->Args->Push(new ArgumentInspector(
			$this->Name,
			$Arg->GetName()
		));

		// determine if implementing an interface method.

		if($Class->Interfaces->Count()) {
			foreach($Class->Interfaces as $Arg) {
				if(method_exists("{$Arg}","{$this->GetName()}"))
				$this->Implement = $Arg;
			}
		}

		// determine if overrriding a parent method.

		if($this->Inherited) {
			$C = $Info->GetDeclaringClass();

			do {
				if($C->HasMethod($this->GetName())) {
					$this->Override = "\\{$C->GetName()}";
					break;
				}
			}
			while($C = $C->GetParentClass());
		}

		return $this;
	}

}

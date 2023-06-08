<?php

namespace Nether\Sensei\Inspectors;

use Nether;
use Nether\Sensei\Meta;

use ReflectionMethod;
use Nether\Common\Datastore;
use Nether\Sensei\Inspectors\ClassInspector;
use Nether\Sensei\Util;
use ReflectionNamedType;

class MethodInspector
extends MemberInspector {

	public bool
	$Final = FALSE;

	public bool
	$Abstract = FALSE;

	#[Nether\Common\Meta\PropertyObjectify]
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
		$Parent = $Info->GetDeclaringClass();
		$Arg = NULL;

		$this->Inherited = $Parent->GetName();
		$this->Final = $Info->IsFinal();
		$this->Abstract = $Info->IsAbstract();
		$this->Static = $Info->IsStatic();
		$this->Public = $Info->IsPublic();
		$this->Protected = $Info->IsProtected();
		$this->Private = $Info->IsPrivate();
		$this->Type = $Return instanceof ReflectionNamedType ? $Return->GetName() : 'mixed';

		if($Parent->GetFilename())
		$this->Info = Nether\Sensei\Util::GetNetherDocFromFileLine(
			$Parent->GetFilename(),
			$Info->GetStartLine()
		);

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

		// check if this is overriding a version from a parent class.

		if($this->Inherited !== NULL && $this->Inherited === $CName) {
			$C = $Info->GetDeclaringClass();

			do {
				if($C->GetName() === $CName)
				continue;

				if($C->HasMethod($this->GetName())) {
					$this->Override = $C->GetName();
					break;
				}
			}
			while($C = $C->GetParentClass());
		}

		// check that it really is inherited tho.

		if($this->Inherited === $CName)
		$this->Inherited = NULL;

		return $this;
	}

}

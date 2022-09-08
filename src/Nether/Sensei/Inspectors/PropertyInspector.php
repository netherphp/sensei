<?php

namespace Nether\Sensei\Inspectors;
use Nether;

use Nether\Sensei\Meta;

use ReflectionProperty;
use Nether\Sensei\Inspectors\ClassInspector;

class PropertyInspector
extends MemberInspector {

	protected function
	Inspect(ClassInspector $Class):
	static {

		list($CName,$PName) = explode('::',$this->Name);

		$Info = new ReflectionProperty($CName,$PName);
		$Type = $Info->GetType();
		$Item = NULL;

		$this->Inherited = $Info->GetDeclaringClass()->GetName();
		$this->Static = $Info->IsStatic();
		$this->Public = $Info->IsPublic();
		$this->Protected = $Info->IsProtected();
		$this->Private = $Info->IsPrivate();
		$this->Type = $Type ? $Type->GetName() : 'mixed';

		if($this->Inherited === $CName)
		$this->Inherited = NULL;

		foreach($Info->GetAttributes(Meta\Info::class) as $Item)
		$this->Info = $Item->NewInstance();

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

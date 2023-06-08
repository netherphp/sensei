<?php

namespace Nether\Sensei\Inspectors;

use Nether;

use ReflectionParameter;
use ReflectionUnionType;
use Nether\Common\Datastore;

class ArgumentInspector
extends AbstractInspector {

	public string
	$InspectorType = 'class';

	public string
	$Name;

	public string
	$Type;

	public bool
	$Nullable;

	public mixed
	$Default = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Func, string $Name) {
		parent::__Construct();

		$this->Name = $Name;

		$this->Inspect($Func);
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	Inspect(string $Func):
	static {

		$Info = new ReflectionParameter(
			(str_contains($Func,'::') ? explode('::',$Func) : $Func),
			$this->Name
		);

		$Type = $Info->GetType();

		////////

		if($Type instanceof ReflectionUnionType)
		$this->Type = (string)$Type;
		else
		$this->Type = is_object($Type) ? $Type->GetName() : 'mixed';

		$this->Nullable = $Info->AllowsNull();
		$this->Default = $Info->IsDefaultValueAvailable() ? $Info->GetDefaultValue() : NULL;

		return $this;
	}

}

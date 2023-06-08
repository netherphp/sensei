<?php

namespace Nether\Sensei\Struct;

use Nether\Sensei\Codebase;
use Nether\Sensei\Inspectors\NamespaceInspector;
use Nether\Sensei\Inspectors\ClassInspector;

class CodebaseStats {

	public array
	$Titles = [
		'namespace' => 'Namespaces',
		'class'     => 'Classes',
		'interface' => 'Interfaces',
		'trait'     => 'Traits',
		'attribute' => 'Attributes'
	];

	public array
	$Counts = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Codebase $Codebase) {

		$CKey = NULL;

		////////

		$this->Counts['namespace'] = $Codebase->Namespaces->Count();

		////////

		foreach(['class', 'interface', 'trait', 'attribute'] as $CKey)
		$this->Counts[$CKey] = ($Codebase->Namespaces)->Accumulate(0, (
			fn(int $Carry, NamespaceInspector $Namespace): int
			=> ($Carry + (
				($Namespace->Classes)
				->Distill(
					fn(ClassInspector $Class): bool
					=> ($Class->GetTypeWord() === $CKey)
				)
				->Count()
			))
		));

		return;
	}

}

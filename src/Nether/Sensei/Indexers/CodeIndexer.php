<?php

namespace Nether\Sensei\Indexers;

use Nether;

use PhpToken;
use SplFileInfo;
use Nether\Object\Datastore;
use Nether\Object\Prototype\ConstructArgs;

class CodeIndexer
extends Nether\Object\Prototype {

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Interfaces;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Traits;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Classes;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Source) {
		parent::__Construct();

		$this->Tokens = new Datastore(PhpToken::Tokenize(
			$Source
		));

		$this->Index();

		return;
	}


	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Nether\Sensei\Meta\Todo('2021-09-12','make Current a real class')]
	protected function
	Index():
	static {

		$Token = NULL;
		$K = NULL;
		$T = NULL;

		$Current = new class {

			public string
			$Namespace;

			public string
			$Class;

			public function
			DigestNamespace(Datastore $Tokens, int $K) {

				$this->Namespace = '';
				$T = NULL;

				while(++$K) {
					$T = $Tokens[$K];

					if($T->text === ';' || $T->text === '{')
					break;

					$this->Namespace .= trim($T->text) . "\\";
				}

				$this->Namespace = rtrim($this->Namespace,'\\');

				return $this->Namespace;
			}

			public function
			DigestClass(Datastore $Tokens, int $K) {

				$T = NULL;

				while(++$K) {
					$T = $Tokens[$K];

					if($T->id !== T_STRING)
					continue;

					$this->Class = $T->text;
					break;
				}

				//var_dump("{$this->Namespace}\\{$this->Class}");

				return "{$this->Namespace}\\{$this->Class}";
			}

		};

		foreach($this->Tokens as $K => $T) {
			if($T->id === T_NAMESPACE)
			$Current->DigestNamespace($this->Tokens,$K);

			elseif($T->id === T_CLASS && $this->TokenIsRealClass($K))
			$this->Classes->Push(
				new Nether\Sensei\Inspectors\ClassInspector(
					$Current->DigestClass($this->Tokens,$K)
				),
				"{$Current->Namespace}\\{$Current->Class}"
			);

			elseif($T->id === T_TRAIT)
			$this->Traits->Push(
				$Current->DigestClass($this->Tokens,$K),
				"{$Current->Namespace}\\{$Current->Class}"
			);

			elseif($T->id === T_INTERFACE)
			$this->Interfaces->Push(
				$Current->DigestClass($this->Tokens,$K),
				"{$Current->Namespace}\\{$Current->Class}"
			);
		}

		return $this;
	}

	public function
	TokenIsRealClass($K):
	bool {

		// see what is before now.

		$C = $K;
		while($this->Tokens[--$C]->id === T_WHITESPACE);

			// anonymous class.
			if($this->Tokens[$C]->id === T_NEW)
			return FALSE;

		// see what is after now.

		$C = $K;
		while($this->Tokens[++$C]->id === T_WHITESPACE);

			// named argument label
			if($this->Tokens[$C]->text === ':')
			return FALSE;

			// probably ::class, as an argument.
			if($this->Tokens[$C]->text === ',')
			return FALSE;

			// probably ::class) as an arugment.
			if($this->Tokens[$C]->text === ')')
			return FALSE;

		return TRUE;
	}

	static public function
	FromFile(SplFileInfo $File):
	static {

		return new static(
			file_get_contents($File->GetRealPath())
		);
	}

}

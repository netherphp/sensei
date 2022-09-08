<?php

namespace Nether\Sensei\Inspectors;

use Nether;

use Nether\Object\Datastore;

// this is more of an honorary inspector than anything since there is no
// ReflectionNamespace to digest.

class NamespaceInspector
extends AbstractInspector {

	public string
	$Name;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Namespaces;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Classes;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Interfaces;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Traits;

	public function
	__Construct(string $Name) {
		parent::__Construct();

		$this->Name = $Name;

		return;
	}

	public function
	GetName():
	string {

		return $this->Name;
	}

	public function
	GetNiceName():
	string {

		return trim($this->Name, '\\');
	}

	public function
	GetNamespaceName():
	string {

		if(str_contains($this->Name, '\\'))
		return trim(substr(
			$this->Name,
			0,
			(strrpos($this->Name, '\\') + 1)
		),'\\');

		return '';
	}

	public function
	GetURI(string $Ext=''):
	string {

		$Path = strtolower(trim("{$this->Name}{$Ext}",'\\'));
		$Path = explode('\\', $Path);

		return join(DIRECTORY_SEPARATOR, $Path);
	}

}

<?php

namespace Nether\Sensei\Inspectors;
use Nether;

use Nether\Common\Datastore;
use Nether\Sensei\Util;

// this is more of an honorary inspector than anything since there is no
// ReflectionNamespace to digest.

class NamespaceInspector
extends AbstractInspector {

	public string
	$Name;

	#[Nether\Common\Meta\PropertyObjectify]
	public Datastore
	$Namespaces;

	#[Nether\Common\Meta\PropertyObjectify]
	public Datastore
	$Classes;

	#[Nether\Common\Meta\PropertyObjectify]
	public Datastore
	$Interfaces;

	#[Nether\Common\Meta\PropertyObjectify]
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

	public function
	SortForPresentation():
	void {

		$this->Classes->Sort(Util::SortClassesByLogic(...));

		return;
	}

}

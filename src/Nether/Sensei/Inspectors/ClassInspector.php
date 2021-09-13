<?php

namespace Nether\Sensei\Inspectors;

use Nether;

use ReflectionClass;
use Nether\Object\Datastore;

class ClassInspector
extends AbstractInspector {

	public string
	$InspectorType = 'class';

	public string
	$Name;

	public bool
	$Abstract = FALSE;

	public bool
	$Final = FALSE;

	public bool
	$Attribute = FALSE;

	public ?string
	$Extends = NULL;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Interfaces;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Constants;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Properties;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$Methods;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Name) {
		parent::__Construct();

		$this->Name = $Name;
		$this->Inspect();

		return;
	}

	public function
	GetClassName():
	string {

		return substr(
			$this->Name,
			(strrpos($this->Name,'\\') + 1)
		);
	}

	public function
	GetNamespaceName():
	string {

		return trim(substr(
			$this->Name,
			0,
			(strrpos($this->Name,'\\') + 1)
		),'\\');
	}

	public function
	GetName():
	string {

		if(str_contains($this->Name,'\\'))
		return array_reverse(explode('\\',$this->Name))[0];

		return $this->Name;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetNamespacedPath(string $Ext=''):
	string {

		$Path = explode('\\',trim("{$this->Name}{$Ext}",'\\'));

		return join(DIRECTORY_SEPARATOR,$Path);
	}

	protected function
	Inspect():
	static {

		class_exists($this->Name);

		////////

		$Class = new ReflectionClass($this->Name);
		$Item = NULL;
		$Extends = $Class->GetParentClass();

		$this->Abstract = $Class->IsAbstract();
		$this->Final = $Class->IsFinal();
		$this->Attribute = count($Class->GetAttributes('Attribute')) > 0;
		$this->Extends = $Extends ? "\\{$Extends->GetName()}" : NULL;

		foreach($Class->GetInterfaces() as $Item)
		$this->Interfaces->Push(
			$Item->GetName()
		);

		foreach($Class->GetReflectionConstants() as $Item)
		$this->Constants->Push(new ConstantInspector(
			"{$this->Name}::{$Item->GetName()}"
		));

		foreach($Class->GetProperties() as $Item)
		$this->Properties->Push(new PropertyInspector(
			"{$this->Name}::{$Item->GetName()}"
		));

		foreach($Class->GetMethods() as $Item)
		$this->Methods->Push(new MethodInspector(
			"{$this->Name}::{$Item->GetName()}"
		));

		return $this;
	}

}

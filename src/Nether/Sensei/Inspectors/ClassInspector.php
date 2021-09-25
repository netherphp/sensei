<?php

namespace Nether\Sensei\Inspectors;

use Nether;
use Nether\Sensei\Meta;

use Exception;
use ReflectionClass;
use Nether\Object\Datastore;

class ClassInspector
extends AbstractInspector {

	public string
	$InspectorType = 'class';

	public string
	$FileExt = '.class.html';

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
	GetName():
	string {

		return $this->Name;
	}

	public function
	GetNiceName():
	string {

		return trim($this->Name,'\\');
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
	GetURI(string $Ext=''):
	string {

		$Path = explode('\\',trim("{$this->Name}{$Ext}",'\\'));

		return join(DIRECTORY_SEPARATOR,$Path);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetMemberType(MemberInspector $Member):
	string {

		return match($Member->Type) {
			'static' => $this->GetName(),
			'self'   => $this->GetName(),
			default  => $Member->Type
		};
	}

	public function
	GetArgumentType(ArgumentInspector $Arg):
	string {

		if(!str_contains($Arg->Type,'\\'))
		return $Arg->Type;

		$Bits = explode('\\',$Arg->Type);
		array_pop($Bits);

		$Namespace = join('\\',$Bits);

		if(str_starts_with($this->GetNamespaceName(),$Namespace))
		return str_replace("{$Namespace}\\",'',$Arg->Type);

		return $Arg->Type;
	}


	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	Inspect():
	static {

		if(!class_exists($this->Name))
		throw new Exception("{$this->Name}");

		////////

		$Class = new ReflectionClass($this->Name);
		$Item = NULL;
		$Extends = $Class->GetParentClass();

		$this->Abstract = $Class->IsAbstract();
		$this->Final = $Class->IsFinal();
		$this->Attribute = count($Class->GetAttributes('Attribute')) > 0;
		$this->Extends = $Extends ? $Extends->GetName() : NULL;

		foreach($Class->GetAttributes(Meta\Info::class) as $Item)
		$this->Info = $Item->NewInstance();

		foreach($Class->GetInterfaces() as $Item)
		$this->Interfaces->Push(
			$Item->GetName()
		);

		foreach($Class->GetReflectionConstants() as $Item)
		$this->Constants->Push(new ConstantInspector(
			"{$this->Name}::{$Item->GetName()}",
			$this
		));

		foreach($Class->GetProperties() as $Item)
		$this->Properties->Push(new PropertyInspector(
			"{$this->Name}::{$Item->GetName()}",
			$this
		));

		foreach($Class->GetMethods() as $Item)
		$this->Methods->Push(new MethodInspector(
			"{$this->Name}::{$Item->GetName()}",
			$this
		));

		($this->Properties)
		->Sort(function(PropertyInspector $A, PropertyInspector $B){

			if($A->GetAccessSortable() !== $B->GetAccessSortable())
			return $A->GetAccessSortable() <=> $B->GetAccessSortable();

			return $A->Name <=> $B->Name;
		});

		($this->Methods)
		->Sort(function(MethodInspector $A, MethodInspector $B){

			// alpha __magic methods
			// alpha public > protected > private
			// alpha static public > protected > private
			// pattern repeated, inherited methods.

			if($A->GetAccessSortable() !== $B->GetAccessSortable())
			return $A->GetAccessSortable() <=> $B->GetAccessSortable();

			if(str_starts_with($A->GetName(),'_') !== str_starts_with($B->GetName(),'_'))
			return $B->GetName() <=> $A->GetName();

			return $A->GetName() <=> $B->GetName();
		});

		return $this;
	}

}

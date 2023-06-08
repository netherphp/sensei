<?php

namespace Nether\Sensei\Inspectors;

use Nether;
use Nether\Sensei\Meta;

use Exception;
use ReflectionClass;
use Nether\Common\Datastore;

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

	public bool
	$Interface = FALSE;

	public bool
	$Trait = FALSE;

	public ?string
	$Extends = NULL;

	#[Nether\Common\Meta\PropertyObjectify]
	public Datastore
	$Interfaces;

	#[Nether\Common\Meta\PropertyObjectify]
	public Datastore
	$Constants;

	#[Nether\Common\Meta\PropertyObjectify]
	public Datastore
	$Properties;

	#[Nether\Common\Meta\PropertyObjectify]
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

		return trim($this->Name, '\\');
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
	GetTypeWord():
	string {

		if($this->Interface)
		return 'interface';

		if($this->Trait)
		return 'trait';

		if($this->Attribute)
		return 'attribute';

		return 'class';
	}

	public function
	GetURI(string $Ext=''):
	string {

		$Path = strtolower(trim("{$this->Name}{$Ext}",'\\'));
		$Path = explode('\\', $Path);

		return join(DIRECTORY_SEPARATOR, $Path);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetConstantsLocal():
	Datastore {

		return (
			$this->Constants
			->Distill(
				fn(ConstantInspector $A)
				=> $A->Inherited === NULL
			)
			->Sort(function(ConstantInspector $A, ConstantInspector $B) {
				if($A->GetAccessSortable() !== $B->GetAccessSortable())
				return $A->GetAccessSortable() <=> $B->GetAccessSortable();

				return $A->Name <=> $B->Name;
			})
		);
	}

	public function
	GetConstantsInherited():
	Datastore {

		return (
			$this->Constants
			->Distill(
				fn(ConstantInspector $A)
				=> $A->Inherited !== NULL
			)
			->Sort(function(ConstantInspector $A, ConstantInspector $B) {
				if($A->GetAccessSortable() !== $B->GetAccessSortable())
				return $A->GetAccessSortable() <=> $B->GetAccessSortable();

				return $A->Name <=> $B->Name;
			})
		);
	}

	public function
	GetMethodsLocal():
	Datastore {

		return (
			$this->Methods
			->Distill(
				fn(MethodInspector $A)
				=> $A->Inherited === NULL
			)
			->Sort(function(MethodInspector $A, MethodInspector $B) {
				if($A->GetAccessSortable() !== $B->GetAccessSortable())
				return $A->GetAccessSortable() <=> $B->GetAccessSortable();

				return $A->Name <=> $B->Name;
			})
		);
	}

	public function
	GetMethodsInherited():
	Datastore {

		return (
			$this->Methods
			->Distill(
				fn(MethodInspector $A)
				=> $A->Inherited !== NULL
			)
			->Sort(function(MethodInspector $A, MethodInspector $B) {
				if($A->GetAccessSortable() !== $B->GetAccessSortable())
				return $A->GetAccessSortable() <=> $B->GetAccessSortable();

				return $A->Name <=> $B->Name;
			})
		);
	}

	public function
	GetPropertiesLocal():
	Datastore {

		return (
			$this->Properties
			->Distill(
				fn(PropertyInspector $A)
				=> $A->Inherited === NULL
			)
			->Sort(function(PropertyInspector $A, PropertyInspector $B) {
				if($A->GetAccessSortable() !== $B->GetAccessSortable())
				return $A->GetAccessSortable() <=> $B->GetAccessSortable();

				return $A->Name <=> $B->Name;
			})
		);
	}

	public function
	GetPropertiesInherited():
	Datastore {

		return (
			$this->Properties
			->Distill(
				fn(PropertyInspector $A)
				=> $A->Inherited !== NULL
			)
			->Sort(function(PropertyInspector $A, PropertyInspector $B) {
				if($A->GetAccessSortable() !== $B->GetAccessSortable())
				return $A->GetAccessSortable() <=> $B->GetAccessSortable();

				return $A->Name <=> $B->Name;
			})
		);
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

		if(!class_exists($this->Name) && !trait_exists($this->Name) && !interface_exists($this->Name))
		throw new Exception("Invalid Class Found: {$this->Name}");

		////////

		$Class = new ReflectionClass($this->Name);
		$Item = NULL;
		$Extends = $Class->GetParentClass();

		$this->Abstract = $Class->IsAbstract();
		$this->Final = $Class->IsFinal();
		$this->Attribute = count($Class->GetAttributes('Attribute')) > 0;
		$this->Extends = $Extends ? $Extends->GetName() : NULL;
		$this->Interface = $Class->IsInterface();
		$this->Trait = $Class->IsTrait();
		$this->Info = Nether\Sensei\Util::GetNetherDocFromFileLine(
			$Class->GetFilename(),
			$Class->GetStartLine()
		);

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

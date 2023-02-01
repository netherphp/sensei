<?php

namespace Nether\Sensei;

use Nether;

use Attribute;
use SplFileInfo;
use JsonSerializable;
use Stringable;

#[Attribute]
abstract class VeryBusyClass
extends Nether\Common\Prototype
implements Stringable {

	const
	FlagOne = (1 << 0),
	FlagTwo = (1 << 1);

	protected const
	Fruit = 'watermelon';

	public string
	$Name;

	public ?SplFileInfo
	$File = NULL;

	public function
	__ToString():
	string {

		return '2';
	}

	static public function
	FirstStaticMethod(string $Input):
	bool {

		return FALSE;
	}

	static public function
	SecondStaticMethod(string $Input, int $MoreInput):
	float {

		return 0.0;
	}

	static protected function
	AnotherStaticMethod():
	static {

		return new static;
	}

}

<?php

namespace Nether\Sensei\Meta;

use Attribute;
use Stringable;

#[Attribute]
class Date
implements Stringable {

	public string
	$Date;

	public function
	__Construct(string $Date) {

		$this->Date = $Date;
		return;
	}

	public function
	__ToString():
	string {

		return $this->Date;
	}

}


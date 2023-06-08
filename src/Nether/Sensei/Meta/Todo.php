<?php

namespace Nether\Sensei\Meta;

use Attribute;

#[Attribute]
class Todo {

	public string
	$Date;

	public string
	$Text;

	public function
	__Construct(string $Date, string $Text='') {

		$this->Date = $Date;
		$this->Text = $Text;

		return;
	}

}


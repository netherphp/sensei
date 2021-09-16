<?php

namespace Nether\Sensei\Meta;

use Attribute;

#[Attribute]
class Info {

	public string
	$Text;

	public function
	__Construct(string $Text='') {

		$this->Text = $Text;

		return;
	}

}


<?php

namespace Nether\Sensei;

class Util {

	#[Meta\DateAdded('2021-09-12')]
	#[Meta\Info('make a directory and its parents if needed.')]
	static public function
	MkDir(string $Dir):
	bool {

		$UMask = umask(0);
		@mkdir($Dir,0777,TRUE);
		umask($UMask);

		return is_dir($Dir);
	}

	#[Meta\DateAdded('2021-09-12')]
	#[Meta\Info('trim excess off namespace and class names.')]
	static public function
	GetNamespaceName(string $Input):
	string {

		return trim($Input,'\\');
	}

	#[Meta\DateAdded('2021-09-19')]
	#[Meta\Info('convert a class or namespace name into a uri.')]
	static public function
	GetNamespaceURI(string $Input):
	string {

		return str_replace('\\','/',$Input);
	}

	#[Meta\DateAdded('2021-09-24')]
	#[Meta\Info('check if this class is built into php.')]
	static public function
	IsBuiltInClass(string $Name):
	bool {

		return in_array(
			$Name,
			$GLOBALS['SenseiPreloadData']['Classes']
		);
	}

	#[Meta\DateAdded('2021-09-24')]
	#[Meta\Info('check if this interface is built into php.')]
	static public function
	IsBuiltInInterface(string $Name):
	bool {

		return in_array(
			$Name,
			$GLOBALS['SenseiPreloadData']['Interfaces']
		);
	}

	#[Meta\DateAdded('2021-09-24')]
	#[Meta\Info('check if this trait is built into php.')]
	static public function
	IsBuiltInTrait(string $Name):
	bool {

		return in_array(
			$Name,
			$GLOBALS['SenseiPreloadData']['Traits']
		);
	}

	#[Meta\DateAdded('2021-09-24')]
	#[Meta\Info('given a thing, generate a url to the php manual.')]
	static public function
	GetClassManualURL(string $Name):
	string {

		// @todo make this configurable.
		$Prefix = 'https://www.php.net/manual/en';

		return sprintf(
			'%s/class.%s.php',
			trim($Prefix,'/'),
			strtolower($Name)
		);
	}

}

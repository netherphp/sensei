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

}

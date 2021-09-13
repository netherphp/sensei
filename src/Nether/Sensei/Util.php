<?php

namespace Nether\Sensei;

class Util {

	static public function
	MkDir(string $Dir):
	bool {

		$UMask = umask(0);
		@mkdir($Dir,0777,TRUE);
		umask($UMask);

		return is_dir($Dir);
	}

	static public function
	GetNamespaceName(string $Input):
	string {

		return trim($Input,'\\');
	}

}

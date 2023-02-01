<?php

namespace Nether\Sensei\Indexers;

use Nether;

use FilterIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Nether\Common\Datastore;

#[Nether\Sensei\Meta\DateAdded('2021-09-12')]
class FileIndexer
extends FilterIterator {

	public function
	__Construct(string $Dir) {

		parent::__Construct(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($Dir)
			)
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Nether\Sensei\Meta\DateAdded('2021-09-12')]
	public function
	Accept():
	bool {

		$Current = $this->Current();

		if($Current->IsDir())
		return FALSE;

		if(!str_ends_with($Current->GetFilename(), '.php'))
		return FALSE;

		return TRUE;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Nether\Sensei\Meta\DateAdded('2021-09-12')]
	public function
	GetArray():
	array {

		$Output = [];
		$File = NULL;

		foreach($this as $File)
		$Output[$File->GetRealPath()] = $File;

		return $Output;
	}

	#[Nether\Sensei\Meta\DateAdded('2021-09-12')]
	public function
	GetDatastore():
	Datastore {

		$Output = new Datastore($this->GetArray());

		return $Output;
	}

}

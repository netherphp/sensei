<?php

namespace Nether\Sensei;

use Nether\Object\Datastore;
use Nether\Sensei\Inspectors\NamespaceInspector;
use Nether\Sensei\Inspectors\ClassInspector;

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

	static public function
	Repath(string $Input):
	string {

		return str_replace('\\', '/', $Input);
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
			$GLOBALS['SenseiBuiltinData']['Classes']
		);
	}

	#[Meta\DateAdded('2021-09-24')]
	#[Meta\Info('check if this interface is built into php.')]
	static public function
	IsBuiltInInterface(string $Name):
	bool {

		return in_array(
			$Name,
			$GLOBALS['SenseiBuiltinData']['Interfaces']
		);
	}

	#[Meta\DateAdded('2021-09-24')]
	#[Meta\Info('check if this trait is built into php.')]
	static public function
	IsBuiltInTrait(string $Name):
	bool {

		return in_array(
			$Name,
			$GLOBALS['SenseiBuiltinData']['Traits']
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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GenerateNamespaceLinkChain(string $Input, string $Prefix='/docs'):
	array {

		$Bits = new Datastore(explode('\\', $Input));
		$Namespace = '';
		$Output = [];

		foreach($Bits as $Key => $Bit) {
			$Namespace .= "/{$Bit}";

			$Output[$Bit] = sprintf(
				'%s/%s.namespace',
				$Prefix,
				trim(strtolower($Namespace), '/')
			);
		}

		return $Output;
	}

	static public function
	GenerateNamespaceLinkChainHTML(string $Input, string $Prefix='/docs'):
	string {

		// todo use the link chain method first then add to the result

		$Bits = new Datastore(explode('\\', $Input));
		$Namespace = '';
		$Output = '';

		foreach($Bits as $Key => $Bit) {
			$Namespace .= "/{$Bit}";

			$Output .= sprintf(
				'<a href="%s/%s.namespace">%s</a>',
				$Prefix,
				trim(strtolower($Namespace), '/'),
				$Bit
			);

			if(!$Bits->IsLastKey($Key))
			$Output .= ' \ ';
		}

		return $Output;
	}

	static public function
	GenerateClassLinkChain(string $Input, string $Prefix='/docs'):
	string {

		$Bits = new Datastore(explode('\\', $Input));
		$Namespace = '';
		$Output = '';

		foreach($Bits as $Key => $Bit) {
			if($Bits->IsLastKey($Key)) {
				$Output .= sprintf(
					'/%s/%s/%s.class',
					$Prefix,
					ltrim(strtolower($Namespace), '/'),
					strtolower($Bit)
				);

				continue;
			}

			$Namespace .= "/{$Bit}";
			$Output .= sprintf(
				'/%s/%s.namespace',
				$Prefix,
				ltrim(strtolower($Namespace), '/')
			);
		}

		return $Output;
	}

	static public function
	GenerateClassLinkChainHTML(string $Input, string $Prefix='/docs'):
	string {

		// todo use the link chain method first then add to the result

		$Bits = new Datastore(explode('\\', $Input));
		$Namespace = '';
		$Output = '';

		foreach($Bits as $Key => $Bit) {
			if($Bits->IsLastKey($Key)) {
				$Output .= sprintf(
					'<a href="/docs/%s/%s.class">%s</a>',
					ltrim(strtolower($Namespace), '/'),
					strtolower($Bit),
					$Bit
				);

				continue;
			}

			$Namespace .= "/{$Bit}";
			$Output .= sprintf(
				'<a href="/docs/%s.namespace">%s</a> \\ ',
				ltrim(strtolower($Namespace), '/'),
				$Bit
			);
		}

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetNetherDocFromFileLine(string $Filename, int $LineStart=1):
	string {

		$Data = array_slice(
			file($Filename),
			($LineStart - 1)
		);

		$Output = '';
		$Iter = 0;
		$Cap = 1;
		$Open = FALSE;

		for($Iter = 0; $Iter <= $Cap; $Iter++) {
			if($Iter >= count($Data))
			break;

			// full line breaks mean no sensei block.

			if(trim($Data[$Iter]) === '')
			break;

			// if we haven't started reading yet see if there is a reason
			// we should continue reading.

			if(!$Open)
			if(preg_match('#(?:[\(\)\{\:\;\,\.])|(?:namespace|class|function)#', $Data[$Iter]))
			$Cap++;

			////////

			$Line = trim($Data[$Iter]);

			if(preg_match('#\/\*\/\/#', $Line))
			$Open = TRUE;

			if($Open) {
				$Cap++;

				if(!str_starts_with($Line, '@'))
				$Output .= $Line . PHP_EOL;
			}

			if(preg_match('#\/\/\*\/#', $Line))
			$Open = FALSE;
		}

		return trim($Output);
	}

	static public function
	TrimNetherDoc(string $Input):
	string {

		$Output = trim($Input, '/*');
		$Output = trim($Output);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FitlerNamespaceNotEmpty(NamespaceInspector $Namespace):
	bool {

		return ($Namespace->Classes->Count() > 0);
	}

	static public function
	SortClassesByLogic(ClassInspector $A, ClassInspector $B):
	int {

		if($A->GetTypeWord() !== $B->GetTypeWord())
		return $A->GetTypeWord() <=> $B->GetTypeWord();

		return $A->Name <=> $B->Name;
	}

	static public function
	SortClassesByName(ClassInspector $A, ClassInspector $B):
	int {

		return $A->Name <=> $B->Name;
	}

}

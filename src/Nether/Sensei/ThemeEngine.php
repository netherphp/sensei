<?php

namespace Nether\Sensei;

use Nether;

use SplFileInfo;
use Exception;
use Nether\Sensei\Util;

class ThemeEngine
extends Nether\Object\Prototype {

	protected Theme
	$Theme;

	protected array
	$Data = [
		'PageTitle'  => 'Documentation',
		'PageDepth'  => 0,
		'PageOutput' => ''
	];

	public function
	__Construct(Theme $Theme) {

		$this->Theme = $Theme;
		return;
	}

	public function
	Set(string $Key, mixed $Value):
	static {

		$this->Data[$Key] = $Value;

		return $this;
	}

	public function
	Area(string $File, ...$Argv):
	static {

		$File = $this->Theme->GetPath("{$File}.phtml");

		$this->Data['PageOutput'] .= (
			function($Engine, $ThemeFile, $Argv){
				extract($Argv);
				ob_start();
				require($ThemeFile);
				return ob_get_clean();
			}
		)($this, $File, $Argv);

		return $this;
	}

	public function
	Element(string $File, ...$Argv):
	string {

		$File = $this->Theme->GetPath("element/{$File}.phtml");

		return (
			function($Engine, $ThemeFile, $Argv){
				extract($Argv);
				ob_start();
				require($ThemeFile);
				return ob_get_clean();
			}
		)($this, $File, $Argv);
	}

	public function
	Render(string $Outfile):
	static {

		$Dir = dirname($Outfile);

		if(!file_exists($Dir))
		Util::MkDir($Dir);

		if(!file_exists($Dir))
		throw new Exception("Error creating dir {$Dir}");

		if(!is_writable($Dir))
		throw new Exception("Dir {$Dir} is not writable");

		$Render = (
			function($Engine, $ThemeFile, $Argv){
				extract($Argv);
				ob_start();
				require($ThemeFile);
				return ob_get_clean();
			}
		)($this, $this->Theme->GetPath('theme.phtml'), $this->Data);

		//echo "Writing: {$Outfile}", PHP_EOL;

		file_put_contents($Outfile,$Render);
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetAssetURL(string $What):
	string {

		$URI = sprintf(
			'%s%s',
			str_repeat('../',($this->Data['PageDepth']-1)),
			trim($What,'/')
		);

		return $URI;
	}

	public function
	GetIndentedItems(array $List, int $Indents):
	string {

		$Output = join(
			sprintf("\n%s",str_repeat("\t",$Indents)),
			$List
		);

		$Output .= "\n";

		return $Output;
	}

	public function
	GetNamespaceLink(string $Path, bool $IsClass=TRUE):
	string {

		return sprintf(
			'<a href="%s">%s</a>',
			'',
			''
		);
	}

	public function
	GetNamespaceLinks(string $Path, string $Spacer='', bool $IsClass=TRUE):
	array {

		$Path = Util::GetNamespaceName($Path);
		$Output = [];
		$Parts = explode('\\', $Path);
		$Part = NULL;
		$Prev = '';

		foreach($Parts as $Part) {
			$Output[] = (sprintf(
				'%s<a href="%s">%s</a>',
				$Spacer,
				$this->GetAssetURL("{$Prev}/{$Part}/index.html"),
				$Part
			));

			$Prev = trim("{$Prev}/{$Part}",'\\');
		}

		if($IsClass)
		$Output[count($Output)-1] = $this->ConvertToClassLink(
			$Output[count($Output)-1]
		);

		return $Output;
	}

	public function
	ConvertToClassLink(string $NSLink):
	string {

		return str_replace('/index.html','.class.html',$NSLink);
	}

	public function
	PrintNamespace(string $Input):
	void {

		echo trim($Input,'\\');
		return;
	}

}

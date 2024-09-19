<?php ##########################################################################
################################################################################

namespace Nether\Sensei;

use Nether\Common;

use PhpToken;
use Exception;

################################################################################
################################################################################

class TestCodeBlock {

	public ?string
	$InputFile = NULL;

	public ?string
	$OutputFile = NULL;

	public ?string
	$TestName = NULL;

	public ?string
	$TestData = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetInputFile(string $Filename):
	static {

		/*!!
		$Obj = new \Nether\Sensei\TestCodeBlock;
		$this->AssertNull($Obj->GetInputFile());

		$Obj->SetInputFile('test);
		$this->AssertEquals('test, $Obj->GetInputFile());
		!!*/

		$this->InputFile = $Filename;

		return $this;
	}

	public function
	SetOutputFile(string $Filename):
	static {

		/*!!
		$Obj = new \Nether\Sensei\TestCodeBlock;
		$this->AssertNull($Obj->GetOutputFile());

		$Obj->SetOutputFile('test);
		$this->AssertEquals('test, $Obj->GetOutputFile());
		!!*/

		$this->OutputFile = $Filename;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromNamespacedClassMethodData(string $N, string $C, string $M, int $T, string $D):
	static {

		$Output = new static;

		$Filename = Common\Filesystem\Util::Pathify(
			'NSenTests',
			join(DIRECTORY_SEPARATOR, explode('\\', $N)),
			sprintf('%s.php', $C)
		);

		$Output->SetOutputFile($Filename);
		$Output->TestName = sprintf('Test_%s_%03d', $M, $T);
		$Output->TestData = $D;

		return $Output;
	}

	static public function
	FromFile(string $Filename):
	Common\Datastore {

		$Output = new Common\Datastore;
		$Source = Common\Filesystem\Util::TryToReadFile($Filename);
		$Tokens = Common\Datastore::FromArray(PhpToken::tokenize($Source));

		$Tok = NULL;
		$Nok = NULL;

		$NName = NULL;
		$CName = NULL;
		$FName = NULL;
		$TNum = 0;

		////////

		do {
			$Tokens->Next();
			$Tok = $Tokens->Current();

			if(!$Tok)
			continue;

			////////

			if($Tok->id === T_NAMESPACE) {
				$Tokens->Next();
				$Nok = $Tokens->Current();

				if($Nok->IsIgnorable()) {
					$Tokens->Next();
					$Nok = $Tokens->Current();
				}

				if($Nok->id === T_NAME_QUALIFIED)
				$NName = $Nok->text;

				continue;
			}

			if($Tok->id === T_CLASS) {
				$Tokens->Next();
				$Nok = $Tokens->Current();

				if($Nok->IsIgnorable()) {
					$Tokens->Next();
					$Nok = $Tokens->Current();
				}

				if($Nok->id === T_STRING)
				$CName = $Nok->text;

				continue;
			}

			if($Tok->id === T_FUNCTION) {
				$Tokens->Next();
				$Nok = $Tokens->Current();

				if($Nok->IsIgnorable()) {
					$Tokens->Next();
					$Nok = $Tokens->Current();
				}

				if($Nok->id === T_STRING) {
					if($FName !== $Nok->text)
					$TNum = 0;

					$FName = $Nok->text;
					$TNum += 1;
				}
			}

			////////

			if(!str_starts_with(trim($Tok->text), '/*!!'))
			continue;

			if(!$NName || !$CName || !$FName) {
				echo 'can only autotest blocks within methods within classes within namespaces right now.';
				echo PHP_EOL;
				continue;
			}

			////////

			$TestName = sprintf(
				'NetherTestGen\\%s\\%s::Test%s%03d',
				$NName, $CName, $FName, $TNum
			);

			$TestCode = $Tok->text;
			$TestCode = preg_replace('#^/\*\!\!#', '', $TestCode);
			$TestCode = preg_replace('#\!\!\*/$#', '', $TestCode);
			$TestCode = preg_replace('#\t#', '', $TestCode);
			$TestCode = trim($TestCode);

			$TestBlock = static::FromNamespacedClassMethodData(
				$NName, $CName, $FName, $TNum,
				$TestCode
			);

			$TestBlock->SetInputFile($Filename);

			$Output->Push($TestBlock);

		} while($Tok);

		////////

		return $Output;
	}

};
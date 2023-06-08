@ECHO OFF

IF NOT EXIST "docs\" (
	mkdir "docs"
)

php -S localhost:80 -t docs router.php

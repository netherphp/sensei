<?php

$Root = dirname(__FILE__);

$Render = function() use($Root) {
	$RenderCmd = sprintf('php %s/bin/nsen render',$Root);
	$RenderOut = NULL;
	$RenderErr = NULL;

	if(isset($_GET['compile']) && $_GET['compile'])
	$RenderCmd = sprintf('php %s\bin\nsen run src',$Root);

	exec($RenderCmd,$RenderOut,$RenderErr);

	if($RenderErr) {
		echo '<pre>';
		var_dump($RenderOut);
		echo '</pre>';

		return FALSE;
	}

	return TRUE;
};

$RequestURI = (function(){
	$URI = trim($_SERVER['REQUEST_URI'],'/');

	if(str_contains($URI,'?'))
	$URI = explode('?',$URI)[0];

	if($URI === '')
	$URI = 'index.html';

	return $URI;
})();

$RequestFile = join(
	DIRECTORY_SEPARATOR,
	[$Root, 'docs', $RequestURI]
);

////////

// if a file does not exist at the location in this particular project that
// most likely means it just hasn't been generated yet.

if(!file_exists($RequestFile))
if(!$Render()) return TRUE;

// if the file still does not exist then it just does not.

if(!file_exists($RequestFile))
return FALSE;

// if we asked for an html file that exists its possible it is out of date
// so trigger a render.

if(str_ends_with($RequestURI,'.html'))
if(!$Render()) return TRUE;

// now just let it serve the file.

return FALSE;

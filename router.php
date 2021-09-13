<?php

$Root = dirname(__FILE__);
$RequestURI = trim($_SERVER['REQUEST_URI'],'/');
$RequestFile = "{$Root}/docs/{$RequestURI}.html";

$RenderCmd = sprintf('php %s/bin/nsen render',$Root);
$RenderOut = NULL;
$RenderErr = NULL;

if($RequestURI === '') {
	echo 'home';
	return TRUE;
}

if(file_exists($RequestFile)) {
	exec($RenderCmd,$RenderOut,$RenderErr);
	if($RenderErr) {
		var_dump($RenderOut);
	}

	echo file_get_contents($RequestFile);
	return TRUE;
}

return FALSE;
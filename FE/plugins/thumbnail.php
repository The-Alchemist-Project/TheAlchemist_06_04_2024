<?php
	$path = @$_GET['path'];
	$_x 	= @$_GET['w'];
	$_y		= @$_GET['h'];
	
	$_unchangeifsmaller	=	isset($_GET['unchange']) ? true : false;
	include("class/Image.php");
	$img = new Image();
	$img->loadImage($path);	
	$img->resize($_x,$_y, true,false );
	$img->showImage();
?>
<?php
session_start();
$RandomStr 		= md5(microtime());// md5 to generate the random string
$ResultStr	 	= strtoupper(substr($RandomStr,0,5));//trim 5 digit 
$NewImage		= imagecreatefromjpeg("img.jpg");//image create by existing image and as back ground 
//$LineColor		= imagecolorallocate($NewImage,195,195,195);//line color 
$TextColor 		= imagecolorallocate($NewImage, 0, 0, 0);//text color-white
//imageline($NewImage,6,12,53,12,$LineColor);//create line 1 on image 
//imageline($NewImage,6,15,53,15,$LineColor);//create line 2 on image 
imagestring($NewImage, 5, 10, 5, $ResultStr, $TextColor);// Draw a random string horizontally 

$_SESSION['captcha_key'] = $ResultStr;// carry the data through session

header("Content-type: image/jpeg");// out out the image 

imagejpeg($NewImage);//Output image to browser 

?>

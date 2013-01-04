<?php
/**
 * ownCloud
 *
 * @author RixTox
 * @copyright 2012 RixTox Me@RixTox.com
 * @license WTFPL
 * 	http://wtfpl2.com/
 *
 */

/*
 *  Public app for generating thumbnails.
 *  Usage:
 *  	Get thumbnail in default sizes:
 *  	http://domain/owncloud/?app=files_thumbnail&
 *      path=/subdir/imagefile.jpg&size={xs, s, m, l, xl}
 *  
 *  	Specify size for thumbnail:
 *  	http://domain/owncloud/?app=files_thumbnail&
 *      path=/subdir/imagefile.jpg&width=125&height=125
 *  
*/

use OC\Thumbnail\ThumbnailManager as Thumbnail;
use OC\Thumbnail\ThumbnailGeneratorRegistry as Generator;

Generator::register_generator('/^image\/.*/', '\OC\Thumbnail\Generator\Image');

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('files_thumbnail');
session_write_close();

if(!empty($_GET['path'])) {
	$filepath = $_GET['path'];
	$filesize = empty($_GET['size'])?'':$_GET['size'];
	$filewidth = empty($_GET['width'])?'':$_GET['width'];
	$fileheight = empty($_GET['height'])?'':$_GET['height'];

	$thumbnail = Thumbnail::getThumbnail($filepath, $filesize, $filewidth, $fileheight);
}

if (!empty($thumbnail)) {
	OCP\Response::enableCaching(3600 * 24); // 24 hour
	$thumbnail->show();
} else {
	\OC_Response::setStatus(404);
	exit();
}

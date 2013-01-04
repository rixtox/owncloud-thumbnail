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
 *  	http://domain/owncloud/?app=files_thumbnail&
 *      path=/subdir/imagefile.jpg&size={xs, s, m, l, xl}
 *  
*/

use OC\Thumbnail;
use OC\Thumbnail\Exceptions;
use OC\Thumbnail\ThumbnailGeneratorRegistry as Generator;

Generator::register_generator('/^image\/.*/',
	'\OC\Thumbnail\Generator\Image');

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('files_thumbnail');
session_write_close();

$filepath =  empty($_GET['path']) ? '' : $_GET['path'];
$filesize = empty($_GET['size']) ? '' : $_GET['size'];

try {
	$thumbnail = Thumbnail\ThumbnailManager::getThumbnail($filepath,
												$filesize);

	// Cache for 24 hour
	OCP\Response::enableCaching(3600 * 24);
	$thumbnail->show();
} catch (Exceptions\ThumbnailError $e) {
	header('Content-Type: application/json');
	echo json_encode(array('error_message' => $e->getMessage()));
}

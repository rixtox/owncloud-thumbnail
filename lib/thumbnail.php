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
namespace OC\Thumbnail;

class ThumbnailManager {

	// Define the default sizes for thumbnails
	// Value from Drobox API https://www.dropbox.com/developers/reference/api#thumbnails
	// Please rewrite it to satisfy your needs.
	public static $sizes = array(
		'xs'  =>array('width'=>32,   'height'=>32),
		's'   =>array('width'=>64,   'height'=>64),
		'm'   =>array('width'=>128,  'height'=>128),
		'l'   =>array('width'=>640,  'height'=>480),
		'xl'  =>array('width'=>1024, 'height'=>768));

	public static function getThumbnail($path, $size = 'm', $width = null, $height = null) {

		if(!empty($width) && !empty($height)) {
			$output_size = array('width'=>$width, 'height'=>$height);
		} else {
			$size = empty(self::$sizes[$size])?'m':$size;
			$output_size = self::$sizes[$size];
		}

		$pathinfo = pathinfo($path);
		$thumbnail_path = "{$pathinfo['dirname']}/{$pathinfo['filename']}_{$output_size['width']}x{$output_size['height']}.{$pathinfo['extension']}";

		$thumbnail_storg = \OCP\Files::getStorage('files_thumbnail');
		if ($thumbnail_storg->file_exists($thumbnail_path)) {
			return new \OC_Image($thumbnail_storg->getLocalFile($thumbnail_path));
		}

		$image = new \OC_Image();
		$image->loadFromFile(\OC_Filesystem::getLocalFile($path));
		if (!$image->valid()) return false;

		$image->fixOrientation();

		$ret = $image->preciseResize( $output_size['width'], $output_size['height'] );

		if (!$ret) {
			\OC_Log::write(self::TAG, 'Couldn\'t resize image', \OC_Log::ERROR);
			unset($image);
			return false;
		}
		$l = $thumbnail_storg->getLocalFile($thumbnail_path);

		$image->save($l);
		return $image;
	}

	public static function removeThumbnails($path) {
		$thumbnail_storg = \OCP\Files::getStorage('files_thumbnail');
		$pathinfo = pathinfo($path);
		$dirname = $pathinfo['dirname'];

		$handle = $thumbnail_storg->opendir($dirname);

		while($filename = readdir($handle)) { // due to the native code bug, OC_FilesystemView->readdir() can not be used.
			if(preg_match("/^{$pathinfo['filename']}_\d*x\d*\.{$pathinfo['extension']}$/", $filename)) {
				$thumbnail_storg->unlink($dirname.'/'.$filename);
			}

		}
	}

	public static function renameThumbnails($oldpath, $newpath) {
		$thumbnail_storg = \OCP\Files::getStorage('files_thumbnail');
		$oldpathinfo = pathinfo($oldpath);
		$olddirname = $oldpathinfo['dirname'];
		$newpathinfo = pathinfo($newpath);
		$newdirname = $newpathinfo['dirname'];

		$handle = $thumbnail_storg->opendir($olddirname);

		while($filename = readdir($handle)) { // due to the native code bug, OC_FilesystemView->readdir() can not be used.
			if(preg_match("/^{$oldpathinfo['filename']}(_\d*x\d*\.){$oldpathinfo['extension']}$/", $filename, $matches)) {
				$thumbnail_storg->rename("{$olddirname}/{$oldpathinfo['filename']}{$matches[1]}{$oldpathinfo['extension']}", "{$newdirname}/{$newpathinfo['filename']}{$matches[1]}{$newpathinfo['extension']}");
			}

		}
	}
}

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
use \OC\Thumbnail\ThumbnailGeneratorRegistry as Generator;

class ThumbnailManager {

	// Define the default sizes for thumbnails
	// Value from Drobox API https://www.dropbox.com/developers/reference/api#thumbnails
	// Please rewrite it to satisfy your needs.
	private static $sizes = array(
		'xs'  =>array('width'=>32,   'height'=>32),
		's'   =>array('width'=>64,   'height'=>64),
		'm'   =>array('width'=>128,  'height'=>128),
		'l'   =>array('width'=>640,  'height'=>480),
		'xl'  =>array('width'=>1024, 'height'=>768));

	const TAG = 'ThumbnailManager';

	public static function getThumbnailStroageImage($path) {
		$thumbnailStorage = \OCP\Files::getStorage('files_thumbnail');
		if ($thumbnailStorage->file_exists($path)) {
			return new \OC_Image($thumbnailStorage->getLocalFile($path));
		}
		return false;
	}

	public static function getThumbnailStroagePath($path) {
		$thumbnailStorage = \OCP\Files::getStorage('files_thumbnail');
		return $thumbnailStorage->getLocalFile($path);
	}

	public static function getThumbnail($path, $size = 'm') {
		$fileMimeType = \OC_Filesystem::getMimeType($path);

		$thumbnailGenerator = Generator::get_generator($fileMimeType);

		if(!\OC_Filesystem::file_exists($path)) {
			return self::generateError('failed', 'Invalid file path.', \OC_Log::ERROR);
		}

		if(!$thumbnailGenerator) {
			return self::generateError('failed', "No thumbnail generator for MIME file type {$fileMimeType}.", \OC_Log::ERROR);
		}

		$size = empty(self::$sizes[$size])?'m':$size;
		$outputSize = $thumbnailGenerator->scaleFilter($path, self::$sizes[$size]);	

		$pathinfo = pathinfo($path);
		$extension = isset($pathinfo['extension'])?$pathinfo['extension']:'';
		$thumbnailPath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_{$outputSize['width']}x{$outputSize['height']}.{$extension}";

		if ($thumbnailImage = self::getThumbnailStroageImage($thumbnailPath)) {
			return $thumbnailImage;
		}

		$thumbnailImage = $thumbnailGenerator->generateThumbnail($path, $outputSize);
		
		$l = self::getThumbnailStroagePath($thumbnailPath);

		$thumbnailImage->save($l);
		return $thumbnailImage;
	}

	public static function generateError($status, $message, $level) {
		\OC_Log::write(self::TAG, $message, $level);
		return array('status'=>$status, 'error'=>$message);
	}

	public static function removeThumbnails($path) {
		$thumbnailStorage = \OCP\Files::getStorage('files_thumbnail');
		$pathinfo = pathinfo($path);
		$dirname = $pathinfo['dirname'];
		$extension = isset($pathinfo['extension'])?$pathinfo['extension']:'';

		$handle = $thumbnailStorage->opendir($dirname);

		while($filename = readdir($handle)) { // due to the native code bug, OC_FilesystemView->readdir() can not be used.
			if(preg_match("/^{$pathinfo['filename']}_\d*x\d*\.{$extension}$/", $filename)) {
				$thumbnailStorage->unlink($dirname.'/'.$filename);
			}

		}
	}

	public static function renameThumbnails($oldpath, $newpath) {
		$thumbnailStorage = \OCP\Files::getStorage('files_thumbnail');
		$oldpathinfo = pathinfo($oldpath);
		$olddirname = $oldpathinfo['dirname'];
		$oldextension = isset($oldpathinfo['extension'])?$oldpathinfo['extension']:'';
		$newpathinfo = pathinfo($newpath);
		$newdirname = $newpathinfo['dirname'];
		$newextension = isset($newpathinfo['extension'])?$newpathinfo['extension']:'';

		$handle = $thumbnailStorage->opendir($olddirname);

		while($filename = readdir($handle)) { // due to the native code bug, OC_FilesystemView->readdir() can not be used.
			if(preg_match("/^{$oldpathinfo['filename']}(_\d*x\d*\.){$oldextension}$/", $filename, $matches)) {
				$thumbnailStorage->rename("{$olddirname}/{$oldpathinfo['filename']}{$matches[1]}{$oldextension}", "{$newdirname}/{$newpathinfo['filename']}{$matches[1]}{$newextension}");
			}

		}
	}
}

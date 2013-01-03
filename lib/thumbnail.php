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
	// Value from Drobox API:
	//   https://www.dropbox.com/developers/reference/api#thumbnails
	// Please rewrite it to satisfy your needs.
	public static $sizes = array(
		'xs'  =>array('width'=>32,   'height'=>32),
		's'   =>array('width'=>64,   'height'=>64),
		'm'   =>array('width'=>128,  'height'=>128),
		'l'   =>array('width'=>640,  'height'=>480),
		'xl'  =>array('width'=>1024, 'height'=>768));

	public static function getThumbnail($path, $size = 'm',
					    $width = null, $height = null)
	{
		$pathinfo = pathinfo($path);
		$ext_name = isset($pathinfo['extension']) ?
		  $pathinfo['extension'] : '';
		switch (strtolower($ext_name)) {
		case 'jpg': case 'jpeg': case 'png': case 'gif':
			return getThumbnailFromImage($path, $size,
						     $width, $height);
		case 'pdf':
			return getThumbnailFromPDF($path, $size,
						     $width, $height);
		default:
			return getEmptyThumbnail();
		}
	}

	public static function getThumbnailFromImage($path,
						     $size = 'm',
						     $width = null,
						     $height = null)
	{
		if (!empty($width) && !empty($height)) {
			$output_size = array('width'=>$width,
					     'height'=>$height);
		} else {
			$size = empty(self::$sizes[$size])?'m':$size;
			$output_size = self::$sizes[$size];
		}

		$pathinfo = pathinfo($path);
		$extension = isset($pathinfo['extension']) ?
		  $pathinfo['extension'] : '';
		$thumbnail_path = "{$pathinfo['dirname']}/" .
		  "{$pathinfo['filename']}" .
		  "_{$output_size['width']}x{$output_size['height']}" .
		  ".{$extension}";


		$thumbnail_storage = \OCP\Files::getStorage('files_thumbnail');
		if ($thumbnail_storage->file_exists($thumbnail_path)) {
			return new \OC_Image($thumbnail_storage-> \
					     getLocalFile($thumbnail_path));
		}

		$image = new \OC_Image();
		$image->loadFromFile(\OC_Filesystem::getLocalFile($path));
		if (!$image->valid()) return false;

		$image->fixOrientation();

		$ret = $image->preciseResize($output_size['width'],
					     $output_size['height']);

		if (!$ret) {
			\OC_Log::write(self::TAG, 'Couldn\'t resize image',
				       \OC_Log::ERROR);
			unset($image);
			return false;
		}
		$l = $thumbnail_storage->getLocalFile($thumbnail_path);

		$image->save($l);
		return $image;
	}

	public static function getThumbnailFromPDF($path,
						   $size = 'm',
						   $width = null,
						   $height = null)
	{
	  	// TODO: Generate PDF Thumbnail here.
		return getEmptyThumbnail();
	}

	public static function removeThumbnails($path) {
		$thumbnail_storage = \OCP\Files::getStorage('files_thumbnail');
		$pathinfo = pathinfo($path);
		$dirname = $pathinfo['dirname'];
		$extension = isset($pathinfo['extension']) ?
		  $pathinfo['extension'] : '';

		$handle = $thumbnail_storage->opendir($dirname);

		// due to the native code bug,
		//+OC_FilesystemView->readdir() can not be used.
		while($filename = readdir($handle)) {
			if(preg_match("/^{$pathinfo['filename']}_" .
				      "\d*x\d*\.{$extension}$/", $filename)) {
				$thumbnail_storage->unlink($dirname .
							   '/'.$filename);
			}

		}
	}

	public static function renameThumbnails($oldpath, $newpath) {
		$thumbnail_storage = \OCP\Files::getStorage('files_thumbnail');
		$oldpathinfo = pathinfo($oldpath);
		$olddirname = $oldpathinfo['dirname'];
		$oldextension = isset($oldpathinfo['extension']) ?
		  $oldpathinfo['extension'] : '';
		$newpathinfo = pathinfo($newpath);
		$newdirname = $newpathinfo['dirname'];
		$newextension = isset($newpathinfo['extension']) ?
		  $newpathinfo['extension'] : '';

		$handle = $thumbnail_storage->opendir($olddirname);

		// due to the native code bug,
		//+OC_FilesystemView->readdir() can not be used.
		while ($filename = readdir($handle)) {
			if(preg_match("/^{$oldpathinfo['filename']}" .
				      "(_\d*x\d*\.){$oldextension}$/",
				      $filename, $matches)) {
				$oldname = "{$olddirname}/" .
				  "{$oldpathinfo['filename']}" .
				  "{$matches[1]}{$oldextension}";
				$newname = "{$newdirname}/" .
				  "{$newpathinfo['filename']}" .
				  "{$matches[1]}{$newextension}";

				$thumbnail_storage->rename($oldname,
							   $newname);
			}

		}
	}


	private static function getEmptyThumbnail()
	{
		return new \OC_Image();
	}

}

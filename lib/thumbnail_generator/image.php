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

namespace OC\Thumbnail\Generator;
use \OC\Thumbnail\ThumbnailGeneratorRegistry as Generator;
use \OC\Thumbnail\ThumbnailGeneratorTemplate as Template;

class Image implements Template {
	public function generateThumbnail($filepath, $thumbnailWidth, $thumbnailHeight) {
		$image = new \OC_Image();
		$image->loadFromFile(\OC_Filesystem::getLocalFile($filepath));
		if (!$image->valid()) return false;

		$image->fixOrientation();

		$ret = $image->preciseResize( $thumbnailWidth, $thumbnailHeight );

		if (!$ret) {
			\OC_Log::write(self::TAG, 'Couldn\'t resize image', \OC_Log::ERROR);
			unset($image);
			return false;
		}
		return $image;
	}
}

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

	const MAXWIDTH = 1024;
	const MAXHEIGHT = 1024;

	public function generateThumbnail($path, $scale) {
		$image = new \OC_Image();
		$image->loadFromFile(\OC_Filesystem::getLocalFile($path));
		if (!$image->valid()) {
			\OC_Log::write(self::TAG, 'Couldn\'t load image file', \OC_Log::ERROR);
			return false;
		}

		$image->fixOrientation();

		$ret = $image->preciseResize( $scale['width'], $scale['height'] );

		if (!$ret) {
			\OC_Log::write(self::TAG, 'Couldn\'t resize image', \OC_Log::ERROR);
			return false;
		}
		return $image;
	}

	public function scaleFilter($path, $scale) {
		$image = new \OC_Image();
		$image->loadFromFile(\OC_Filesystem::getLocalFile($path));
		$width = ($scale['width'] > $image->width())?$image->width():$scale['width'];
		$height = ($scale['height'] > $image->height())?$image->height():$scale['height'];
		$width = ($width > self::MAXWIDTH)?self::MAXWIDTH:$width;
		$height = ($height > self::MAXHEIGHT)?self::MAXHEIGHT:$height;
		return array('width'=>$width, 'height'=>$height);
	}
}

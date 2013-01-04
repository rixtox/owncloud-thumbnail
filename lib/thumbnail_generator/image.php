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

		$old_w = $image->width();
		$old_h = $image->height();
		$max_w = $scale['width'];
		$max_h = $scale['height'];

		if ($max_w / $max_h < $old_w / $old_h) {
			$new_w = $max_w;
			$new_h = ($old_h / $old_w) * $new_w;
		} else {
			$new_h = $max_h;
			$new_w = ($old_w / $old_h) * $new_h;
		}

		return array('width'  => floor($new_w),
					 'height' => floor($new_h));
	}
}

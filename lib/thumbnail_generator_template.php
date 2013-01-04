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

interface ThumbnailGeneratorTemplate {
	// return OC_Image
	public function generateThumbnail($filepath, $thumbnailWidth, $thumbnailHeight);

}
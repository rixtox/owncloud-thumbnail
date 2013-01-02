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


class OC_Files_Thumbnail_Hooks_Handlers {

	public static function removeThumbnails($params) {
		\OC\Thumbnail\ThumbnailManager::removeThumbnails($params['path']);
	}

	public static function renameThumbnails($params) {
		\OC\Thumbnail\ThumbnailManager::renameThumbnails($params['oldpath'], $params['newpath']);
	}
}

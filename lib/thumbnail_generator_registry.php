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

class ThumbnailGeneratorRegistry {
	const TAG = 'ThumbnailGeneratorRegistry';
	public static $registry = array();

	public static function register_generator($MimePattern, $className) {
		if($MimeKey = self::get_generator_name($MimePattern)) {
			\OC_Log::write(self::TAG, "Thumbnail generator {$MimeKey} already has a pattern of {$MimePattern}.", \OC_Log::WARN);
			return false;
		}
		if(array_key_exists($className, self::$registry)) {
			array_push(self::$registry[$className], $MimePattern);
		} else {
			self::$registry[$className] = array($MimePattern);
		}
	}

	public static function get_generator_name($MimeValue) {
		foreach (self::$registry as $registry_key => $registry_values) {
			foreach ($registry_values as $registry_value) {
				if($MimeValue == $registry_value || preg_match($registry_value, $MimeValue)) {
					return $registry_key;
				}
			}
		}
		return false;
	}

	public static function get_generator($MimeValue) {
		if($generator_name = self::get_generator_name($MimeValue)) {
			return new $generator_name();
		} else return false;
	}
}
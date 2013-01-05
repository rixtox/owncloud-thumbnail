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

namespace OC\Thumbnail\Exceptions;

class ThumbnailError extends \RuntimeException {
	function __construct($log_msg) {
		parent::__construct($log_msg);

		\OC_Log::write("thumbnail_generator",
						self::getMessage(),
						\OC_Log::ERROR);
	}
}

class InvalidPathError extends ThumbnailError {
	function __construct() {
		parent::__construct("Invalid file path.");
	}
}

class GeneratorNotFoundError extends ThumbnailError {
	function __construct($mime_type) {
		parent::__construct("No thumbnail generator for " .
				"MIME file type {$mime_type}.");
	}
}

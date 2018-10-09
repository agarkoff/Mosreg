<?php
/**
 * Created by PhpStorm.
 * User: stasa
 * Date: 09.10.2018
 * Time: 9:24
 */

namespace misterparser;

class Log {

    private static $homeDirectory;

    function __construct() {
        self::$homeDirectory = Utils::getHomeDirectory();
    }

    static function debug($message) {
        error_log(date("Y-m-d H:m:s") . " " . $message . "\n", 3, self::$homeDirectory . "debug.log");
    }
}

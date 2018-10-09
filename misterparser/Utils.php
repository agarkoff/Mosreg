<?php
/**
 * Created by PhpStorm.
 * User: stasa
 * Date: 09.10.2018
 * Time: 9:25
 */

namespace misterparser;


class Utils {

    static function getHomeDirectory() {
        if (isset($_SERVER['HOME'])) {
            $path = $_SERVER['HOME'];
        } else if (isset($_SERVER['HOMEPATH'])) {
            $path = $_SERVER['HOMEPATH'];
        } else {
            die("Не удалось получить домашнюю папку.");
        }
        return $path . "\\";
    }
}

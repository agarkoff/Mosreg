<?php
/**
 * Created by PhpStorm.
 * User: stasa
 * Date: 09.10.2018
 * Time: 8:16
 */

namespace misterparser;

use Exception;

class Http
{
    function callApi($method, $url, $data) {
        return $this->fetch(function ($method, $url, $data) {
            Http::callApi0($method, $url, $data, getRandomProxy());
        });
    }

    function get($url) {
        return$this->fetch(function ($url) {
            Http::get0($url, getRandomProxy());
        });
    }

    private function fetch(\Closure $closure) {
        $result = null;
        $inited = false;
        while (!$inited) {
            try {
                $result = $closure();
                $inited = true;
            } catch (Exception $e) {
                Log::debug($e->getMessage());
            }
        }
        return $result;
    }

    private function callApi0($method, $url, $data, $proxy) {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "XXX-TenantId-Header: 2"
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
        if ($proxy) {
            curl_setopt($curl, CURLOPT_PROXY, "socks4://" . $proxy);
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

        $result = curl_exec($curl);
        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl);
        return $result;
    }

    private function get0($url, $proxy) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
        curl_setopt($curl, CURLOPT_PROXY, "socks4://" . $proxy);

        $result = curl_exec($curl);
        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl);
        return $result;
    }

    function getRandomProxy() {
        $proxylist = file("proxylist.txt", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $line = $proxylist[rand(0, count($proxylist) - 1)];
        Log::debug("Используем прокси: " . $line);
        return $line;
    }
}
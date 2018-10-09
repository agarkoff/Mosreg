<?php
/**
 * Created by PhpStorm.
 * User: stasa
 * Date: 08.10.2018
 * Time: 17:00
 */

use PhpQuery\PhpQuery;
use misterparser\Http;
use misterparser\Log;
use misterparser\Utils;
use misterparser\Purchase;

class MosregParser {

    function main() {
        Log::debug("Запуск скрипта...");
        $queries = file(Utils::getHomeDirectory() . "queries.txt", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        Log::debug("Прочитано запросов из файла: " . count($queries));
        $purchases = $this->parse($queries);
        $result = $this->render($purchases);
        file_put_contents(Utils::getHomeDirectory() . "mosreg.html", $result);
        Log::debug("Скрипт выполнен.");
    }

    function parse($queries) {
        $itemsAll = array();
        foreach ($queries as $query) {
            $items = $this->processQuery($query);
            array_merge($itemsAll, $items);
        }
        Log::debug("Найдено закупок всего: " . count($itemsAll));
        return $itemsAll;
    }

    function processQuery($query) {
        Log::debug("Обработка запроса: " . $query);
        //$lastWorkingDay = date("Y-m-d\TH:m:s.000\Z", strtotime("today -1"));
        $lastWorkingDay = "2018-10-05T03:10:00.000Z";
        Log::debug("Используем для поиска дату: " . $lastWorkingDay);
        $http = new Http();
        $purchases = array();
        $json = $this->constructRequestJson($query, $lastWorkingDay);
        $result = json_decode($http->callApi("POST", "https://api.market.mosreg.ru/api/Trade/GetTradesForParticipantOrAnonymous", $json));
        foreach ($result->invdata as $invdata) {
            $html = $http->get("https://market.mosreg.ru/Trade/ViewTrade?id=" . $invdata->Id);
            $pq = new PhpQuery;
            $pq->load_str($html);
            $trs = $pq->query("table.info-table tr");
            $purchase = new Purchase($invdata->Id);
            foreach ($trs as $tr) {
                $td = $pq->query("td", $tr);
                $text = $td[0]->nodeValue;
                $value = trim($td[1]->nodeValue);
                if ($text === "Полное наименование") {
                    $purchase->customerName = $value;
                } else if ($text === "Адрес места нахождения") {
                    $purchase->address = $value;
                } else if ($text === "Наименование") {
                    $purchase->purchaseName = $value;
                } else if ($text === "НМЦК, руб.") {
                    $purchase->cost = $value;
                }
            }
            //$result = json_decode($http->callApi("GET", "https://api.market.mosreg.ru/api/Trade/" . $invdata->Id . "/GetTradeDocuments", null));
            array_push($purchases, $purchase);
        }
        Log::debug("Найдено закупок: " . count($purchases));
        return $purchases;
    }

    function render($purchases) {
        $result = "";
        $loader = new Twig_Loader_Filesystem('.');
        $twig = new Twig_Environment($loader);
        try {
            $result = $twig->render('email.html', ['purchases' => $purchases]);
        } catch (Twig_Error_Loader $e) {
            Log::debug($e->getMessage());
        } catch (Twig_Error_Runtime $e) {
            Log::debug($e->getMessage());
        } catch (Twig_Error_Syntax $e) {
            Log::debug($e->getMessage());
        }
        return $result;
    }


    function constructRequestJson($tradeName, $lastWorkingDay) {
        $config = array(
            "page" => "1",
            "itemsPerPage" => "10",
            "tradeName" => $tradeName,
            "tradeState" => "",
            "OnlyTradesWithMyApplications" => false,
            "sortingParams" => array(),
            "filterPriceMin" => "",
            "filterDateFrom" => $lastWorkingDay,
            "filterDateTo" => null,
            "filterFillingApplicationEndDateFrom" => null,
            "FilterFillingApplicationEndDateTo" => null,
            "filterTradeEasuzNumber" => "",
            "showOnlyOwnTrades" => false,
            "IsImmediate" => false,
            "UsedClassificatorType" => 10,
            "classificatorCodes" => array(),
            "CustomerFullNameOrInn" => "",
            "CustomerAddress" => "",
            "ParticipantHasApplicationsOnTrade" => "",
        );
        return json_encode($config);
    }
}

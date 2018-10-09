<?php
/**
 * Created by PhpStorm.
 * User: stasa
 * Date: 09.10.2018
 * Time: 9:39
 */

namespace misterparser;


class Purchase {
    var $id;
    var $customerName;
    var $address;
    var $purchaseName;
    var $cost;

    public function __construct($id) {
        $this->id = $id;
    }
}

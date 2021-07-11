<?php

namespace Core\Base;

use Core\Main\Request\Request;

abstract class ControllerAuth extends Controller {
    protected array $temp_account = [];

    protected function verifyActive($session_key){
        $result = Request::sendInner(new \App\Version\b001\Account\Controller(), "verifyActive", [$session_key]);
        if(isset($result["account"])) $this->temp_account = $result["account"];
    }
}
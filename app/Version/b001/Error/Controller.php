<?php

namespace App\Version\b001\Error;

use Core\Utils\Answer;

class Controller extends \Core\Base\Controller{
    public function page404(){
        Answer::criticalError(["Method is missing"]);
    }
}
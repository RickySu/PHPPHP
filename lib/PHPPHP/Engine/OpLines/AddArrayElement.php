<?php

namespace PHPPHP\Engine\OpLines;

use PHPPHP\Engine\Zval;

class AddArrayElement extends \PHPPHP\Engine\OpLine {

    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        $key = $this->op1->toString();
        $array = $this->result->toArray();
        $var = Zval::ptrFactory($this->op2->getZval())->separateIfRef();
        if ($key) {
            $array[$key] = $var;
        } else {
            $array[] = $var;
        }
        $this->result->setValue($array);
        $data->nextOp();
    }

}
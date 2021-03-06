<?php

namespace PHPPHP\Engine\OpLines;

use PHPPHP\Engine\Zval;

class AddArrayElement extends \PHPPHP\Engine\OpLine {

    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        $key = $this->op1->toString();
        $array = $this->result->toArray();
        if ($key) {
            $array[$key] = Zval::ptrFactory(clone $this->op2->getZval());
        } else {
            $array[] = Zval::ptrFactory(clone $this->op2->getZval());
        }
        $this->result->setValue($array);
        $data->nextOp();
    }

}
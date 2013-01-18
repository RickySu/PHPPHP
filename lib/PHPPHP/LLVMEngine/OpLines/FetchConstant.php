<?php

namespace PHPPHP\LLVMEngine\OpLines;

//use PHPPHP\LLVMEngine\

class FetchConstant extends \PHPPHP\Engine\OpLine {

    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        $consts = $data->executor->getConstantStore();
        $value = $consts->get($this->op1->toString());

        $this->result->setValue($value);

        $data->nextOp();
    }

}
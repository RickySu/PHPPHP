<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class RecvInit extends Recv {

    protected function prepareParamInit($index, LLVMZval $paramZval) {
        $value = $this->opCode->op2->getImmediateZval()->getValue();
        $this->writeImmediateValueAssign($paramZval, $value);
    }

}

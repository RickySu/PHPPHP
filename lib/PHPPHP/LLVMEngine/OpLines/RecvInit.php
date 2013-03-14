<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class RecvInit extends Recv {

    protected $paramZval;

    public function write() {
        parent::write();
    }

    protected function writeZval(LLVMZval $varZval)
    {
        $this->writeVarAssign($this->paramZval, $varZval);
    }

    protected function writeValue($value)
    {
        $this->writeImmediateValueAssign($this->paramZval, $value);
    }

    protected function prepareParamInit(LLVMZval $paramZval) {
        $this->paramZval=$paramZval;
        $this->prepareOpZval($this->opCode->op2);
    }

}

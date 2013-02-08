<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Assign extends OpLine {

    use Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
    }

    protected function writeZvalZval(LLVMZval $op1Zval,LLVMZval $op2Zval){
        $this->writeVarAssign($op1Zval, $op2Zval);
    }

    protected function writeZvalValue(LLVMZval $op1Zval,$value){
        $this->writeImmediateValueAssign($op1Zval, $value);
    }

}
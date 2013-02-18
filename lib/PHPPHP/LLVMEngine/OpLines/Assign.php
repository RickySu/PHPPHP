<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;

class Assign extends OpLine {

    use Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeZvalZval(LLVMZval $op1Zval,LLVMZval $op2Zval){
        $this->writeVarAssign($op1Zval, $op2Zval);
    }

    protected function writeZvalValue(LLVMZval $op1Zval,$value){
        $this->writeImmediateValueAssign($op1Zval, $value);
    }

    protected function writeValueValue($value1,$value2){
        $this->opCode->op1->getImmediateZval()->setValue($value2);
    }

}
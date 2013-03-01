<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignDim extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2, $this->opCode->dim);
        $this->gcTempZval();
    }

    protected function writeZvalZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval, LLVMZval $dimZval) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssign($valueZval, $op2Zval);
        $this->writeAssignVarElementArrayVar($op1Zval, $valueZval, $dimZval);
    }

    protected function writeZvalValueZval(LLVMZval $op1Zval, $value, LLVMZval $dimZval) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeImmediateValueAssign($valueZval, $value);
        $this->writeAssignVarElementArrayVar($op1Zval, $valueZval, $dimZval);
    }

    protected function writeZvalZvalValue(LLVMZval $op1Zval, LLVMZval $op2Zval, $dimValue) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssign($valueZval, $op2Zval);
        $this->writeAssignArray($op1Zval, $valueZval, $dimValue);
    }

    protected function writeZvalValueValue(LLVMZval $op1Zval, $value, $dimValue) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeImmediateValueAssign($valueZval, $value);
        $this->writeAssignArray($op1Zval, $valueZval, $dimValue);
    }

    protected function writeAssignArray(LLVMZval $dstZval, LLVMZval $valueZval, $dimValue) {
        if ($dimValue == NULL) {
            //array set  $foo[]='bar';
            $this->writeAssignNextElementArrayVar($dstZval, $valueZval);
            return;
        }
        //array set  $foo['key']='bar';
        if (!is_numeric($dimValue)) {
            $this->writeAssignStringElementArrayVar($dstZval, $valueZval, $dimValue);
            return;
        }
        $dimValue = (int) $dimValue;
        if ($dimValue < 0) {
            $this->writeAssignStringElementArrayVar($dstZval, $valueZval, $dimValue);
            return;
        }
        $this->writeAssignIntegerElementArrayVar($dstZval, $valueZval, $dimValue);
    }

}

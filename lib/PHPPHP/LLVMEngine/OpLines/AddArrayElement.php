<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;

class AddArrayElement extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval,
        Parts\ArrayOp;

    protected $resultZval;

    public function write() {
        parent::write();
        if (!$this->opCode->result->markUnUsed) {
            $this->resultZval=$this->function->getZvalIR($this->getResultRegister(), false, true);
            $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
            $this->setResult($this->resultZval);
        }
        $this->gcTempZval();
    }

    protected function writeZvalValue(LLVMZval $dimZval, $value) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeImmediateValueAssign($valueZval, $value);
        $this->writeAssignVarElementArrayVar($this->resultZval, $valueZval, $dimZval);
    }

    protected function writeZvalZval(LLVMZval $dimZval, LLVMZval $valueZvalSrc) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssign($valueZval, $valueZvalSrc);
        $this->writeAssignVarElementArrayVar($this->resultZval, $valueZval, $dimZval);
    }

    protected function writeValueValue($dimValue, $value) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeImmediateValueAssign($valueZval, $value);
        $this->writeAssignArray($this->resultZval, $valueZval, $dimValue);
    }

    protected function writeValueZval($dimValue, LLVMZval $valueZvalSrc) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssign($valueZval, $valueZvalSrc);
        $this->writeAssignArray($this->resultZval, $valueZval, $dimValue);
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

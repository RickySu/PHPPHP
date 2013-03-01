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
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssign($valueZval, $op2Zval);
        $this->writeAssignArray($op1Zval, $valueZval);
    }

    protected function writeZvalValue(LLVMZval $op1Zval, $value) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeImmediateValueAssign($valueZval, $value);
        $this->writeAssignArray($op1Zval, $valueZval);
    }

    protected function writeAssignArray(LLVMZval $dstZval, LLVMZval $valueZval) {
        if ($this->opCode->dim == NULL) {
            $this->writeAssignNextElementArrayVar($dstZval, $valueZval);
            return;
        }
        if ($this->opCode->dim->getImmediateZval() instanceof Zval\Value) {
            $index = $this->opCode->dim->getImmediateZval()->getValue();
            if (!is_numeric($index)) {
                $this->writeAssignStringElementArrayVar($dstZval, $valueZval, $index);
                return;
            }
            $index = (int) $index;
            if ($index < 0) {
                $this->writeAssignStringElementArrayVar($dstZval, $valueZval, $index);
                return;
            }
            $this->writeAssignIntegerElementArrayVar($dstZval, $valueZval, $index);
            return;
        }
    }

}

<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;

class AssignDimRef extends AssignDim {

    use Parts\TypeCast,
        Parts\PrepareOpZval,
        Parts\ArrayOp;

    protected function writeZvalZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval, LLVMZval $dimZval) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssignRef($valueZval, $op2Zval);
        $this->writeAssignVarElementArrayVar($op1Zval, $valueZval, $dimZval);
    }

    protected function writeZvalZvalValue(LLVMZval $op1Zval, LLVMZval $op2Zval, $dimValue) {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssignRef($valueZval, $op2Zval);
        $this->writeAssignArray($op1Zval, $valueZval, $dimValue);
    }

}

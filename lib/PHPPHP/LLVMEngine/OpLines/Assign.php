<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Assign extends OpLine {

    use Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $op1Var = $this->opCode->op1->getImmediateZval();
        if ($op1Var instanceof Zval\Value) {
            if (!isset($op1Var->TempVarName)) {
                $op1VarName = substr($this->function->getRegisterSerial(), 1);
                $op1Var->TempVarName = $op1VarName;
            }
        }

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        if ($op2Zval instanceof LLVMZval) {
            $this->writeVarAssign($op1Zval, $op2Zval);
        } else {
            $this->writeImmediateValueAssign($op1Zval, $op2Zval);
        }
        $this->gcTempZval();
    }

}
<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Assign extends OpLine {
    use Parts\VarAssign;

    public function write() {
        parent::write();
        $op1Var = $this->opCode->op1->getImmediateZval();
        $op2Var = $this->opCode->op2->getImmediateZval();
        if ($op1Var instanceof Zval\Value) {
            if (!isset($op1Var->TempVarName)) {
                $op1VarName = substr($this->function->getRegisterSerial(), 1);
                $op1Var->TempVarName = $op1VarName;
            } else {
                $op1VarName = $op1Var->TempVarName;
            }
            $op1Zval = $this->function->getZvalIR($op1VarName, true, true);
        } else {
            $op1VarName = $op1Var->getName();
            $op1Zval = $this->function->getZvalIR($op1VarName);
        }

        if ($op2Var instanceof Zval\Value) {
            if (isset($op2Var->TempVarName)) {
                $op2VarName = $op2Var->TempVarName;
                $op2Zval = $this->function->getZvalIR($op2VarName, true, true);
                $this->writeVarAssign($op1Zval, $op2Zval);
            } else {
                $this->writeImmediateValueAssign($op1Zval, $op2Var->getValue());
            }
        } else {
            $op2Zval = $this->function->getZvalIR($op2Var->getName());
            $this->writeVarAssign($op1Zval, $op2Zval);
        }
    }

}
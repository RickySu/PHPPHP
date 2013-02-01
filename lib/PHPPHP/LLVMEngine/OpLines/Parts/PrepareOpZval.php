<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;

trait PrepareOpZval {

    protected function prepareOpZval(&$op1Zval, &$op2Zval) {
        $op1Var = $this->opCode->op1->getImmediateZval();
        $op2Var = $this->opCode->op2->getImmediateZval();
        if (($op1Var instanceof Zval\Value) && ($op2Var instanceof Zval\Value)) {
            $op1Zval = $op1Var->getValue();
            $op2Zval = $op2Var->getValue();
            return;
        }
        if ($op1Var instanceof Zval\Value) {
            $op1Zval = $this->makeTempZval($op1Var->getValue());
        } else {
            $op1Zval = $this->function->getZvalIR($op1Var->getName());
        }
        die;
        if ($op2Var instanceof Zval\Value) {
            $op2Zval = $this->makeTempZval($op2Var->getValue());
        } else {
            $op2Zval = $this->function->getZvalIR($op2Var->getName());
        }
    }

    protected function gcTempZval() {
        foreach ($this->tmpZval as $Zval) {
            $this->gcVarZval($Zval);
        }
    }

    /**
     *
     * @return LLVMZval
     */
    protected function makeTempZval($value) {
        $op1Zval = $this->function->getZvalIR(LLVMZval::ZVAL_TEMP_OP, true, true);
        $this->tmpZval[] = $op1Zval;
        $this->writeImmediateValueAssign($op1Zval, $value);
        return $op1Zval;
    }

    /**
     *
     * @return LLVMZval
     */
    protected function prepareResultZval() {
        if (!isset($this->opCode->result->TempVarName)) {
            $resultVarName = substr($this->function->getRegisterSerial(), 1);
            $this->opCode->result->getImmediateZval()->TempVarName = $resultVarName;
        }
        return $this->function->getZvalIR($resultVarName, true, true);
    }

}

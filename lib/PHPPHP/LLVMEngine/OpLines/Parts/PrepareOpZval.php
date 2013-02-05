<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;

trait PrepareOpZval {

    use VarAssign;

    protected function prepareOpZval() {
        $isAllValueOpZval = true;
        $opZvals = array();
        $opVars = func_get_args();

        foreach ($opVars as $index => $opVar) {
            $opVar = $opVars[$index] = $opVars[$index]->getImmediateZval();
            $isAllValueOpZval = $isAllValueOpZval && ($opVar instanceof Zval\Value) && (!isset($opVar->TempVarName));
        }

        if ($isAllValueOpZval) {
            foreach ($opVars as $opVar) {
                $opZvals[] = $opVar->getValue();
            }
            return $opZvals;
        }

        foreach ($opVars as $opVar) {
            if ($opVar instanceof Zval\Value) {
                if (isset($opVar->TempVarName)) {
                    $opZval = $this->function->getZvalIR($opVar->TempVarName, true, true);
                } else {
                    $opZval = $this->makeTempZval($opVar->getValue());
                }
            } else {
                $opZval = $this->function->getZvalIR($opVar->getName());
            }
            $opZvals[] = $opZval;
        }
        return $opZvals;
    }

    protected function gcTempZval() {
    }

    /**
     *
     * @return LLVMZval
     */
    protected function makeTempZval($value) {
        $op1Zval = $this->function->getZvalIR($resultVarName = substr($this->function->getRegisterSerial(), 1), true, true);
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

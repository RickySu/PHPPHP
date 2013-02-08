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

        foreach ($opVars as $opVar) {
            if ($opVar->getImmediateZval() instanceof Zval\Value) {
                $opZval=$opVar->getValue();
            } else {
                $opZval = $this->function->getZvalIR($opVar->getName());
            }
            $opZvals[] = $opZval;
        }
        $cbMethod="write";
        foreach($opZvals as $opZval){
            if($opZval instanceof LLVMZval){
                $cbMethod.="Zval";
            }else{
                $cbMethod.="Value";
            }
        }
        return call_user_func_array(array($this,$cbMethod),$opZvals);
    }

    /**
     *
     * @return LLVMZval
     */
    protected function makeTempZval($value) {
        $opZval = $this->function->getZvalIR($resultVarName = substr($this->function->getRegisterSerial(), 1), true, true);
        $this->writeImmediateValueAssign($opZval, $value);
        $this->registTempZval($opZval);
        return $opZval;
    }

    protected function setResult($value) {
        $this->opCode->result->getImmediateZval()->setValue($value);
    }

}

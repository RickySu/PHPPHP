<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;

trait PrepareOpZval {

    use VarAssign;

    protected function prepareOpZval() {
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
        $nOpZval=count($opZvals);
        $className = explode('\\', get_class($this));
        $className = $className[count($className) - 1];
        foreach($opZvals as $index => $opZval){
            if($opZval instanceof LLVMZval){
                if($opZval->isTemp()){
                    if( $this->opCode->result || ($nOpZval-1 == $index) ){
                        $this->registTempZval($opZval);
                    }
                }
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
    protected function makeTempZval($value,$registTemp=true) {
        $opZval = $this->function->getZvalIR(substr($this->function->getRegisterSerial(), 1), false, true);
        $this->writeImmediateValueAssign($opZval, $value);
        if($registTemp){
            $this->registTempZval($opZval);
        }
        return $opZval;
    }

    protected function setResult($value) {
        $this->opCode->result->getImmediateZval()->setValue($value);
    }

    protected function writeZvalValue(LLVMZval $opZval, $value) {
        $this->TypeCastNumber($opZval, $value, array($this,'writeIntegerOp'), array($this,'writeDoubleOp'));
    }

    protected function writeValueZval($value, LLVMZval $opZval) {
        $this->TypeCastNumber($value, $opZval, array($this,'writeIntegerOp'), array($this,'writeDoubleOp'));
    }

    protected function writeZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $this->TypeCastNumber($op1Zval, $op2Zval, array($this,'writeIntegerOp'), array($this,'writeDoubleOp'));
    }

    protected function writeZval(LLVMZval $opZval) {
        $this->TypeCastNumberSingle($opZval, array($this,'writeIntegerOp'), array($this,'writeDoubleOp'));
    }


}

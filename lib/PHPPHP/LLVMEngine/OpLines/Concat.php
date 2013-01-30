<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Concat extends OpLine {

    public function write() {
        parent::write();
        $op1Var = $this->opCode->op1->getImmediateZval();
        echo $this->getValue($op1Var);
        print_r($this->opCode);//die;
    }

    protected function getValue($op1Var){
        if($op1Var instanceof Zval\Value){
            $value=(string)$op1Var->getValue();
            $op1Val = $this->function->writeConstant($value);
            $op1ValPtr=$this->function->getRegisterSerial();
            return $op1Val->ptr();
        }
        else{
            $op1Zval=$this->function->getZvalIR($op1Var->getName());
        }
    }
}
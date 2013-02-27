<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignDim extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
     //   print_r($this->opCode);die;
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeZvalZval(LLVMZval $op1Zval,LLVMZval $op2Zval){
        $this->writeAssignNextElementArrayVar($op1Zval, $op2Zval);
    }

    protected function writeZvalValue(LLVMZval $op1Zval,$value){
        $valueZval=new LLVMZval(NULL,true,false,$this->function);
        $this->writeImmediateValueAssign($valueZval, $value);
        $this->writeAssignNextElementArrayVar($op1Zval, $valueZval);
        $this->gcVarZval($valueZval);
    }


}

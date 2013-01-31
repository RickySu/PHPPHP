<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignConcat extends Assign {

    public function write() {
        parent::write();
    }

    protected function writeAssignString(LLVMZval $varZval, $value) {
        if($value===''){
            return;
        }
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Concat Assign String $value");
        $this->writeDebugInfo("assign $varZval.=$value");
        $constant = $this->function->writeConstant($value);
        $varZvalPtr=$this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_STRING,LLVMZval::ZVAL_GC_LIST, $varZval->getPtrRegister(), strlen($value), $constant->ptr());
        $varZval->savePtrRegister($varZvalPtr);
    }

    protected function writeAssignInteger(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Concat Assign Integer $value");
        $this->writeAssignString($varZval, "$value");
    }

    protected function writeAssignDouble(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Concat Assign Double $value");
        $this->writeAssignString($varZval, "$value");
    }

    protected function writeVarAssign(LLVMZval $op1Zval,LLVMZval $op2Zval) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Concat Assign Zval $op2Zval");
        $this->writeDebugInfo("assign $op1Zval.=$op2Zval");
        $op1ZvalPtr=$this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL,LLVMZval::ZVAL_GC_LIST, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister());
        $op1Zval->savePtrRegister($op1ZvalPtr);
    }

}
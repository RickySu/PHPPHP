<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Assign extends OpLine {

    public function write() {
        parent::write();
        $op1VarName = $this->opCode->op1->getImmediateZval()->getName();
        $op2Zval = $this->opCode->op2->getImmediateZval();
        if ($op2Zval instanceof Zval\Value) {
            $this->writeImmediateValueAssign($op1VarName, $op2Zval->getValue());
        } else {
            $this->writeVarAssign($op1VarName, $op2Zval->getName());
        }
    }

    protected function writeImmediateValueAssign($varName, $value) {
        $valueType = gettype($value);
        $this->writeDebugInfo("$varName <= ($valueType)");
        switch($valueType){
            case 'integer':
                $this->writeIntegerAssign($varName, $value);
                break;
            case 'double':
                $this->writeIntegerDouble($varName, $value);
                break;
        }
    }

    protected function writeIntegerAssign($varName,$value){
        $varZval=$this->function->getZvalIR($varName);
        $ZvalIR=LLVMZval::zval()->getStructIR();
        $fromRegister='%'.$this->function->getRegisterSerial();
        $this->writeDebugInfo("Init Zval");
        $this->function->writeOpLineIR("$fromRegister = load ".LLVMZval::zval('**')." $varZval, align ".LLVMZval::zval('*')->size());
        $this->writeDebugInfo("Assign Integer $value");
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_ASSIGN_INTEGER,$fromRegister,$value));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_INTEGER);
    }

    protected function writeIntegerDouble($varName,$value){
        $varZval=$this->function->getZvalIR($varName);
        $ZvalIR=LLVMZval::zval()->getStructIR();
        $fromRegister='%'.$this->function->getRegisterSerial();
        $this->writeDebugInfo("Init Zval");
        $this->function->writeOpLineIR("$fromRegister = load ".LLVMZval::zval('**')." $varZval, align ".LLVMZval::zval('*')->size());
        $this->writeDebugInfo("Assign Float $value");
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_ASSIGN_DOUBLE,$fromRegister,$value));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_DOUBLE);
    }

    protected function writeVarAssign($op1VarName, $op2VarName) {
        $this->writeDebugInfo("$op1VarName <= (var) $op2VarName");
    }

}
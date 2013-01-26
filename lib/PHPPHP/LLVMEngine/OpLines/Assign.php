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
                $this->writeAssignInteger($varName, $value);
                break;
            case 'double':
                $this->writeAssignDouble($varName, $value);
                break;
            case 'string':
                $this->writeAssignString($varName, $value);
                break;
        }
    }

    protected function writeAssignString($varName,$value){
        $varZval=$this->function->getZvalIR($varName);
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign String $value");
        $constant=$this->function->writeConstant($value);
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_ASSIGN_STRING,$varZval,strlen($value),$constant->ptr()));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_STRING);
    }

    protected function writeAssignInteger($varName,$value){
        $varZval=$this->function->getZvalIR($varName);
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Integer $value");
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_ASSIGN_INTEGER,$varZval,$value));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_INTEGER);
    }

    protected function writeAssignDouble($varName,$value){
        $varZval=$this->function->getZvalIR($varName);
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Float $value");
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_ASSIGN_DOUBLE,$varZval,$value));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_DOUBLE);
    }

    protected function writeVarAssign($op1VarName, $op2VarName) {
        $this->writeDebugInfo("$op1VarName <= (var) $op2VarName");
    }

}
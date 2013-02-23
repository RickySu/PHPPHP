<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\LLVMEngine\Register;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

trait VarAssign {

    protected function writeImmediateValueAssign(LLVMZval $op1Zval, $value) {
        $valueType = gettype($value);
        $this->writeDebugInfo("$op1Zval <= ($valueType)");
        switch ($valueType) {
            case 'integer':
                $this->writeAssignInteger($op1Zval, $value);
                break;
            case 'double':
                $this->writeAssignDouble($op1Zval, $value);
                break;
            case 'string':
                $this->writeAssignString($op1Zval, $value);
                break;
            case 'boolean':
                $this->writeAssignBoolean($op1Zval, $value);
                break;
            case 'array':
                $this->writeAssignEmptyArray($op1Zval);
                break;
            case 'NULL':
                $this->writeAssignNULL($op1Zval, $value);
                break;
            default:
                break;
        }
    }

    protected function writeAssignNULL(LLVMZval $varZval, $value) {
        $this->gcVarZval($varZval);
        $returnZValRegister = $varZval->getPtrRegister();
        return $returnZValRegister;
    }

    protected function writeAssignString(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign String ".  str_replace(array("\r","\n"), array('\\r','\\n'), $value));
        $returnZValRegister = $this->function->getRegisterSerial();
        $constant = $this->function->writeConstant($value);
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_STRING, LLVMZval::getGCList(), $varZval->getPtrRegister(), strlen($value), $constant->ptr());
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignInteger(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Integer $value");
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_INTEGER, LLVMZval::getGCList(), $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignBoolean(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Boolean $value");
        if(is_bool($value)){
            $value=(int)$value;
        }
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_BOOLEAN, LLVMZval::getGCList(), $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignDouble(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Float $value");
        if (is_double($value) && ($value == floor($value))) {
            $value.='.0';
        }
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_DOUBLE, LLVMZval::getGCList(), $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeVarAssign(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $this->writeDebugInfo("$op1Zval <= (var) $op2Zval");
        $op1ZvalPtr=$this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ZVAL, LLVMZval::getGCList(), $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister());
        $op1Zval->savePtrRegister($op1ZvalPtr);
        return $op1ZvalPtr;
    }


    protected function writeAssignEmptyArray(LLVMZval $op1Zval) {
        $this->writeDebugInfo("$op1Zval <= (array)");
        $this->gcVarZval($op1Zval,false);
        $op1ZvalPtr=$this->function->InternalModuleCall(InternalModule::ZVAL_INIT, LLVMZval::getGCList());
        $this->function->InternalModuleCall(InternalModule::ZVAL_INIT_ARRAY, $op1ZvalPtr);
        $op1Zval->savePtrRegister($op1ZvalPtr);
        return $op1ZvalPtr;
    }

    protected function writeAssignNextElementArrayVar(LLVMZval $arrayZval,LLVMZval $valueZval){
        $this->writeDebugInfo("{$arrayZval}[] <= (zval)");
        $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ARRAY_NEXT_ELEMENT, $arrayZval->getPtrRegister(),$valueZval->getPtrRegister());
    }

}
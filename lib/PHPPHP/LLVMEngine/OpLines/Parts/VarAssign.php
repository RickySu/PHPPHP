<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

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
        $this->writeDebugInfo("Assign String " . str_replace(array("\r", "\n"), array('\\r', '\\n'), $value));
        $returnZValRegister = $this->function->getRegisterSerial();
        $constant = $this->function->writeConstant($value);
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_STRING, $varZval->getPtrRegister(), strlen($value), $constant->ptr());
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignInteger(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Integer $value");
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_INTEGER, $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignBoolean(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Boolean $value");
        if (is_bool($value)) {
            $value = (int) $value;
        }
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_BOOLEAN, $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignDouble(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Float $value");
        if (is_double($value) && ($value == floor($value))) {
            $value.='.0';
        }
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_DOUBLE, $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeVarAssign(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $this->writeDebugInfo("$op1Zval <= (var) $op2Zval");
        $op1ZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ZVAL, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister());
        $op1Zval->savePtrRegister($op1ZvalPtr);
        return $op1ZvalPtr;
    }

    protected function writeVarAssignRef(LLVMZval $op1Zval,LLVMZval $op2Zval){
        $this->writeDebugInfo("$op1Zval <= (var ref) $op2Zval");

        $cmpResult = $this->function->getRegisterSerial();
        $op1ZvalPtr = $op1Zval->getPtrRegister();

        $this->function->writeOpLineIR("$cmpResult = icmp ne " . LLVMZval::zval('*') . " $op1ZvalPtr, null");
        $LabelTrue = "{$this->function->getRegisterSerial()}_label_true";
        $LabelFalse = "{$this->function->getRegisterSerial()}_label_false";
        $this->function->writeOpLineIR("br i1 $cmpResult, label $LabelTrue, label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelTrue, 1) . ':');
        //$op1Zval need GC
        $this->function->InternalModuleCall(InternalModule::ZVAL_GC, $op1ZvalPtr);
        $this->function->writeOpLineIR("br label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelFalse, 1) . ':');
        $op1ZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_REF, $op2Zval->getPtrRegister());
        $op1Zval->savePtrRegister($op1ZvalPtr);
        $op2Zval->savePtrRegister($op1ZvalPtr);
    }
}
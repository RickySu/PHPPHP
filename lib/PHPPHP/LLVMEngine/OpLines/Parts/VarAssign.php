<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\Engine\Zval;
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
        $this->writeDebugInfo("Assign String $value");
        $returnZValRegister = $this->function->getRegisterSerial();
        $constant = $this->function->writeConstant($value);
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_STRING, LLVMZval::ZVAL_GC_LIST, $varZval->getPtrRegister(), strlen($value), $constant->ptr());
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignInteger(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Integer $value");
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_INTEGER, LLVMZval::ZVAL_GC_LIST, $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignBoolean(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Boolean $value");
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_BOOLEAN, LLVMZval::ZVAL_GC_LIST, $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeAssignDouble(LLVMZval $varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Float $value");
        if (is_double($value) && ($value == floor($value))) {
            $value.='.0';
        }
        $returnZValRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_DOUBLE, LLVMZval::ZVAL_GC_LIST, $varZval->getPtrRegister(), ($value == '' ? 0 : $value));
        $varZval->savePtrRegister($returnZValRegister);
        return $returnZValRegister;
    }

    protected function writeVarAssign(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $this->writeDebugInfo("$op1Zval <= (var) $op2Zval");
        $op1ZvalPtr = $op1Zval->getPtrRegister();
        $op2ZvalPtr = $op2Zval->getPtrRegister();
        $cmpResult = $this->function->getRegisterSerial();
        $LabelTrue = "{$this->function->getRegisterSerial()}_label_true";
        $LabelFalse = "{$this->function->getRegisterSerial()}_label_false";

        //check $op1Zval is initialized?

        $this->function->writeOpLineIR("$cmpResult = icmp ne " . LLVMZval::zval('*') . " $op1ZvalPtr, null");
        $this->function->writeOpLineIR("br i1 $cmpResult, label $LabelTrue, label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelTrue, 1) . ':');

        //$op1Zval need GC
        $this->function->InternalModuleCall(InternalModule::ZVAL_GC, LLVMZval::ZVAL_GC_LIST, $op1ZvalPtr);
        $this->function->writeOpLineIR("br label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelFalse, 1) . ':');

        list($isRefRegister, $isRefRegisterPtr) = $this->writeGetIsRefIR($op2ZvalPtr);

        $cmpResult = $this->function->getRegisterSerial();
        $LabelTrue = "{$this->function->getRegisterSerial()}_label_true";
        $LabelElse = "{$this->function->getRegisterSerial()}_label_else";
        $LabelEndif = "{$this->function->getRegisterSerial()}_label_endif";
        $this->function->writeOpLineIR("$cmpResult = icmp eq " . LLVMZval::zval()->getStructIR()->getElement('is_ref') . " $isRefRegister, 1");
        $this->function->writeOpLineIR("br i1 $cmpResult, label $LabelTrue, label $LabelElse");

        $this->function->writeOpLineIR(substr($LabelTrue, 1) . ':');
        //need copy on write
        $op1ZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_COPY, LLVMZval::ZVAL_GC_LIST, $op2ZvalPtr);
        $op1Zval->savePtrRegister($op1ZvalPtr);

        $this->function->writeOpLineIR("br label $LabelEndif");

        $this->function->writeOpLineIR(substr($LabelElse, 1) . ':');
        //not is_ref
        list($refCountRegister, $refCountRegisterPtr) = $this->writeGetRefCountIR($op2ZvalPtr);

        $refCountType = LLVMZval::zval()->getStructIR()->getElement('refcount');
        $refCountRegisterAdded = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$refCountRegisterAdded = add $refCountType $refCountRegister, 1");
        $this->function->writeOpLineIR("store $refCountType $refCountRegisterAdded, $refCountType* $refCountRegisterPtr, align " . $refCountType->size());
        $op1Zval->savePtrRegister($op2ZvalPtr);
        $this->function->writeOpLineIR("br label $LabelEndif");

        $this->function->writeOpLineIR(substr($LabelEndif, 1) . ':');
        return $op2ZvalPtr;
    }

}
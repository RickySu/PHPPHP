<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Assign extends OpLine {

    public function write() {
        parent::write();
        $op1Var = $this->opCode->op1->getImmediateZval();
        $op2Var = $this->opCode->op2->getImmediateZval();
        if ($op1Var instanceof Zval\Value) {
            if (!isset($op1Var->TempVarName)) {
                $op1VarName = substr($this->function->getRegisterSerial(), 1);
                $op1Var->TempVarName = $op1VarName;
            } else {
                $op1VarName = $op1Var->TempVarName;
            }
            $op1Zval = $this->function->getZvalIR($op1VarName, true, true);
        } else {
            $op1VarName = $op1Var->getName();
        }
        if ($op2Var instanceof Zval\Value) {
            $op1Zval = $this->function->getZvalIR($op1VarName);
            if (isset($op2Var->TempVarName)) {
                $op2VarName = $op2Var->TempVarName;
                $op2Zval = $this->function->getZvalIR($op2VarName, true, true);
                $this->writeVarAssign($op1Zval, $op2Zval);
            } else {
                $this->writeImmediateValueAssign($op1Zval, $op2Var);
            }
        } else {
            $op1Zval = $this->function->getZvalIR($op1VarName, false);
            $op2Zval = $this->function->getZvalIR($op2Var->getName());
            $this->writeVarAssign($op1Zval, $op2Zval);
        }
    }

    protected function writeImmediateValueAssign($op1Zval, $value) {
        $this->writeDebugInfo("$op1Zval <= ({$value->getType()})");
        $op1ZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1ZvalPtr = load " . LLVMZval::zval('**') . " $op1Zval, align " . LLVMZval::zval('*')->size());
        switch ($value->getType()) {
            case 'integer':
                $this->writeAssignInteger($op1Zval, $op1ZvalPtr, $value->getValue());
                break;
            case 'double':
                $this->writeAssignDouble($op1Zval, $op1ZvalPtr, $value->getValue());
                break;
            case 'string':
                $this->writeAssignString($op1Zval, $op1ZvalPtr, $value->getValue());
                break;
        }
    }

    protected function writeAssignString($varZval, $varZvalPtr, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign String $value");
        $returnZValRegister = $this->function->getRegisterSerial();
        $constant = $this->function->writeConstant($value);
        $this->function->writeOpLineIR("$returnZValRegister = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_STRING, '%zvallist', $varZvalPtr, strlen($value), $constant->ptr()));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_STRING);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnZValRegister, " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
    }

    protected function writeAssignInteger($varZval, $varZvalPtr, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Integer $value");
        $returnZValRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$returnZValRegister = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_INTEGER, '%zvallist', $varZvalPtr, $value));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_INTEGER);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnZValRegister, " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
    }

    protected function writeAssignDouble($varZval, $varZvalPtr, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Float $value");
        $returnZValRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$returnZValRegister = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_DOUBLE, '%zvallist', $varZvalPtr, $value));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_DOUBLE);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnZValRegister, " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
    }

    protected function writeVarAssign($op1Zval, $op2Zval) {
        $this->writeDebugInfo("$op1Zval <= (var) $op2Zval");
        $op1ZvalPtr = $this->function->getRegisterSerial();
        $op2ZvalPtr = $this->function->getRegisterSerial();
        $cmpResult = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1ZvalPtr = load " . LLVMZval::zval('**') . " $op1Zval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR("$op2ZvalPtr = load " . LLVMZval::zval('**') . " $op2Zval, align " . LLVMZval::zval('*')->size());

        $LabelTrue="{$this->function->getRegisterSerial()}_label_true";
        $LabelFalse="{$this->function->getRegisterSerial()}_label_false";

        //check $op1Zval is initialized?

        $this->function->writeOpLineIR("$cmpResult = icmp ne ".LLVMZval::zval('*')." $op1ZvalPtr, null");
        $this->function->writeOpLineIR("br i1 $cmpResult, label $LabelTrue, label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelTrue,1).':');
        //$op1Zval need GC
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_GC, '%zvallist', $op1ZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_GC);
        $this->function->writeOpLineIR("br label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelFalse,1).':');

        list($isRefRegister, $isRefRegisterPtr) = $this->writeGetIsRefIR($op2ZvalPtr);
        $cmpResult = $this->function->getRegisterSerial();
        $LabelTrue="{$this->function->getRegisterSerial()}_label_true";
        $LabelElse="{$this->function->getRegisterSerial()}_label_else";
        $LabelEndif="{$this->function->getRegisterSerial()}_label_endif";
        $this->function->writeOpLineIR("$cmpResult = icmp eq ".LLVMZval::zval()->getStructIR()->getElement('is_ref')." $isRefRegister, 1");
        $this->function->writeOpLineIR("br i1 $cmpResult, label $LabelTrue, label $LabelElse");

        $this->function->writeOpLineIR(substr($LabelTrue,1).':');
        //need copy on write
        $op1ZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1ZvalPtr = ".InternalModule::call(InternalModule::ZVAL_COPY, '%zvallist', $op2ZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_COPY);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $op1ZvalPtr, " . LLVMZval::zval('**') . " $op1Zval, align " . LLVMZval::zval('*')->size());

        $this->function->writeOpLineIR("br label $LabelEndif");

        $this->function->writeOpLineIR(substr($LabelElse,1).':');
        //not is_ref
        list($refCountRegister, $refCountRegisterPtr) = $this->writeGetRefCountIR($op2ZvalPtr);

        $refCountType = LLVMZval::zval()->getStructIR()->getElement('refcount');
        $refCountRegisterAdded = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$refCountRegisterAdded = add $refCountType $refCountRegister, 1");
        $this->function->writeOpLineIR("store $refCountType $refCountRegisterAdded, $refCountType* $refCountRegisterPtr, align " . $refCountType->size());
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $op2ZvalPtr, " . LLVMZval::zval('**') . " $op1Zval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR("br label $LabelEndif");

        $this->function->writeOpLineIR(substr($LabelEndif,1).':');
    }

}
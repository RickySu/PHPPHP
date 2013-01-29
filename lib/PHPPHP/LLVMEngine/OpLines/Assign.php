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
        switch ($value->getType()) {
            case 'integer':
                $this->writeAssignInteger($op1Zval, $value->getValue());
                break;
            case 'double':
                $this->writeAssignDouble($op1Zval, $value->getValue());
                break;
            case 'string':
                $this->writeAssignString($op1Zval, $value->getValue());
                break;
        }
    }

    protected function writeAssignString($varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign String $value");
        $varZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$varZvalPtr = load " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
        $returnZValRegister = $this->function->getRegisterSerial();
        $constant = $this->function->writeConstant($value);
        $this->function->writeOpLineIR("$returnZValRegister = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_STRING, '%zvallist', $varZvalPtr, strlen($value), $constant->ptr()));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_STRING);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnZValRegister, " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
    }

    protected function writeAssignInteger($varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Integer $value");
        $varZvalPtr = $this->function->getRegisterSerial();
        $returnZValRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$varZvalPtr = load " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR("$returnZValRegister = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_INTEGER, '%zvallist', $varZvalPtr, $value));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_INTEGER);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnZValRegister, " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
    }

    protected function writeAssignDouble($varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Assign Float $value");
        $varZvalPtr = $this->function->getRegisterSerial();
        $returnZValRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$varZvalPtr = load " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR("$returnZValRegister = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_DOUBLE, '%zvallist', $varZvalPtr, $value));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_DOUBLE);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnZValRegister, " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
    }

    protected function writeVarAssign($op1Zval, $op2Zval) {
        $this->writeDebugInfo("$op1Zval <= (var) $op2Zval");
        $op2ZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op2ZvalPtr = load " . LLVMZval::zval('**') . " $op2Zval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $op2ZvalPtr, " . LLVMZval::zval('**') . " $op1Zval, align " . LLVMZval::zval('*')->size());

        list($refCountRegister, $refCountRegisterPtr) = $this->writeGetRefCountIR($op2ZvalPtr);

        $refCountType = LLVMZval::zval()->getStructIR()->getElement('refcount');
        $refCountRegisterAdded = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$refCountRegisterAdded = add $refCountType $refCountRegister, 1");
        $this->function->writeOpLineIR("store $refCountType $refCountRegisterAdded, $refCountType* $refCountRegisterPtr, align " . $refCountType->size());
    }

}
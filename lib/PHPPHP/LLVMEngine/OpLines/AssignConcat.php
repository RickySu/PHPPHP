<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignConcat extends Assign {

    public function write() {
        parent::write();
    }

    protected function writeAssignString($varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Concat Assign String $value");
        $this->writeDebugInfo("assign $varZval.=$value");
        $varZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$varZvalPtr = load " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
        $returnZValRegister = $this->function->getRegisterSerial();
        $constant = $this->function->writeConstant($value);
        $this->function->writeOpLineIR("$returnZValRegister = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_CONCAT_STRING, '%zvallist', $varZvalPtr, strlen($value), $constant->ptr()));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_CONCAT_STRING);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnZValRegister, " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
    }

    protected function writeAssignInteger($varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Concat Assign Integer $value");
        $this->writeAssignString($varZval, "$value");
    }

    protected function writeAssignDouble($varZval, $value) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Concat Assign Double $value");
        $this->writeAssignString($varZval, "$value");
    }

    protected function writeVarAssign($op1Zval, $op2Zval) {
        $this->writeDebugInfo("Init Zval");
        $this->writeDebugInfo("Concat Assign Zval $op2Zval");
        $this->writeDebugInfo("assign $op1Zval.=$op2Zval");
        $op1ZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1ZvalPtr = load " . LLVMZval::zval('**') . " $op1Zval, align " . LLVMZval::zval('*')->size());
        $op2ZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op2ZvalPtr = load " . LLVMZval::zval('**') . " $op2Zval, align " . LLVMZval::zval('*')->size());
        $returnZValRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$returnZValRegister = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, '%zvallist', $op1ZvalPtr, $op2ZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL);
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnZValRegister, " . LLVMZval::zval('**') . " $op1Zval, align " . LLVMZval::zval('*')->size());
    }

}
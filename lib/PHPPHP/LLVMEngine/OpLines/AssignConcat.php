<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignConcat extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $op1ZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister());
        $op1Zval->savePtrRegister($op1ZvalPtr);
    }

    protected function writeZvalValue(LLVMZval $op1Zval, $value) {
        if ($value === NULL || $value === "") {
            return;
        }
        $constant = $this->function->writeConstant($value);
        $op1ZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_STRING, $op1Zval->getPtrRegister(), strlen($value), $constant->ptr());
        $op1Zval->savePtrRegister($op1ZvalPtr);
    }

    protected function writeValueValue($value1, $value2) {
        $this->opCode->op1->getImmediateZval()->setValue($value1 . $value2);
    }

    protected function writeValueZval($value, LLVMZval $op1Zval) {
        $tempZval = $this->makeTempZval($value, false);
        $tempZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, $tempZval->getPtrRegister(), $op1Zval->getPtrRegister());
        $tempZval->savePtrRegister($tempZvalPtr);
        $this->opCode->op1->getImmediateZval()->setValue($tempZval);
    }

}
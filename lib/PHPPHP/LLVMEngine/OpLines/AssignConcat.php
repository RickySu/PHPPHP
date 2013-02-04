<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignConcat extends OpLine {

    use Parts\PrepareOpZval;

    public function write() {
        parent::write();
        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        if (!($op2Zval instanceof LLVMZval)) {
            $op1ZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_STRING, LLVMZval::ZVAL_GC_LIST, $op1Zval->getPtrRegister(), $op2Zval);
        } else {
            $op1ZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, LLVMZval::ZVAL_GC_LIST, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister());
        }
        $op1Zval->savePtrRegister($op1ZvalPtr);
    }

}
<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Concat extends OpLine {

    use Parts\VarAssign,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $resultZval = $this->prepareResultZval();
        $this->writeVarAssign($resultZval, $op1Zval);
        $resultZvalPtr =$this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, LLVMZval::ZVAL_GC_LIST, $resultZval->getPtrRegister(), $op2Zval->getPtrRegister());
        $resultZval->savePtrRegister($resultZvalPtr);
    }
}
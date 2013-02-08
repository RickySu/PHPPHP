<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class EchoOp extends OpLine {
use Parts\PrepareOpZval;
    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
    }

    protected function writeZval(LLVMZval $opZval){
        $this->writeDebugInfo("echo (var) $opZval");
        $this->function->InternalModuleCall(InternalModule::T_ECHO_ZVAL, $opZval->getPtrRegister());
    }

    protected function writeValue($value){
        $valueType = gettype($value);
        $this->writeDebugInfo("echo ($valueType)");
        $constant = $this->function->writeConstant($value);
        $this->function->InternalModuleCall(InternalModule::T_ECHO, strlen($value), $constant->ptr());
    }

}
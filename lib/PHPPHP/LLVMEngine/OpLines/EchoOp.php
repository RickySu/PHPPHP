<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class EchoOp extends OpLine {

    public function write() {
        $op1Var = $this->opCode->op1->getImmediateZval();
        if ($op1Var instanceof Zval\Value) {
            if (isset($op1Var->TempVarName)) {
                $op1Zval = $this->function->getZvalIR($op1Var->TempVarName, true, true);
                $this->writeVarEcho($op1Zval);
            } else {
                $this->writeImmediateValueEcho($op1Var->getValue());
            }
        } else {
            $op1Zval = $this->function->getZvalIR($op1Var->getName());
            $this->writeVarEcho($op1Zval);
        }
    }

    protected function writeImmediateValueEcho($value) {
        $valueType = gettype($value);
        $this->writeDebugInfo("echo ($valueType)");
        $constant = $this->function->writeConstant($value);
        $this->function->InternalModuleCall(InternalModule::T_ECHO, strlen($value), $constant->ptr());
    }

    protected function writeVarEcho(LLVMZval $varZval) {
        $this->writeDebugInfo("echo (var) $varZval");
        $this->function->InternalModuleCall(InternalModule::T_ECHO_ZVAL, $varZval->getPtrRegister());
    }

}
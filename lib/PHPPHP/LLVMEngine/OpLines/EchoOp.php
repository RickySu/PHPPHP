<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class EchoOp extends OpLine {

    public function write() {
        parent::write();
        $op1Var = $this->opCode->op1->getImmediateZval();
        if ($op1Var instanceof Zval\Value) {
            if (isset($op1Var->TempVarName)) {
                $op1Zval=$this->function->getZvalIR($op1Var->TempVarName, true, true);
                $this->writeVarEcho($op1Zval);
            } else {
                $this->writeImmediateValueEcho($op1Var->getValue());
            }
        } else {
            $op1Zval=$this->function->getZvalIR($op1Var->getName());
            $this->writeVarEcho($op1Zval);
        }
    }

    protected function writeImmediateValueEcho($value) {
        $valueType = gettype($value);
        $this->writeDebugInfo("echo ($valueType)");
        $constant = $this->function->writeConstant($value);
        $IR = InternalModule::call(InternalModule::T_ECHO, strlen($value), $constant->ptr());
        $this->function->writeOpLineIR($IR);
        $this->function->writeUsedFunction(InternalModule::T_ECHO);
    }

    protected function writeVarEcho($varZval) {
        $this->writeDebugInfo("echo (var) $varZval");
        $varZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$varZvalPtr = load " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::T_ECHO_ZVAL, $varZvalPtr));
        $this->function->writeUsedFunction(InternalModule::T_ECHO_ZVAL);
    }

}
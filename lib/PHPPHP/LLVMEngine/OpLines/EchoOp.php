<?php
namespace PHPPHP\LLVMEngine\OpLines;
use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class EchoOp extends OpLine{

    public function write() {
        parent::write();
        $op1Zval = $this->opCode->op1->getImmediateZval();
        if ($op1Zval instanceof Zval\Value) {
            $this->writeImmediateValueEcho($op1Zval->getValue());
        } else {
            $this->writeVarEcho($op1Zval->getName());
        }
    }
    protected function writeImmediateValueEcho($value) {
        $valueType = gettype($value);
        $this->writeDebugInfo("echo ($valueType)");
        $constant=$this->function->writeConstant($value);
        $IR=InternalModule::call(InternalModule::T_ECHO,strlen($value),$constant->ptr());
        $this->function->writeOpLineIR($IR);
        $this->function->writeUsedFunction(InternalModule::T_ECHO);
    }

    protected function writeVarEcho($varName) {
        $this->writeDebugInfo("echo (var) $varName");
        $varZval=$this->function->getZvalIR($varName);
        $this->writeDebugInfo("use $varZval");
        $this->writeDebugInfo("Init Zval");
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::T_ECHO_ZVAL,$varZval));
        $this->function->writeUsedFunction(InternalModule::T_ECHO_ZVAL);
    }

}
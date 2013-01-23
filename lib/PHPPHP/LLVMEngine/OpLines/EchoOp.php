<?php
namespace PHPPHP\LLVMEngine\OpLines;
use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Internal\Module;

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
        $constant=$this->module->writeConstant($value);
        $IR=Module::call(Module::T_ECHO,$constant->size(),$constant->ptr())."\n";
        $this->module->writeOpLineIR($IR);
        $this->module->getWriter()->writeUsedModule(Module::T_ECHO);
    }

    protected function writeVarEcho($varName) {
        $this->writeDebugInfo("echo (var)");
    }
}
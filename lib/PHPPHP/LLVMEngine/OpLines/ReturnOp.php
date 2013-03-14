<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Writer\FunctionWriter;

class ReturnOp extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
    }

    /**
     * @return LLVMZval
     */
    protected function getReturnZval() {
        $returnZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$returnZvalRegister = load " . LLVMZval::zval('**') . " " . FunctionWriter::RETVAL, ", align " . BaseType::void('*')->size());
        $returnZval = new LLVMZval(NULL, false, true, $this->function);
        $returnZval->savePtrRegister($returnZvalRegister);
        return $returnZval;
    }

    protected function saveReturnZval($register) {
        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $register, " . LLVMZval::zval('**') . " " . FunctionWriter::RETVAL, ", align " . BaseType::void('*')->size());
    }

    protected function writeZval(LLVMZval $varZval) {
        $returnZval = $this->getReturnZval();
        $this->writeVarAssign($returnZval, $varZval);
        $this->saveReturnZval($returnZval->getPtrRegister());
        $this->jumpEnd();
    }

    protected function writeValue($value) {
        if ($value !== NULL) {
            $returnZval = $this->getReturnZval();
            $this->writeImmediateValueAssign($returnZval, $value);
            $this->saveReturnZval($returnZval->getPtrRegister());
        }
        $this->jumpEnd();
    }

    protected function jumpEnd() {
        $this->function->writeOpLineIR("br label %end_return");
    }

}

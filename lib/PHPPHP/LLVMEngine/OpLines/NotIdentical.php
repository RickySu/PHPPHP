<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class NotIdentical extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->writeNotIdentical($resultZval, $op1Zval, $op2Zval);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval !== $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeNotIdentical(LLVMZval $resultZval, LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $EqualRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_EQUAL_EXACT, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister());
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = xor " . BaseType::long() . " $EqualRegister, 1");

        $this->writeAssignBoolean($resultZval, $resultZvalRegister);
    }

}
<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class JumpIfNot extends OpLine {
    use Parts\PrepareOpZval;

    public function write() {
        parent::write();

        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);

        if ($op1Zval instanceof LLVMZval) {
            $this->testNULL($op1Zval);

            $this->writeIfNot($op1Zval);

        } else {
            if (!$op1Zval) {
                $this->function->writeJumpLabelIR($this->opCode->op2);
            }
        }
    }

    protected function writeIfNot(LLVMZval $op1Zval) {
        $op1ZvalValue=$this->function->InternalModuleCall(InternalModule::ZVAL_DOUBLE_VALUE,$op1Zval->getPtrRegister());
        $isZero = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isZero = fcmp oeq ".BaseType::double()." $op1ZvalValue, 0.0");
        $isZeroLabel=substr($this->function->getRegisterSerial(),1)."_isZero";
        $isZeroEndifLabel=substr($this->function->getRegisterSerial(),1)."_Endif";
        $this->function->writeOpLineIR("br i1 $isZero, label  %$isZeroLabel, label %$isZeroEndifLabel");
        $this->function->writeOpLineIR("$isZeroLabel:");
        $this->function->writeJumpLabelIR($this->opCode->op2);
        $this->function->writeOpLineIR("br label %$isZeroEndifLabel");
        $this->function->writeOpLineIR("$isZeroEndifLabel:");
    }

    protected function testNULL(LLVMZval $op1Zval) {
        $op1ZvalPtr = $op1Zval->getPtrRegister();
        $isNULL = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isNULL = icmp eq ".LLVMZval::zval('*')." {$op1Zval->getPtrRegister()}, null");
        $isNULLLabel=substr($this->function->getRegisterSerial(),1)."_isNULL";
        $isNULLEndifLabel=substr($this->function->getRegisterSerial(),1)."_Endif";
        $this->function->writeOpLineIR("br i1 $isNULL, label  %$isNULLLabel, label %$isNULLEndifLabel");
        $this->function->writeOpLineIR("$isNULLLabel:");
        $this->function->writeJumpLabelIR($this->opCode->op2);
        $this->function->writeOpLineIR("br label %$isNULLEndifLabel");
        $this->function->writeOpLineIR("$isNULLEndifLabel:");
    }

}

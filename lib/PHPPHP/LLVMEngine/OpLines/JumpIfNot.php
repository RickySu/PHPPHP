<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class JumpIfNot extends OpLine {

    use Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
        $this->gcTempZval();
    }

    protected function writeValue($value) {
        if (!$value) {
            $this->function->writeJumpLabelIR($this->opCode->op2);
        }
    }

    protected function writeZval(LLVMZval $opZval) {
        $isFalse = $this->function->InternalModuleCall(InternalModule::ZVAL_TEST_FALSE, $opZval->getPtrRegister());
        $isFalseResult = $this->function->getRegisterSerial();
        $ifSerial = substr($this->function->getRegisterSerial(),1);
        $LabelIfNot = "Label_IfNot_$ifSerial";
        $LabelEndIf = "Label_EndIf_$ifSerial";
        $this->function->writeOpLineIR("$isFalseResult = icmp eq ".BaseType::long()." $isFalse, 1");
        $this->function->writeOpLineIR("br i1 $isFalseResult, label  %$LabelIfNot, label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelIfNot:");
        $this->function->writeJumpLabelIR($this->opCode->op2);
        $this->function->writeOpLineIR("br label  %$LabelIfNot");
        $this->function->writeOpLineIR("$LabelEndIf:");
    }

    protected function writeIfNot(LLVMZval $op1Zval) {
        $op1ZvalValue = $this->function->InternalModuleCall(InternalModule::ZVAL_DOUBLE_VALUE, $op1Zval->getPtrRegister());
        $isZero = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isZero = fcmp oeq " . BaseType::double() . " $op1ZvalValue, 0.0");
        $isZeroLabel = substr($this->function->getRegisterSerial(), 1) . "_isZero";
        $isZeroEndifLabel = substr($this->function->getRegisterSerial(), 1) . "_Endif";
        $this->gcTempZval();
        $this->function->writeOpLineIR("br i1 $isZero, label  %$isZeroLabel, label %$isZeroEndifLabel");
        $this->function->writeOpLineIR("$isZeroLabel:");
        $this->function->writeJumpLabelIR($this->opCode->op2);
        $this->function->writeOpLineIR("br label %$isZeroEndifLabel");
        $this->function->writeOpLineIR("$isZeroEndifLabel:");
    }

}

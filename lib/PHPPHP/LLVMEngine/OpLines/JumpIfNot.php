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
        $ifSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfNot = "Label_IfNot_$ifSerial";
        $LabelEndIf = "Label_EndIf_$ifSerial";
        $this->function->writeOpLineIR("$isFalseResult = trunc " . BaseType::long() . " $isFalse to i1");
        $this->function->writeOpLineIR("br i1 $isFalseResult, label  %$LabelIfNot, label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelIfNot:");
        $this->function->writeJumpLabelIR($this->opCode->op2);
        $this->function->writeOpLineIR("br label  %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
    }

}

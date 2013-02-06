<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class JumpIf extends OpLine {
    use Parts\PrepareOpZval;

    public function write() {
        parent::write();

        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);

        if ($op1Zval instanceof LLVMZval) {
            $this->writeIf($op1Zval);
        } else {
            if (!$op1Zval) {
                $this->function->writeJumpLabelIR($this->opCode->op2);
            }
        }
    }

    protected function writeIf(LLVMZval $op1Zval) {
        $op1ZvalValue=$this->function->InternalModuleCall(InternalModule::ZVAL_DOUBLE_VALUE,$op1Zval->getPtrRegister());
        $isNotZero = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isNotZero = fcmp one ".BaseType::double()." $op1ZvalValue, 0.0");
        $isNotZeroLabel=substr($this->function->getRegisterSerial(),1)."_isNotZero";
        $isNotZeroEndifLabel=substr($this->function->getRegisterSerial(),1)."_Endif";
        $this->function->writeOpLineIR("br i1 $isNotZero, label  %$isNotZeroLabel, label %$isNotZeroEndifLabel");
        $this->function->writeOpLineIR("$isNotZeroLabel:");
        $this->gcTempZval();
        $this->function->writeJumpLabelIR($this->opCode->op2);
        $this->function->writeOpLineIR("br label %$isNotZeroEndifLabel");
        $this->function->writeOpLineIR("$isNotZeroEndifLabel:");
    }

}

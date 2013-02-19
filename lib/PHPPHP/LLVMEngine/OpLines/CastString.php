<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class CastString extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
        $this->gcTempZval();
    }

    protected function writeValue($value1) {
        $this->setResult((string) $value1);
    }

    protected function writeZval(LLVMZval $opZval) {
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeVarAssign($resultZval, $opZval);
        $this->function->InternalModuleCall(InternalModule::ZVAL_CONVERT_STRING, $resultZval->getPtrRegister());
        $this->setResult($resultZval);
    }

}
<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class CastDouble extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        if (!$this->opCode->result->markUnUsed) {
            $this->prepareOpZval($this->opCode->op1);
        }
        $this->gcTempZval();
    }

    protected function writeValue($value1) {
        $this->setResult((double) $value1);
    }

    protected function writeZval(LLVMZval $opZval) {
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeVarAssign($resultZval, $opZval);
        $this->function->InternalModuleCall(InternalModule::ZVAL_CONVERT_DOUBLE, $resultZval->getPtrRegister());
        $this->setResult($resultZval);
    }

}
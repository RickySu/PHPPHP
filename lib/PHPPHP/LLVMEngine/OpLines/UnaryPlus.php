<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;

class UnaryPlus extends OpLine {

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
        $this->setResult($value1);
    }

    protected function writeZval(LLVMZval $opZval) {
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeVarAssign($resultZval, $opZval);
        $this->setResult($resultZval);
    }

}

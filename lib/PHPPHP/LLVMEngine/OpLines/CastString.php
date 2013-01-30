<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class CastString extends OpLine {

    use Parts\Convert;

    public function write() {
        parent::write();
        $op1Var = $this->opCode->op1->getImmediateZval();
        if (!isset($this->opCode->result->TempVarName)) {
            $resultVarName = substr($this->function->getRegisterSerial(), 1);
            $this->opCode->result->getImmediateZval()->TempVarName = $resultVarName;
        }
        $resultZval = $this->function->getZvalIR($resultVarName, false, true);
        $op1Zval = $this->function->getZvalIR($op1Var->getName());
        $this->convertString($resultZval,$op1Zval);
    }

}
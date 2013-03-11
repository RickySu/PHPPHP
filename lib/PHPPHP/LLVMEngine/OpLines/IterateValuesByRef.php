<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Internal\Module as InternalModule;
use PHPPHP\LLVMEngine\Zval as LLVMZval;

class IterateValuesByRef extends IterateValues
{
    protected function writeSetValue($iterateObjectVarRegister){
        $valueZval = $this->function->getZvalIR($this->opCode->result->getImmediateZval()->getName());
        $currentValueRegister=$this->function->InternalModuleCall(InternalModule::ZVAL_ITERATE_CURRENT_VALUE,$iterateObjectVarRegister);
        $currentValueZval = new LLVMZval(NULL, false, false, $this->function);
        $currentValueZval->savePtrRegister($currentValueRegister);
        $this->writeVarAssignRef($valueZval, $currentValueZval);
    }
}

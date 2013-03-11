<?php

namespace PHPPHP\LLVMEngine\OpLines;

class IterateByRef extends Iterate
{

    /*public function write()
    {
        parent::write();
        $iterateObjectVarName=$this->function->getInternalVar(substr($this->function->getRegisterSerial(),1), BaseType::void('*'), 'null');
        $this->opCode->result->iterateObjectVarName=$iterateObjectVarName;
        $this->writeExitLoopIfEndofElement($iterateObjectVarName);
        $this->gcTempZval();
    }*/
}

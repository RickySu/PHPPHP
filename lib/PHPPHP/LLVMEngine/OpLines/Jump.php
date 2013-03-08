<?php

namespace PHPPHP\LLVMEngine\OpLines;

class Jump extends OpLine
{
    public function write()
    {
        parent::write();
        $this->function->writeJumpLabelIR($this->opCode->op1);
    }

}

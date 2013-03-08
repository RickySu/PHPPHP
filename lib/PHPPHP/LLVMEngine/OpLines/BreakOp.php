<?php

namespace PHPPHP\LLVMEngine\OpLines;

class BreakOp extends OpLine
{
    public function write()
    {
        parent::write();
        $this->function->writeJumpLabelIR($this->opCode->op1->breakOp);
    }

}

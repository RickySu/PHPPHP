<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;

class UnsetOp extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
        $this->gcTempZval();
    }

    protected function writeValue($value1)
    {
    }

    protected function writeZval(LLVMZval $opZval)
    {
        $this->gcVarZval($opZval);
    }

}

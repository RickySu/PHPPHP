<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class InitFCallByName extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $this->opCode->nextOpCode->InitFCallByNameOp=$this->opCode;
        $this->opCode->FCallParams=array();
        $this->gcTempZval();
    }
}

<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;

class AddArrayElementRef extends AddArrayElement
{
    use Parts\TypeCast,
        Parts\PrepareOpZval,
        Parts\ArrayOp;

    protected $resultZval;

    protected function writeZvalZval(LLVMZval $dimZval, LLVMZval $valueZvalSrc)
    {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssignRef($valueZval, $valueZvalSrc);
        $this->writeAssignVarElementArrayVar($this->resultZval, $valueZval, $dimZval);
    }

    protected function writeValueZval($dimValue, LLVMZval $valueZvalSrc)
    {
        $valueZval = new LLVMZval(NULL, true, false, $this->function);
        $this->writeVarAssignRef($valueZval, $valueZvalSrc);
        $this->writeAssignArray($this->resultZval, $valueZval, $dimValue);
    }

}

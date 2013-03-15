<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Zval as LLVMZval;

class Send extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
        $this->opCode->nextOpCode->InitFCallByNameOp=$this->opCode->InitFCallByNameOp;
    }

    protected function writeZval(LLVMZval $opZval){
        $this->opCode->InitFCallByNameOp->FCallParams[]=$opZval;
    }

    protected function writeValue($value){

        $resultRegister = substr($this->function->getRegisterSerial(),1);
        $resultZval=$this->function->getZvalIR($resultRegister, false, true);
        $this->writeImmediateValueAssign($resultZval, $value);
        $this->opCode->InitFCallByNameOp->FCallParams[]=$resultZval;
    }

}

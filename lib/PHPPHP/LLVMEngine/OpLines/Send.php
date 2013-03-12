<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class Send extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
        $this->opCode->nextOpCode->InitFCallByNameOp=$this->opCode->InitFCallByNameOp;
        $this->gcTempZval();
    }

    protected function writeZval(LLVMZval $opZval){
        $resultRegister = substr($this->function->getRegisterSerial(),1);
        $resultZval=$this->function->getZvalIR($resultRegister, false, true);
        $this->writeVarAssign($resultZval,$opZval);
        $this->opCode->InitFCallByNameOp->FCallParams[]=$resultZval;
    }

    protected function writeValue($value){
        $resultRegister = substr($this->function->getRegisterSerial(),1);
        $resultZval=$this->function->getZvalIR($resultRegister, false, true);
        $this->writeImmediateValueAssign($resultZval, $value);
        $this->opCode->InitFCallByNameOp->FCallParams[]=$resultZval;
    }

}

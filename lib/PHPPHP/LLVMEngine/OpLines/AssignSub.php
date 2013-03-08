<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class AssignSub extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeValueValue($value1, $value2)
    {
        $this->setResult($value1-$value2);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)
    {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = sub " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZval=$this->function->getZvalIR($this->opCode->op1->getName());
        $this->writeAssignInteger($resultZval, $resultRegister);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)
    {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = fsub " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZval=$this->function->getZvalIR($this->opCode->op1->getName());
        $this->writeAssignDouble($resultZval, $resultRegister);
    }

}

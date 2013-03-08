<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class SmallerOrEqual extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        if (!$this->opCode->result->markUnUsed) {
            $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        }
        $this->gcTempZval();
    }

    protected function writeValueValue($value1, $value2)
    {
        $this->setResult($value1 < $value2);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)
    {
        $result = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$result = icmp sle " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeResult($result);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)
    {
        $result = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$result = fcmp ole " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeResult($result);
    }

    protected function writeResult($resultRegister)
    {
        $resultCastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultCastRegister = zext i1 $resultRegister to " . BaseType::long());
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeAssignBoolean($resultZval, $resultCastRegister);
        $this->setResult($resultZval);
    }

}

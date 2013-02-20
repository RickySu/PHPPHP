<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class BitwiseNot extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        if (!$this->opCode->result->markUnUsed) {
            $this->prepareOpZval($this->opCode->op1);
        }
        $this->gcTempZval();
    }

    protected function writeValue($value1) {
        $this->setResult(~$value1);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = xor " . BaseType::long() . " $typeCastOp1ValueRegister, -1");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignInteger($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister) {
        $typeCastOp1ValueIntegerRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$typeCastOp1ValueIntegerRegister = fptosi " . BaseType::double() . " $typeCastOp1ValueRegister to " . BaseType::long());
        return $this->writeIntegerOp($typeCastOp1ValueIntegerRegister);
    }

}

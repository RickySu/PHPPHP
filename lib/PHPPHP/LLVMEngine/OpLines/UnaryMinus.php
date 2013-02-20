<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class UnaryMinus extends OpLine {

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
        $this->setResult(-$value1);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->function->writeOpLineIR("$resultRegister = sub " . BaseType::long() . " 0, $typeCastOp1ValueRegister");
        $this->writeAssignInteger($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->function->writeOpLineIR("$resultRegister = fsub " . BaseType::double() . " 0.0, $typeCastOp1ValueRegister");
        $this->writeAssignDouble($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

}

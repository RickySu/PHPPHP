<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class Add extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeValueValue($value1, $value2) {
        $this->setResult($value1+$value2);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = add " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval=$this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignInteger($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = fadd " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval=$this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignDouble($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

}

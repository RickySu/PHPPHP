<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class PostInc extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
        $this->gcTempZval();
    }

    protected function writeValueValue($value1) {
        if (!$this->opCode->result->markUnUsed) {
            $this->setResult($value1++);
        }
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister) {
        if (!$this->opCode->result->markUnUsed) {
            $resultZvalRegister = $this->getResultRegister();
            $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
            $this->writeAssignInteger($resultZval, $typeCastOp1ValueRegister);
            $this->setResult($resultZval);
        }
        $opZval = $this->function->getZvalIR($this->opCode->op1->getName());
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = add " . BaseType::long() . " $typeCastOp1ValueRegister, 1");
        $this->writeAssignInteger($opZval, $resultRegister);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister) {
        if (!$this->opCode->result->markUnUsed) {
            $resultZvalRegister = $this->getResultRegister();
            $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
            $this->writeAssignDouble($resultZval, $typeCastOp1ValueRegister);
            $this->setResult($resultZval);
        }
        $opZval = $this->function->getZvalIR($this->opCode->op1->getName());
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = fadd " . BaseType::double() . " $typeCastOp1ValueRegister, 1.0");
        $this->writeAssignDouble($opZval, $resultRegister);
    }

}

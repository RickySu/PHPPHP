<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class BooleanNot extends OpLine {

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
        $this->setResult(!$value1);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister) {
        $op1True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = icmp ne " . BaseType::long() . " $typeCastOp1ValueRegister, 0");
        $this->writeResult($op1True);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister) {
        $op1True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = fcmp one " . BaseType::double() . " $typeCastOp1ValueRegister, 0.0");
        $this->writeResult($op1True);
    }

    protected function writeResult($op1True) {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = xor i1 $op1True, true");
        $resultCastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultCastRegister = zext i1 $resultRegister to " . BaseType::long());
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignBoolean($resultZval, $resultCastRegister);
        $this->setResult($resultZval);
    }

}

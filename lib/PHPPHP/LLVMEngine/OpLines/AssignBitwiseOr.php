<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class AssignBitwiseOr extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeValueValue($value1, $value2) {
        $this->setResult($value1 | $value2);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultAddRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultAddRegister = or " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZval = $this->function->getZvalIR($this->opCode->op1->getName());
        $this->writeAssignInteger($resultZval, $resultAddRegister);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $typeCastOp1ValueIntegerRegister = $this->function->getRegisterSerial();
        $typeCastOp2ValueIntegerRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$typeCastOp1ValueIntegerRegister = fptosi " . BaseType::double() . " $typeCastOp1ValueRegister to " . BaseType::long());
        $this->function->writeOpLineIR("$typeCastOp2ValueIntegerRegister = fptosi " . BaseType::double() . " $typeCastOp2ValueRegister to " . BaseType::long());
        return $this->writeIntegerOp($typeCastOp1ValueIntegerRegister, $typeCastOp2ValueIntegerRegister);
    }

}

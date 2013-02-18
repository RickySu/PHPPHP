<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;

class BooleanAnd extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeValueValue($value1, $value2) {
        $this->setResult($value1 && $value2);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $op1True = $this->function->getRegisterSerial();
        $op2True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = icmp ne " . BaseType::long() . " $typeCastOp1ValueRegister, 0");
        $this->function->writeOpLineIR("$op2True = icmp ne " . BaseType::long() . " $typeCastOp2ValueRegister, 0");
        $this->writeResult($op1True, $op2True);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $op1True = $this->function->getRegisterSerial();
        $op2True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = fcmp one " . BaseType::double() . " $typeCastOp1ValueRegister, 0.0");
        $this->function->writeOpLineIR("$op2True = fcmp one " . BaseType::double() . " $typeCastOp2ValueRegister, 0.0");
        $this->writeResult($op1True, $op2True);
    }

    protected function writeResult($op1True, $op2True){
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = and i1 $op1True, $op2True");
        $resultCastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultCastRegister = zext i1 $resultRegister to ".BaseType::long());
        $resultZvalRegister = $this->getResultRegister();
        $resultZval=$this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignBoolean($resultZval, $resultCastRegister);
        $this->setResult($resultZval);
    }

}

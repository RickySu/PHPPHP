<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Add extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
    }

    protected function writeValueValue($value1, $value2) {
        $this->setResult($value1+$value2);
    }

    protected function writeZvalValue(LLVMZval $opZval, $value) {

    }

    protected function writeValueZval($value, LLVMZval $opZval) {
        return $this->writeZvalValue($opZval,$value);
    }

    protected function writeZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $this->TypeCastNumber($op1Zval, $op2Zval, array($this,'writeIntegerAdd'), array($this,'writeDoubleAdd'));
    }

    protected function writeIntegerAdd($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultAddRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultAddRegister = add " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval=$this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignInteger($resultZval, $resultAddRegister);
        $this->setResult($resultZval);
    }

    protected function writeDoubleAdd($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultAddRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultAddRegister = fadd " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval=$this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignDouble($resultZval, $resultAddRegister);
        $this->setResult($resultZval);
    }

}

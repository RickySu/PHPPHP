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

    protected function writeIntegerOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultAddRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultAddRegister = add " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval=$this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignInteger($resultZval, $resultAddRegister);
        $this->setResult($resultZval);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultAddRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultAddRegister = fadd " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval=$this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignDouble($resultZval, $resultAddRegister);
        $this->setResult($resultZval);
    }

}

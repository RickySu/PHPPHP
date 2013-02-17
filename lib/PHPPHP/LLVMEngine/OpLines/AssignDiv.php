<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignDiv extends OpLine {

    const OP1ZVALDIVTEMP = 'op1zval_div_temp';
    const OP2ZVALDIVTEMP = 'op2zval_div_temp';

    use Parts\TypeCast,
        Parts\PrepareOpZval,
        Parts\Convert;


    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeValueValue($value1, $value2) {
        $this->setResult($value1 / $value2);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultAddRegister = $this->function->getRegisterSerial();
        $isIntegerTypeRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultAddRegister = srem " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->function->writeOpLineIR("$isIntegerTypeRegister = icmp eq " . BaseType::long() . " $resultAddRegister, 0");
        $ifSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfZero = "Label_IfZero_$ifSerial";
        $LabelEndIf = "Label_EndIf_$ifSerial";
        $this->function->writeOpLineIR("br i1 $isIntegerTypeRegister, label %$LabelIfZero, label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelIfZero:");
        $resultAddRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultAddRegister = sdiv " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZval=$this->function->getZvalIR($this->opCode->op1->getName());
        $this->writeAssignInteger($resultZval, $resultAddRegister);
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
        $typeCastOp1ValueDoubleRegister = $this->function->getRegisterSerial();
        $typeCastOp2ValueDoubleRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$typeCastOp1ValueDoubleRegister = sitofp " . BaseType::long() . " $typeCastOp1ValueRegister to " . BaseType::double());
        $this->function->writeOpLineIR("$typeCastOp2ValueDoubleRegister = sitofp " . BaseType::long() . " $typeCastOp2ValueRegister to " . BaseType::double());
        $this->writeDoubleOp($typeCastOp1ValueDoubleRegister, $typeCastOp2ValueDoubleRegister);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultAddRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultAddRegister = fdiv " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $resultZval=$this->function->getZvalIR($this->opCode->op1->getName());
        $this->writeAssignDouble($resultZval, $resultAddRegister);
    }
    
}

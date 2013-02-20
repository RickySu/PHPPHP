<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class EmptyOp extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
        $this->gcTempZval();
    }

    protected function writeValue($value1) {
        $this->setResult(empty($value1));
    }

    protected function writeZval(LLVMZval $opZval) {
        $GuessTypeRegister=$this->function->InternalModuleCall(InternalModule::ZVAL_TYPE_GUESS,$opZval->getPtrRegister());
        $isString=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isString = icmp eq ".BaseType::int()." $GuessTypeRegister , ".LLVMZval\Type::TYPE_STRING);
        $ifSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfString = "Label_IfString_$ifSerial";
        $LabelElse = "Label_Else_$ifSerial";
        $LabelEndIf = "Label_EndIf_$ifSerial";
        $this->function->writeOpLineIR("br i1 $isString, label %$LabelIfString, label %$LabelElse");
        $this->function->writeOpLineIR("$LabelIfString:");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeAssignBoolean($resultZval, false);
        $this->setResult($resultZval);
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelElse:");
        $this->TypeCastNumberSingle($opZval, array($this,'writeIntegerOp'), array($this,'writeDoubleOp'));
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $op1True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = icmp eq " . BaseType::long() . " $typeCastOp1ValueRegister, 0");
        $this->function->writeOpLineIR("$resultRegister = zext i1 $op1True to " . BaseType::long());
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeAssignBoolean($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $op1True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = fcmp eq " . BaseType::double() . " $typeCastOp1ValueRegister, 0.0");
        $this->function->writeOpLineIR("$resultRegister = zext i1 $op1True to " . BaseType::long());
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeAssignBoolean($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

}

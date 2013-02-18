<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class IssetOp extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1);
        $this->gcTempZval();
    }

    protected function writeZval(LLVMZval $opZval) {
        $this->testNULL($opZval);
    }

    protected function testNULL(LLVMZval $opZval) {
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $this->setResult($resultZval);
        $isNULL = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isNULL = icmp eq " . LLVMZval::zval('*') . " {$opZval->getPtrRegister()}, null");
        $isNotNULLLabel = substr($this->function->getRegisterSerial(), 1) . "_is_NOT_NULL";
        $isNULLLabel = substr($this->function->getRegisterSerial(), 1) . "_isNULL";
        $isNULLEndifLabel = substr($this->function->getRegisterSerial(), 1) . "_Endif";
        $this->function->writeOpLineIR("br i1 $isNULL, label  %$isNULLLabel, label %$isNotNULLLabel");
        $this->function->writeOpLineIR("$isNULLLabel:");
        $this->writeAssignBoolean($resultZval, false);
        $this->function->writeOpLineIR("br label %$isNULLEndifLabel");
        $this->function->writeOpLineIR("$isNotNULLLabel:");
        $ifSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfZvalNULL = "Label_IfZvalNULL_$ifSerial";
        $LabelIfZvalNULLElse = "Label_IfZvalNULL_Else_$ifSerial";
        $guessTypePtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMZval::zval()->getStructIR()->getElementPtrIR($guessTypePtr, $opZval->getPtrRegister(), 'type'));
        $guessType = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$guessType = load " . BaseType::char('*') . " $guessTypePtr");
        $isNULL = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isNULL = icmp eq " . BaseType::char() . " $guessType, ".LLVMZval\Type::TYPE_NULL);
        $this->function->writeOpLineIR("br i1 $isNULL, label %$LabelIfZvalNULL, label %$LabelIfZvalNULLElse");
        $this->function->writeOpLineIR("$LabelIfZvalNULL:");
        $this->writeAssignBoolean($resultZval, false);
        $this->function->writeOpLineIR("br label %$isNULLEndifLabel");
        $this->function->writeOpLineIR("$LabelIfZvalNULLElse:");
        $this->writeAssignBoolean($resultZval, true);
        $this->function->writeOpLineIR("br label %$isNULLEndifLabel");
        $this->function->writeOpLineIR("$isNULLEndifLabel:");
    }

}

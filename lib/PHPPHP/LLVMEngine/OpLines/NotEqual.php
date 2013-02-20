<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class NotEqual extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        if (!$this->opCode->result->markUnUsed) {
            $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        }
        $this->gcTempZval();
    }

    protected function writeValueValue($value1, $value2) {
        $this->setResult($value1 != $value2);
    }

    protected function writeZvalValue(LLVMZval $opZval, $value) {
        if (is_string($value)) {
            $guessTypePtr = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR(LLVMZval::zval()->getStructIR()->getElementPtrIR($guessTypePtr, $opZval->getPtrRegister(), 'type'));
            $guessType = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR("$guessType = load " . BaseType::char('*') . " $guessTypePtr");
            $isString = $this->function->getRegisterSerial();
            $ifSerial = substr($this->function->getRegisterSerial(), 1);
            $LabelIfString = "Label_IfString_$ifSerial";
            $LabelElse = "Label_Else_$ifSerial";
            $LabelEndIf = "Label_EndIf_$ifSerial";
            $this->function->writeOpLineIR("$isString = icmp eq " . BaseType::char() . " $guessType, " . LLVMZval\Type::TYPE_STRING);
            $this->function->writeOpLineIR("br i1 $isString, label %$LabelIfString, label %$LabelElse");
            $this->function->writeOpLineIR("$LabelIfString:");
            // (zval)string == string
            $constant = $this->function->writeConstant($value);
            $resultEqual = $this->function->InternalModuleCall(InternalModule::ZVAL_EQUAL_STRING, $opZval->getPtrRegister(), strlen($value), $constant->ptr());
            $result = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR("$result = xor " . BaseType::long() . " $resultEqual, 1");
            $resultZvalRegister = $this->getResultRegister();
            $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
            $this->writeAssignBoolean($resultZval, $result);
            $this->setResult($resultZval);
            $this->function->writeOpLineIR("br label %$LabelEndIf");
            $this->function->writeOpLineIR("$LabelElse:");
            // (zval)number == string
            $this->TypeCastNumber($opZval, $value, array($this, 'writeIntegerOp'), array($this, 'writeDoubleOp'));
            $this->function->writeOpLineIR("br label %$LabelEndIf");
            $this->function->writeOpLineIR("$LabelEndIf:");
        } else {
            $this->TypeCastNumber($opZval, $value, array($this, 'writeIntegerOp'), array($this, 'writeDoubleOp'));
        }
    }

    protected function writeValueZval($value, LLVMZval $opZval) {
        $this->writeZvalValue($opZval, $value);
    }

    protected function writeZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $resultEqualRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_EQUAL, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister());
        $resultRegister = $this->getResultRegister();
        $this->function->writeOpLineIR("%$resultRegister = xor " . BaseType::long() . " $resultEqualRegister, 1");
        $resultZval = $this->function->getZvalIR($resultRegister, false, true);
        $this->writeAssignBoolean($resultZval, "%$resultRegister");
        $this->setResult($resultZval);
    }

    protected function writeIntegerOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = icmp ne " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeResult($resultRegister);
    }

    protected function writeDoubleOp($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = fcmp one " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeResult($resultRegister);
    }

    protected function writeResult($opTrue) {
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = zext i1 $opTrue to " . BaseType::long());
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeAssignBoolean($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

}

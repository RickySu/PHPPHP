<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class NotIdentical extends OpLine {


    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $this->gcTempZval();
    }

    protected function writeValueValue($value1, $value2) {
        $this->setResult($value1 !== $value2);
    }

    protected function writeZvalValue(LLVMZval $opZval, $value) {

        switch (gettype($value)) {
            case 'string':
                $valueType = LLVMZval\Type::TYPE_STRING;
                break;
            case 'integer':
                $valueType = LLVMZval\Type::TYPE_INTEGER;
                break;
            case 'double':
                $valueType = LLVMZval\Type::TYPE_DOUBLE;
                break;
            case 'boolean':
                $valueType = LLVMZval\Type::TYPE_BOOLEAN;
                break;
            case 'NULL':
            default:
                $valueType = LLVMZval\Type::TYPE_NULL;
                break;
        }

        $guessTypePtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMZval::zval()->getStructIR()->getElementPtrIR($guessTypePtr, $opZval->getPtrRegister(), 'type'));
        $guessType = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$guessType = load " . BaseType::char('*') . " $guessTypePtr");
        $ifSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfEqualType = "Label_IfEqualType_$ifSerial";
        $LabelIfStringType = "Label_IfStringType_$ifSerial";
        $LabelIfNumberType = "Label_IfNumberType_$ifSerial";
        $LabelElse = "Label_Else_$ifSerial";
        $LabelEndIf = "Label_EndIf_$ifSerial";
        $isEqual = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isEqual = icmp eq " . BaseType::char() . " $guessType, $valueType");
        $this->function->writeOpLineIR("br i1 $isEqual, label %$LabelIfEqualType, label %$LabelElse");
        $this->function->writeOpLineIR("$LabelIfEqualType:");
        // type equal
        $isString = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isString = icmp eq " . BaseType::char() . " $guessType, ".LLVMZval\Type::TYPE_STRING);
        $this->function->writeOpLineIR("br i1 $isString, label %$LabelIfStringType, label %$LabelIfNumberType");
        $this->function->writeOpLineIR("$LabelIfStringType:");
        //  type string
        $constant = $this->function->writeConstant($value);
        $resultEqual = $this->function->InternalModuleCall(InternalModule::ZVAL_EQUAL_STRING, $opZval->getPtrRegister(), strlen($value), $constant->ptr());
        $result=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$result = xor ".BaseType::long()." $resultEqual, 1");
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignBoolean($resultZval, $result);
        $this->setResult($resultZval);
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelIfNumberType:");
        //  type number
        $this->TypeCastNumber($opZval, $value, array($this, 'writeIntegerOp'), array($this, 'writeDoubleOp'));
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelElse:");
        // type not equal
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignBoolean($resultZval, true);
        $this->setResult($resultZval);
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");

        //    $this->TypeCastNumber($opZval, $value, array($this, 'writeIntegerOp'), array($this, 'writeDoubleOp'));
    }

    protected function writeValueZval($value, LLVMZval $opZval) {
        $this->writeZvalValue($opZval, $value);
    }

    protected function writeZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $resultEqualRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_EQUAL_EXACT, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister());
        $resultRegister=$this->getResultRegister();
        $this->function->writeOpLineIR("%$resultRegister = xor ".BaseType::long()." $resultEqualRegister, 1");
        $resultZval = $this->function->getZvalIR($resultRegister, true, true);
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
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $this->writeAssignBoolean($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

}

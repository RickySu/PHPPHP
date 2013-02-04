<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\TypeCast as LLVMTypeCast;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

trait TypeCast {

    protected function TypeCast(LLVMZval $op1Zval, LLVMZval $op2Zval, $integerOperation, $doubleOperation) {
        $typeCastOp1 = new LLVMTypeCast($this->function->getInternalVar(LLVMTypeCast::TYPE_CAST_OP1, LLVMTypeCast::typeCast()), $this->function);
        $typeCastOp2 = new LLVMTypeCast($this->function->getInternalVar(LLVMTypeCast::TYPE_CAST_OP2, LLVMTypeCast::typeCast()), $this->function);

        $castResultType = $this->function->InternalModuleCall(InternalModule::ZVAL_TYPE_CAST, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister(), $typeCastOp1, $typeCastOp2);

        $IfSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfInteger = "Label_IfInteger_$IfSerial";
        $LabelIfDouble = "Label_IfDouble_$IfSerial";
        $LabelEndIf = "Label_EndIf_$IfSerial";

        $isIntegerTypeRegister = $this->function->getRegisterSerial();

        $this->function->writeOpLineIR("$isIntegerTypeRegister = icmp eq " . BaseType::int() . " $castResultType, " . LLVMZval\Type::TYPE_INTEGER);
        $this->function->writeOpLineIR("br i1 $isIntegerTypeRegister, label %$LabelIfInteger, label %$LabelIfDouble");

        $this->function->writeOpLineIR("$LabelIfInteger:");  //If Integer
        $typeCastOp1ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp2ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp1ValueRegister = $this->function->getRegisterSerial();
        $typeCastOp2ValueRegister = $this->function->getRegisterSerial();

        $this->function->writeOpLineIR(LLVMTypeCast::typeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr, $typeCastOp1, 'lval'));
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = load " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('lval') . "* $typeCastOp1ValuePtr");
        $this->function->writeOpLineIR(LLVMTypeCast::typeCast()->getStructIR()->getElementPtrIR($typeCastOp2ValuePtr, $typeCastOp2, 'lval'));
        $this->function->writeOpLineIR("$typeCastOp2ValueRegister = load " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('lval') . "* $typeCastOp2ValuePtr");
        $integerOperation($typeCastOp1ValueRegister, $typeCastOp2ValueRegister);

        $this->function->writeOpLineIR("br label %$LabelEndIf");


        $this->function->writeOpLineIR("$LabelIfDouble:");  //If Double
        $typeCastOp1Value = $this->function->getRegisterSerial();
        $typeCastOp2Value = $this->function->getRegisterSerial();
        $typeCastOp1ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp2ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp1ValueRegister = $this->function->getRegisterSerial();
        $typeCastOp2ValueRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMTypeCast::typeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr, $typeCastOp1, 'dval'));
        $this->function->writeOpLineIR("$typeCastOp1Value = load " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval') . "* $typeCastOp1ValuePtr");
        $this->function->writeOpLineIR(LLVMTypeCast::typeCast()->getStructIR()->getElementPtrIR($typeCastOp2ValuePtr, $typeCastOp2, 'dval'));
        $this->function->writeOpLineIR("$typeCastOp2Value = load " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval') . "* $typeCastOp2ValuePtr");
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = bitcast " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval') . " $typeCastOp1Value to " . BaseType::double());
        $this->function->writeOpLineIR("$typeCastOp2ValueRegister = bitcast " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval') . " $typeCastOp2Value to " . BaseType::double());
        $doubleOperation($typeCastOp1ValueRegister, $typeCastOp2ValueRegister);

        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
    }

    protected function TypeCastSingle(LLVMZval $op1Zval, $integerOperation, $doubleOperation) {
        $typeCastOp1 = new LLVMTypeCast($this->function->getInternalVar(LLVMTypeCast::TYPE_CAST_OP1, LLVMTypeCast::typeCast()), $this->function);

        $castResultType = $this->function->InternalModuleCall(InternalModule::ZVAL_TYPE_CAST_SINGLE, LLVMZval\Type::TYPE_INTEGER, $op1Zval->getPtrRegister(), $typeCastOp1);

        $IfSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfInteger = "Label_IfInteger_$IfSerial";
        $LabelIfDouble = "Label_IfDouble_$IfSerial";
        $LabelEndIf = "Label_EndIf_$IfSerial";

        $isIntegerTypeRegister = $this->function->getRegisterSerial();

        $this->function->writeOpLineIR("$isIntegerTypeRegister = icmp eq " . BaseType::int() . " $castResultType, " . LLVMZval\Type::TYPE_INTEGER);
        $this->function->writeOpLineIR("br i1 $isIntegerTypeRegister, label %$LabelIfInteger, label %$LabelIfDouble");

        $this->function->writeOpLineIR("$LabelIfInteger:");  //If Integer
        $typeCastOp1ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp1ValueRegister = $this->function->getRegisterSerial();

        $this->function->writeOpLineIR(LLVMTypeCast::typeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr, $typeCastOp1, 'lval'));
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = load " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('lval') . "* $typeCastOp1ValuePtr");
        $integerOperation($typeCastOp1ValueRegister);

        $this->function->writeOpLineIR("br label %$LabelEndIf");


        $this->function->writeOpLineIR("$LabelIfDouble:");  //If Double
        $typeCastOp1Value = $this->function->getRegisterSerial();
        $typeCastOp1ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp1ValueRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMTypeCast::typeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr, $typeCastOp1, 'dval'));
        $this->function->writeOpLineIR("$typeCastOp1Value = load " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval') . "* $typeCastOp1ValuePtr");
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = bitcast " . LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval') . " $typeCastOp1Value to " . BaseType::double());
        $doubleOperation($typeCastOp1ValueRegister);

        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
    }

}
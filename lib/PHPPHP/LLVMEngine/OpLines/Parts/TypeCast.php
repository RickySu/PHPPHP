<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\TypeCast as LLVMTypeCast;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

trait TypeCast {

    protected function TypeCastNumberZvalValue(LLVMZval $op1Zval, $op2Value, $integerOperation, $doubleOperation) {
        $integerOperationProxy = function($typeCastOp1ValueRegister) use($op1Zval, $op2Value, $integerOperation, $doubleOperation) {
                    $op2Value*=1;
                    if (is_double($op2Value) && ($op2Value != floor($op2Value))) {
                        $typeCastOp1DoubleValuePtr = $this->function->getRegisterSerial();
                        $this->function->writeOpLineIR("$typeCastOp1DoubleValuePtr = sitofp " . BaseType::long() . " $typeCastOp1ValueRegister to " . BaseType::double());
                        return call_user_func($doubleOperation, $typeCastOp1DoubleValuePtr, "$op2Value");
                    } else {
                        return call_user_func($integerOperation, $typeCastOp1ValueRegister, "$op2Value");
                    }
                };
        $doubleOperationProxy = function($typeCastOp1ValueRegister) use($op1Zval, $op2Value, $integerOperation, $doubleOperation) {
                    $op2Value*=1;
                    if($op2Value == floor($op2Value)){
                        $op2Value="$op2Value.0";
                    }
                    return call_user_func($doubleOperation, $typeCastOp1ValueRegister, $op2Value);
                };
        return $this->TypeCastNumberSingle($op1Zval, $integerOperationProxy, $doubleOperationProxy);
    }

    protected function TypeCastNumberValueZval( $op1Value, LLVMZval $op2Zval, $integerOperation, $doubleOperation) {
        $integerOperationProxy = function($typeCastOp2ValueRegister) use($op1Value, $op2Zval, $integerOperation, $doubleOperation) {
                    $op1Value*=1;
                    if (is_double($op1Value) && ($op1Value != floor($op1Value))) {
                        $typeCastOp2DoubleValuePtr = $this->function->getRegisterSerial();
                        $this->function->writeOpLineIR("$typeCastOp2DoubleValuePtr = sitofp " . BaseType::long() . " $typeCastOp2ValueRegister to " . BaseType::double());
                        return call_user_func($doubleOperation, $typeCastOp2DoubleValuePtr, "$op1Value");
                    } else {
                        return call_user_func($integerOperation, $typeCastOp2ValueRegister, "$op1Value");
                    }
                };
        $doubleOperationProxy = function($typeCastOp2ValueRegister) use($op1Value, $op2Zval, $integerOperation, $doubleOperation) {
                    $op1Value*=1;
                    if($op1Value == floor($op1Value)){
                        $op1Value="$op1Value.0";
                    }
                    return call_user_func($doubleOperation, $typeCastOp2ValueRegister, $op1Value);
                };
        return $this->TypeCastNumberSingle($op2Zval, $integerOperationProxy, $doubleOperationProxy);
    }

    protected function TypeCastNumber($op1Zval, $op2Zval, $integerOperation, $doubleOperation) {
        if ($op1Zval instanceof LLVMZval && !($op2Zval instanceof LLVMZval)) {
            return $this->TypeCastNumberZvalValue($op1Zval, $op2Zval, $integerOperation, $doubleOperation);
        }

        if ($op2Zval instanceof LLVMZval && !($op1Zval instanceof LLVMZval)) {
            return $this->TypeCastNumberValueZval($op1Zval, $op2Zval, $integerOperation, $doubleOperation);
        }

        $typeCastOp1 = new LLVMTypeCast($this->function->getInternalVar(LLVMTypeCast::TYPE_CAST_OP1, LLVMTypeCast::TypeCast()), $this->function);
        $typeCastOp2 = new LLVMTypeCast($this->function->getInternalVar(LLVMTypeCast::TYPE_CAST_OP2, LLVMTypeCast::TypeCast()), $this->function);

        $castResultType = $this->function->InternalModuleCall(InternalModule::ZVAL_TYPE_CAST_NUMBER, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister(), $typeCastOp1, $typeCastOp2);

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

        $this->function->writeOpLineIR(LLVMTypeCast::TypeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr, $typeCastOp1, 'lval'));
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = load " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('lval') . "* $typeCastOp1ValuePtr");
        $this->function->writeOpLineIR(LLVMTypeCast::TypeCast()->getStructIR()->getElementPtrIR($typeCastOp2ValuePtr, $typeCastOp2, 'lval'));
        $this->function->writeOpLineIR("$typeCastOp2ValueRegister = load " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('lval') . "* $typeCastOp2ValuePtr");
        call_user_func($integerOperation, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);

        $this->function->writeOpLineIR("br label %$LabelEndIf");


        $this->function->writeOpLineIR("$LabelIfDouble:");  //If Double
        $typeCastOp1Value = $this->function->getRegisterSerial();
        $typeCastOp2Value = $this->function->getRegisterSerial();
        $typeCastOp1ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp2ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp1ValueRegister = $this->function->getRegisterSerial();
        $typeCastOp2ValueRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMTypeCast::TypeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr, $typeCastOp1, 'dval'));
        $this->function->writeOpLineIR("$typeCastOp1Value = load " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('dval') . "* $typeCastOp1ValuePtr");
        $this->function->writeOpLineIR(LLVMTypeCast::TypeCast()->getStructIR()->getElementPtrIR($typeCastOp2ValuePtr, $typeCastOp2, 'dval'));
        $this->function->writeOpLineIR("$typeCastOp2Value = load " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('dval') . "* $typeCastOp2ValuePtr");
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = bitcast " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('dval') . " $typeCastOp1Value to " . BaseType::double());
        $this->function->writeOpLineIR("$typeCastOp2ValueRegister = bitcast " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('dval') . " $typeCastOp2Value to " . BaseType::double());
        call_user_func($doubleOperation, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);

        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
    }

    protected function TypeCastNumberSingle(LLVMZval $op1Zval, $integerOperation, $doubleOperation) {
        $typeCastOp1 = new LLVMTypeCast($this->function->getInternalVar(LLVMTypeCast::TYPE_CAST_OP1, LLVMTypeCast::TypeCast()), $this->function);

        $castResultType = $this->function->InternalModuleCall(InternalModule::ZVAL_TYPE_CAST_NUMBER_SINGLE, LLVMZval\Type::TYPE_INTEGER, $op1Zval->getPtrRegister(), $typeCastOp1);

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

        $this->function->writeOpLineIR(LLVMTypeCast::TypeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr, $typeCastOp1, 'lval'));
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = load " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('lval') . "* $typeCastOp1ValuePtr");
        call_user_func($integerOperation,$typeCastOp1ValueRegister);

        $this->function->writeOpLineIR("br label %$LabelEndIf");


        $this->function->writeOpLineIR("$LabelIfDouble:");  //If Double
        $typeCastOp1Value = $this->function->getRegisterSerial();
        $typeCastOp1ValuePtr = $this->function->getRegisterSerial();
        $typeCastOp1ValueRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMTypeCast::TypeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr, $typeCastOp1, 'dval'));
        $this->function->writeOpLineIR("$typeCastOp1Value = load " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('dval') . "* $typeCastOp1ValuePtr");
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = bitcast " . LLVMTypeCast::TypeCast()->getStructIR()->getElementEffectiveType('dval') . " $typeCastOp1Value to " . BaseType::double());
        call_user_func($doubleOperation,$typeCastOp1ValueRegister);

        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
    }

}
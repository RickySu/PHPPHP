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
        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $resultZval = $op1Zval;
        $writeDoubleAssignDiv = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleAssignDiv($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $op1TempZval = $this->function->getZvalIR(self::OP1ZVALDIVTEMP, true, true);
            $op2TempZval = $this->function->getZvalIR(self::OP2ZVALDIVTEMP, true, true);
            $this->convertDouble($op1TempZval, $op1Zval);
            $this->convertDouble($op2TempZval, $op2Zval);
            $this->TypeCastNumber($op1TempZval, $op2TempZval, function() {

                    }, $writeDoubleAssignDiv);
            $this->gcVarZval($op1TempZval);
            $this->gcVarZval($op2TempZval);
            $GuessType = $this->function->InternalModuleCall(InternalModule::ZVAL_TYPE_GUESS_NUMBER, $resultZval->getPtrRegister());

            $IfSerial = substr($this->function->getRegisterSerial(), 1);
            $LabelIfInteger = "Label_IfInteger_$IfSerial";
            $LabelEndIf = "Label_EndIf_$IfSerial";
            $isIntegerTypeRegister = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR("$isIntegerTypeRegister = icmp eq " . BaseType::int() . " $GuessType, " . LLVMZval\Type::TYPE_INTEGER);
            $this->function->writeOpLineIR("br i1 $isIntegerTypeRegister, label %$LabelIfInteger, label %$LabelEndIf");

            $this->function->writeOpLineIR("$LabelIfInteger:");

            $this->function->InternalModuleCall(InternalModule::ZVAL_CONVERT_INTEGER,$resultZval->getPtrRegister());

            $this->function->writeOpLineIR("br label %$LabelEndIf");
            $this->function->writeOpLineIR("$LabelEndIf:");

        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval / $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeDoubleAssignDiv(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fdiv " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignDouble($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

}

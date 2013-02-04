<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class BooleanXor extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $writeIntegerBooleanXor = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerBooleanXor($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleBooleanXor = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleBooleanXor($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCastNumber($op1Zval, $op2Zval, $writeIntegerBooleanXor, $writeDoubleBooleanXor);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval xor $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerBooleanXor(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $op1True = $this->function->getRegisterSerial();
        $op2True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = icmp ne " . BaseType::long() . " $typeCastOp1ValueRegister, 0");
        $this->function->writeOpLineIR("$op2True = icmp ne " . BaseType::long() . " $typeCastOp2ValueRegister, 0");

        $resultZvalBitcastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalBitcastRegister = xor i1 $op1True, $op2True");
        $this->function->writeOpLineIR("$resultZvalRegister = zext i1 $resultZvalBitcastRegister to ".BaseType::long());
        $this->writeAssignBoolean($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleBooleanXor(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $op1True = $this->function->getRegisterSerial();
        $op2True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = fcmp one " . BaseType::double() . " $typeCastOp1ValueRegister, 0.0");
        $this->function->writeOpLineIR("$op2True = fcmp one " . BaseType::double() . " $typeCastOp2ValueRegister, 0.0");

        $resultZvalBitcastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalBitcastRegister = xor i1 $op1True, $op2True");
        $this->function->writeOpLineIR("$resultZvalRegister = zext i1 $resultZvalBitcastRegister to ".BaseType::long());
        $this->writeAssignBoolean($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

}

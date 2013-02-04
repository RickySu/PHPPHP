<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class BitwiseNot extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();

        $resultZval = $this->prepareResultZval();
        $writeIntegerBitwiseNot = function($typeCastOp1ValueRegister) use($resultZval) {
                    $this->writeIntegerBitwiseNot($resultZval, $typeCastOp1ValueRegister);
                };
        $writeDoubleBitwiseNot = function($typeCastOp1ValueRegister)use($resultZval) {
                    $this->writeDoubleBitwiseNot($resultZval, $typeCastOp1ValueRegister);
                };

        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);

        if ($op1Zval instanceof LLVMZval) {
            $this->TypeCastNumberSingle($op1Zval, $writeIntegerBitwiseNot, $writeDoubleBitwiseNot);
        } else {
            $this->writeImmediateValueAssign($resultZval, -$op1Zval);
        }
    }

    protected function writeIntegerBitwiseNot(LLVMZval $resultZval, $typeCastOp1ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = xor " . BaseType::long() . " $typeCastOp1ValueRegister, -1");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleBitwiseNot(LLVMZval $resultZval, $typeCastOp1ValueRegister) {
        $typeCastOp1ValueIntegerRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$typeCastOp1ValueIntegerRegister = fptosi ".BaseType::double(). " $typeCastOp1ValueRegister to ".BaseType::long());
        return $this->writeIntegerBitwiseNot($resultZval, $typeCastOp1ValueIntegerRegister);
    }

}

<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class BitwiseXor extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $writeIntegerBitwiseXor = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerBitwiseXor($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleBitwiseXor = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleBitwiseXor($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCast($op1Zval, $op2Zval, $writeIntegerBitwiseXor, $writeDoubleBitwiseXor);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval ^ $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerBitwiseXor(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = xor " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleBitwiseXor(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $typeCastOp1ValueIntegerRegister= $this->function->getRegisterSerial();
        $typeCastOp2ValueIntegerRegister= $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$typeCastOp1ValueIntegerRegister = fptosi ".BaseType::double(). " $typeCastOp1ValueRegister to ".BaseType::long());
        $this->function->writeOpLineIR("$typeCastOp2ValueIntegerRegister = fptosi ".BaseType::double(). " $typeCastOp2ValueRegister to ".BaseType::long());
        return $this->writeIntegerBitwiseXor($resultZval, $typeCastOp1ValueIntegerRegister, $typeCastOp2ValueIntegerRegister);
    }

}

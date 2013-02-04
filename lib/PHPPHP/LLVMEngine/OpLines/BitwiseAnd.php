<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class BitwiseAnd extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $writeIntegerBitwiseAnd = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerBitwiseAnd($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleBitwiseAnd = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleBitwiseAnd($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCast($op1Zval, $op2Zval, $writeIntegerBitwiseAnd, $writeDoubleBitwiseAnd);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval & $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerBitwiseAnd(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = and " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleBitwiseAnd(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $typeCastOp1ValueIntegerRegister= $this->function->getRegisterSerial();
        $typeCastOp2ValueIntegerRegister= $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$typeCastOp1ValueIntegerRegister = fptosi ".BaseType::double(). " $typeCastOp1ValueRegister to ".BaseType::long());
        $this->function->writeOpLineIR("$typeCastOp2ValueIntegerRegister = fptosi ".BaseType::double(). " $typeCastOp2ValueRegister to ".BaseType::long());
        return $this->writeIntegerBitwiseAnd($resultZval, $typeCastOp1ValueIntegerRegister, $typeCastOp2ValueIntegerRegister);
    }

}

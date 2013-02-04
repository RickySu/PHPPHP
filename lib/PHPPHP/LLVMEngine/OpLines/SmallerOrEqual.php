<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class SmallerOrEqual extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $writeIntegerSmallerOrEqual = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerSmallerOrEqual($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleSmallerOrEqual = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleSmallerOrEqual($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCastNumber($op1Zval, $op2Zval, $writeIntegerSmallerOrEqual, $writeDoubleSmallerOrEqual);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval <= $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerSmallerOrEqual(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $resultZvalBitcastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = icmp sle " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->function->writeOpLineIR("$resultZvalBitcastRegister = zext i1 $resultZvalRegister to ".BaseType::long());
        $this->writeAssignBoolean($resultZval, $resultZvalBitcastRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleSmallerOrEqual(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $resultZvalBitcastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fcmp ole " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->function->writeOpLineIR("$resultZvalBitcastRegister = zext i1 $resultZvalRegister to ".BaseType::long());
        $this->writeAssignBoolean($resultZval, $resultZvalBitcastRegister);
        return $resultZvalRegister;
    }

}

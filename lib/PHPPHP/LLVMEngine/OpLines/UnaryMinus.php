<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class UnaryMinus extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();

        $resultZval = $this->prepareResultZval();
        $writeIntegerSub = function($typeCastOp1ValueRegister) use($resultZval) {
                    $this->writeIntegerSub($resultZval, $typeCastOp1ValueRegister);
                };
        $writeDoubleSub = function($typeCastOp1ValueRegister)use($resultZval) {
                    $this->writeDoubleSub($resultZval, $typeCastOp1ValueRegister);
                };

        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);

        if ($op1Zval instanceof LLVMZval) {
            $this->TypeCastNumberSingle($op1Zval, $writeIntegerSub, $writeDoubleSub);
        } else {
            $this->writeImmediateValueAssign($resultZval, -$op1Zval);
        }
    }

    protected function writeIntegerSub(LLVMZval $resultZval, $typeCastOp1ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = sub " . BaseType::long() . " 0, $typeCastOp1ValueRegister");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleSub(LLVMZval $resultZval, $typeCastOp1ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fsub " . BaseType::double() . " 0.0, $typeCastOp1ValueRegister");
        $this->writeAssignDouble($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

}

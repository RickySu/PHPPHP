<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class PreInc extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);
        $resultZval = $this->prepareResultZval();

        $writeIntegerPreInc = function($typeCastOp1ValueRegister) use($resultZval, $op1Zval) {
                    $this->writeIntegerPreInc($resultZval, $op1Zval, $typeCastOp1ValueRegister);
                };
        $writeDoublePreInc = function($typeCastOp1ValueRegister)use($resultZval, $op1Zval) {
                    $this->writeDoublePreInc($resultZval, $op1Zval, $typeCastOp1ValueRegister);
                };

        if ($op1Zval instanceof LLVMZval) {
            $this->TypeCastNumberSingle($op1Zval, $writeIntegerPreInc, $writeDoublePreInc);
        } else {
            $this->writeImmediateValueAssign($resultZval, ++$op1Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerPreInc(LLVMZval $resultZval, LLVMZval $op1Zval, $typeCastOp1ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = add " . BaseType::long() . " $typeCastOp1ValueRegister, 1");
        $this->writeAssignInteger($op1Zval, $resultZvalRegister);
        $this->writeVarAssign($resultZval,$op1Zval);
        return $resultZvalRegister;
    }

    protected function writeDoublePreInc(LLVMZval $resultZval, LLVMZval $op1Zval, $typeCastOp1ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fadd " . BaseType::double() . " $typeCastOp1ValueRegister, 1.0");
        $this->writeAssignDouble($op1Zval, $resultZvalRegister);
        $this->writeVarAssign($resultZval,$op1Zval);
        return $resultZvalRegister;
    }

}

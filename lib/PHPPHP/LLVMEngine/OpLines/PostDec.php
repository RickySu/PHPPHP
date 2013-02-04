<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class PostDec extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);
        $resultZval = $this->prepareResultZval();
        $writeIntegerPostDec = function($typeCastOp1ValueRegister) use($resultZval, $op1Zval) {
                    $this->writeIntegerPostDec($resultZval, $op1Zval, $typeCastOp1ValueRegister);
                };
        $writeDoublePostDec = function($typeCastOp1ValueRegister)use($resultZval, $op1Zval) {
                    $this->writeDoublePostDec($resultZval, $op1Zval, $typeCastOp1ValueRegister);
                };

        if ($op1Zval instanceof LLVMZval) {
            $this->TypeCastNumberSingle($op1Zval, $writeIntegerPostDec, $writeDoublePostDec);
        } else {
            $this->writeImmediateValueAssign($resultZval, --$op1Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerPostDec(LLVMZval $resultZval, LLVMZval $op1Zval, $typeCastOp1ValueRegister) {
        $this->writeVarAssign($resultZval, $op1Zval);
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = sub " . BaseType::long() . " $typeCastOp1ValueRegister, 1");
        $this->writeAssignInteger($op1Zval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoublePostDec(LLVMZval $resultZval, LLVMZval $op1Zval, $typeCastOp1ValueRegister) {
        $this->writeVarAssign($resultZval, $op1Zval);
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fsub " . BaseType::double() . " $typeCastOp1ValueRegister, 1.0");
        $this->writeAssignDouble($op1Zval, $resultZvalRegister);
        return $resultZvalRegister;
    }

}

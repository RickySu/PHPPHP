<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class PostInc extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;



    public function write() {
        parent::write();
        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);
        $resultZval = $this->prepareResultZval();
        $writeIntegerPostInc = function($typeCastOp1ValueRegister) use($resultZval, $op1Zval) {
                    $this->writeIntegerPostInc($resultZval, $op1Zval, $typeCastOp1ValueRegister);
                };
        $writeDoublePostInc = function($typeCastOp1ValueRegister)use($resultZval, $op1Zval) {
                    $this->writeDoublePostInc($resultZval, $op1Zval, $typeCastOp1ValueRegister);
                };
        if ($op1Zval instanceof LLVMZval) {
            $this->TypeCastNumberSingle($op1Zval, $writeIntegerPostInc, $writeDoublePostInc);
        } else {
            $this->writeImmediateValueAssign($resultZval, ++$op1Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerPostInc(LLVMZval $resultZval, LLVMZval $op1Zval, $typeCastOp1ValueRegister) {
        $this->writeVarAssign($resultZval, $op1Zval);
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = add " . BaseType::long() . " $typeCastOp1ValueRegister, 1");
        $this->writeAssignInteger($op1Zval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoublePostInc(LLVMZval $resultZval, LLVMZval $op1Zval, $typeCastOp1ValueRegister) {
        $resultZvalPtr=$this->writeVarAssign($resultZval, $op1Zval);
        $resultZval->savePtrRegister($resultZvalPtr);
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fadd " . BaseType::double() . " $typeCastOp1ValueRegister, 1.0");
        $op1ZvalPtr=$this->writeAssignDouble($op1Zval, $resultZvalRegister);
        $op1Zval->savePtrRegister($op1ZvalPtr);
        return $resultZvalRegister;
    }

}

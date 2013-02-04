<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Add extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $writeIntegerAdd = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerAdd($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleAdd = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleAdd($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCastNumber($op1Zval, $op2Zval, $writeIntegerAdd, $writeDoubleAdd);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval + $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerAdd(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = add " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleAdd(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fadd " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignDouble($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

}

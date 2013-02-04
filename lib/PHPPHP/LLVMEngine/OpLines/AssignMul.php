<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignMul extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        $resultZval = $op1Zval;
        $writeIntegerAssignMul = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerAssignMul($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleAssignMul = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleAssignMul($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCastNumber($op1Zval, $op2Zval, $writeIntegerAssignMul, $writeDoubleAssignMul);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval * $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerAssignMul(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = mul " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleAssignMul(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fmul " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignDouble($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

}

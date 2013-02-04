<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class AssignBitwiseOr extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        $resultZval = $op1Zval;

        $writeIntegerAssignBitwiseOr = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerAssignBitwiseOr($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleAssignBitwiseOr = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleAssignBitwiseOr($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCastNumber($op1Zval, $op2Zval, $writeIntegerAssignBitwiseOr, $writeDoubleAssignBitwiseOr);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval | $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerAssignBitwiseOr(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = or " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleAssignBitwiseOr(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $typeCastOp1ValueIntegerRegister = $this->function->getRegisterSerial();
        $typeCastOp2ValueIntegerRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$typeCastOp1ValueIntegerRegister = fptosi " . BaseType::double() . " $typeCastOp1ValueRegister to " . BaseType::long());
        $this->function->writeOpLineIR("$typeCastOp2ValueIntegerRegister = fptosi " . BaseType::double() . " $typeCastOp2ValueRegister to " . BaseType::long());
        return $this->writeIntegerAssignBitwiseOr($resultZval, $typeCastOp1ValueIntegerRegister, $typeCastOp2ValueIntegerRegister);
    }

}

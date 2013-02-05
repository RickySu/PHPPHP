<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class AssignBitwiseXor extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    

    public function write() {
        parent::write();
        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        $resultZval = $op1Zval;

        $writeIntegerAssignBitwiseXor = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerAssignBitwiseXor($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleAssignBitwiseXor = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleAssignBitwiseXor($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCastNumber($op1Zval, $op2Zval, $writeIntegerAssignBitwiseXor, $writeDoubleAssignBitwiseXor);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval ^ $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeIntegerAssignBitwiseXor(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = xor " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleAssignBitwiseXor(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $typeCastOp1ValueIntegerRegister = $this->function->getRegisterSerial();
        $typeCastOp2ValueIntegerRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$typeCastOp1ValueIntegerRegister = fptosi " . BaseType::double() . " $typeCastOp1ValueRegister to " . BaseType::long());
        $this->function->writeOpLineIR("$typeCastOp2ValueIntegerRegister = fptosi " . BaseType::double() . " $typeCastOp2ValueRegister to " . BaseType::long());
        return $this->writeIntegerAssignBitwiseXor($resultZval, $typeCastOp1ValueIntegerRegister, $typeCastOp2ValueIntegerRegister);
    }

}

<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Smaller extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $writeIntegerSmaller = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerSmaller($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleSmaller = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleSmaller($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->TypeCastNumber($op1Zval, $op2Zval, $writeIntegerSmaller, $writeDoubleSmaller);
        } else {
            $this->writeImmediateValueAssign($resultZval, (int)($op1Zval < $op2Zval));
        }
        $this->gcTempZval();
    }

    protected function writeIntegerSmaller(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $resultZvalBitcastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = icmp slt " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->function->writeOpLineIR("$resultZvalBitcastRegister = zext i1 $resultZvalRegister to ".BaseType::long());
        $this->writeAssignBoolean($resultZval, $resultZvalBitcastRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleSmaller(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $resultZvalBitcastRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fcmp slt " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->function->writeOpLineIR("$resultZvalBitcastRegister = zext i1 $resultZvalRegister to ".BaseType::long());
        $this->writeAssignBoolean($resultZval, $resultZvalBitcastRegister);
        return $resultZvalRegister;
    }

}

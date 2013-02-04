<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class EmptyOp extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();

        $resultZval = $this->prepareResultZval();
        $writeIntegerEmptyOp = function($typeCastOp1ValueRegister) use($resultZval) {
                    $this->writeIntegerEmptyOp($resultZval, $typeCastOp1ValueRegister);
                };
        $writeDoubleEmptyOp = function($typeCastOp1ValueRegister)use($resultZval) {
                    $this->writeDoubleEmptyOp($resultZval, $typeCastOp1ValueRegister);
                };

        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);

        if ($op1Zval instanceof LLVMZval) {
            $this->TypeCastNumberSingle($op1Zval, $writeIntegerEmptyOp, $writeDoubleEmptyOp);
        } else {
            $this->writeImmediateValueAssign($resultZval, empty($op1Zval));
        }
    }

    protected function writeIntegerEmptyOp(LLVMZval $resultZval, $typeCastOp1ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $op1True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = icmp eq " . BaseType::long() . " $typeCastOp1ValueRegister, 0");
        $this->function->writeOpLineIR("$resultZvalRegister = zext i1 $op1True to ".BaseType::long());
        $this->writeAssignBoolean($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleEmptyOp(LLVMZval $resultZval, $typeCastOp1ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $op1True = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1True = fcmp oeq " . BaseType::double() . " $typeCastOp1ValueRegister, 0.0");
        $this->function->writeOpLineIR("$resultZvalRegister = zext i1 $op1True to ".BaseType::long());
        $this->writeAssignBoolean($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

}

<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class UnaryPlus extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();

        $resultZval = $this->prepareResultZval();

        list($op1Zval) = $this->prepareOpZval($this->opCode->op1);

        if ($op1Zval instanceof LLVMZval) {
            $this->writeVarAssign($resultZval, $op1Zval);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval);
        }
    }

}

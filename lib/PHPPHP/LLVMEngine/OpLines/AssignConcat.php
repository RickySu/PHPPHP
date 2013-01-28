<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignConcat extends Assign {

    public function write() {
        parent::write();
        print_r($this->opCode);
    }

    protected function writeVarAssign($op1Zval, $op2Zval) {
        $this->writeDebugInfo("debug");
    }

}
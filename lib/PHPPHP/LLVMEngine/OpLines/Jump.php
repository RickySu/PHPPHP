<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Jump extends OpLine {

    public function write() {
        parent::write();
        $this->function->writeJumpLabelIR($this->opCode->op1);
    }

}

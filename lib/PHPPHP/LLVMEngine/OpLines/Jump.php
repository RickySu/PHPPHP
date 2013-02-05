<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Jump extends OpLine {

    public function write() {
        parent::write();
        $this->function->setJumpLabel($this->opCode->op1);
        $this->function->writeOpLineIR($this->function->getJumpLabelIR($this->opCode->op1));
    }

}

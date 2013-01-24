<?php

namespace PHPPHP\LLVMEngine\OpLines;

class FetchConstant extends OpLine{

    public function write() {
        parent::write();
        $this->opCode->result->setValue(10);
    }
}
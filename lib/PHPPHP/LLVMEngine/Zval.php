<?php
namespace PHPPHP\LLVMEngine;

class Zval extends Writer\Base {

    public function writeDefine(){
        $this->writer->writeDefine(new Zval\Value());
        $this->writer->writeDefine(new Zval\Ptr());
    }

}
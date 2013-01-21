<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\Writer;

abstract class Base{

    /**
     *
     * @var Writer
     */
    protected $writer=null;

    public function setWriter(Writer $writer){
        $this->writer=$writer;
    }

    abstract protected function writeDeclare();

}
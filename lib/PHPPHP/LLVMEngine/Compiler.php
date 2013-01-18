<?php

namespace PHPPHP\LLVMEngine;
use PHPPHP\LLVMEngine\OpLines;
use PHPPHP\Engine\OpArray;

class Compiler{

    /**
     *
     * @var Writer
     */
    protected $writer;
    protected $context;

    public function __construct() {
    }

    public function compile(OpArray $opArray,$context){
        $this->writer=new Writer($context);
        $this->context=$context;
    }

}
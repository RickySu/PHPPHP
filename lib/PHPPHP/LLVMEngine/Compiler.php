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
        $this->writer=new Writer();
    }

    public function compile(OpArray $opArray,$context){
        $module=new Writer\ModuleWriter($context);
        $this->writer->addModuleWriter($module);
        $this->writer->write();
    }

}
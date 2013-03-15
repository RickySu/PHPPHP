<?php

namespace PHPPHP\LLVMEngine;

class Executor {

    /**
     *
     * @var Writer
     */
    protected $writer;
    protected $context;
    protected $llvmBind;

    public function __construct() {
        $this->llvmBind = new \LLVMBind();
        $this->init();
    }

    protected function shutdown() {
        $this->llvmBind->execute('jit_shutdown');
    }

    public function test($a){

    }

    protected function init(){
        $bitcode = Internal\Module::getBitcode();
        $this->llvmBind->loadBitcode($bitcode);
        //$this->llvmBind->registCallback(0,array($this,'test'));
    }

    public function execute($bitcode,$entryName){
        $this->llvmBind->execute('jit_init');
        $this->llvmBind->loadBitcode($bitcode);
        $this->llvmBind->execute($entryName);
        $this->shutdown();
    }

}

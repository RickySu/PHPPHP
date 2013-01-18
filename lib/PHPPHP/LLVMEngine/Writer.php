<?php

namespace PHPPHP\LLVMEngine;

class Writer {

    protected $context;
    protected $IRDefine = "";

    /**
     *
     * @param type $context
     */
    public function __construct($context = null) {
        if ($context) {
            $this->context = $context;
            $this->assignStructureDefine();
        }
    }

    public function writeDefineBlock($IR) {
        $this->IRDefine.="$IR\n";
    }

    public function writeDefine(Writer\Base $Base) {
        $Base->setWriter($this);
        $Base->writeDefine();
    }

    protected function assignStructureDefine() {
        $this->writeDefine(new Zval());
        echo $this->IRDefine;
    }

}
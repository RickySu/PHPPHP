<?php
namespace PHPPHP\LLVMEngine;

class Register{
    protected $register;

    public function __construct($register) {
        $this->register="%r$register";
    }

    public function __toString() {
        return $this->register;
    }
}
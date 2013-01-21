<?php
namespace PHPPHP\LLVMEngine\OpLines;
use PHPPHP\LLVMEngine\Writer\Module;

interface OpLineInterface{
    public function setModule(Module $module);
}
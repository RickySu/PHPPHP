<?php
namespace PHPPHP\LLVMEngine\Writer;

class ModuleEntryWriter extends FunctionWriter
{
    public function getEntryName()
    {
        return "PHPLLVM_{$this->moduleWriter->getModuleName()}_entry";
    }

}

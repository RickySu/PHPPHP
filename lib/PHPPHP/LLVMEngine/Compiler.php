<?php

namespace PHPPHP\LLVMEngine;

use PHPPHP\LLVMEngine\OpLines;
use PHPPHP\Engine\OpArray;

dl('llvm_bind.so');

class Compiler {

    /**
     *
     * @var Writer
     */
    protected $writer;
    protected $context;
    protected $llvmBind;

    public function __construct() {
        $this->writer = new Writer();
        $this->llvmBind = new \LLVMBind();
    }

    public function compile($compiledData, $context) {
        $opArray = $compiledData['opcode'];
        $module = new Writer\ModuleWriter($context);
        $this->writer->addModuleWriter($module);
        $this->compileOpLine($module, $opArray);
        $IR = $this->writer->write();
        echo $IR;
        $this->writer->clear();
        $bitcode = Internal\Module::getBitcode();
        $this->llvmBind->loadBitcode($bitcode);
        $bitcode = $this->llvmBind->compileAssembly($IR);
        echo $this->llvmBind->getLastError();
        $this->llvmBind->loadBitcode($bitcode);
        $this->llvmBind->execute($module->getEntryName());
        //echo $module->getEntryName();
        die;
        echo $bitcode;
    }

    protected function compileOpLine(Writer\ModuleWriter $module, OpArray $opArray) {
        $function = $module->getEntryFunction();
        foreach ($opArray as $opCode) {
            $className = explode('\\', get_class($opCode));
            $className = $className[count($className) - 1];
            $opLineClassName = '\\PHPPHP\\LLVMEngine\\OpLines\\' . $className;
            $opLine = new $opLineClassName($opCode);
            $function->addOpLine($opLine);
        }
    }

}
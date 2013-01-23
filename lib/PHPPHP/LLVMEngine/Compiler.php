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
        $this->compileOpLine($module, $opArray);
        $this->writer->write();
    }

    protected function compileOpLine(Writer\ModuleWriter $module,OpArray $opArray){
        foreach ($opArray as $opCode){
            $className=explode('\\',get_class($opCode));
            $className=$className[count($className)-1];
            $opLineClassName='\\PHPPHP\\LLVMEngine\\OpLines\\'.$className;
            $opLine=new $opLineClassName($opCode);
            $module->addOpLine($opLine);
            //break;
        }
    }

}
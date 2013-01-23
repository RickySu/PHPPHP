<?php
namespace PHPPHP\LLVMEngine\OpLines;
use PHPPHP\LLVMEngine\Writer\ModuleWriter;
use PHPPHP\Engine\OpLine as opCode;
abstract class OpLine{
    /**
     *
     * @var ModuleWriter
     */
    protected $module;

    /**
     *
     * @var opCode
     */
    protected $opCode;

    public function __construct(opCode $opCode) {
        $this->opCode=$opCode;
    }

    public function setModule(ModuleWriter $module){
        $this->module=$module;
    }

    public function write(){
        $className=explode('\\',get_class($this));
        $className=$className[count($className)-1];
        $this->writeDebugInfo();
        $this->writeDebugInfo("line {$this->opCode->lineno} $className");
    }

    protected function writeDebugInfo($info=null){
        if($info===null){
            $this->module->writeOpLineIR('');
            return;
        }
        $this->module->writeOpLineIR("; $info");
    }

}
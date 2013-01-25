<?php
namespace PHPPHP\LLVMEngine\OpLines;
use PHPPHP\LLVMEngine\Writer\FunctionWriter;
use PHPPHP\Engine\OpLine as opCode;
abstract class OpLine{
    /**
     *
     * @var FunctionWriter
     */
    protected $function;

    /**
     *
     * @var opCode
     */
    protected $opCode;

    public function __construct(opCode $opCode) {
        $this->opCode=$opCode;
    }

    public function setFunction(FunctionWriter $function){
        $this->function=$function;
    }

    public function write(){
        $className=explode('\\',get_class($this));
        $className=$className[count($className)-1];
        $this->writeDebugInfo();
        $this->writeDebugInfo("line {$this->opCode->lineno} $className");
    }

    protected function writeDebugInfo($info=null){
        if($info===null){
            $this->function->writeOpLineIR('');
            return;
        }
        $this->function->writeOpLineIR("; $info");
    }

}
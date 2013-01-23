<?php

namespace PHPPHP\LLVMEngine;

class Writer {

    protected $baseIRDeclare=array();
    protected $moduleIRDeclare=array();
    protected $moduleConstantDeclare=array();
    protected $moduleIR=array();
    protected $moduleExternalDeclare = array();
    protected $modules = array();

    public function __construct() {
        $this->assignInternalModuleDefine();
    }

    protected function assignInternalModuleDefine() {
        $interalModules = Internal\Module::Define();
        foreach($interalModules as $entryName => $module){
            list($return,$params)=$module;
            $paramIR=implode(', ',$params);
            $IR="declare fastcc $return @$entryName($paramIR)";
            $this->writeModuleIRDeclare($entryName, $IR);
        }
    }

    public function writeDeclareBlock($IR) {
        $this->baseIRDeclare[] = $IR;
    }

    public function writeModuleIRDeclare($entryName, $IR) {
        $this->moduleIRDeclare[$entryName] = $IR;
    }

    public function writeModuleConstantDeclare($entryName, $IR) {
        $this->moduleConstantDeclare[$entryName][] = $IR;
    }

    public function writeUsedModule($entryName){
        $this->moduleExternalDeclare[$entryName]=true;
    }

    public function writeModuleIR($entryName, $IR) {
        $this->moduleIR[$entryName] = $IR;
    }

    public function writeDeclare(Writer\Base $base) {
        $base->setWriter($this);
        $base->writeDeclare();
    }

    public function assignStructureDeclare() {
        $this->writeDeclare(new Zval());
    }

    /**
     *
     * @param \PHPPHP\LLVMEngine\Writer\Module $module
     * @return \PHPPHP\LLVMEngine\Writer
     */
    public function addModuleWriter(Writer\ModuleWriter $module) {
        if (!in_array($module, $this->modules)) {
            $this->modules[] = $module;
            $module->setWriter($this);
        }
        return $this;
    }

    protected function writeModules() {
        $index = 0;
        while (isset($this->modules[$index])) {
            $module = $this->modules[$index++];
            $module->write();
        }
    }

    public function write() {
        $this->assignStructureDeclare();
        $this->writeModules();
        $outputIR='';
        $outputIR.=implode("\n",$this->baseIRDeclare)."\n";
        foreach ($this->moduleConstantDeclare as $constantDeclare){
            $outputIR.=implode("\n", $constantDeclare);
        }
        $outputIR.="\n";
        $outputIR.=implode("\n",$this->moduleIR);
        $outputIR.="\n";
        foreach($this->moduleExternalDeclare as $external => $used){
            $outputIR.=$this->moduleIRDeclare[$external]."\n";
        }

        echo $outputIR;
        //print_r($this->moduleIRDeclare);
        /*
        print_r($this->baseIRDeclare);
        print_r($this->moduleIRDeclare);
        print_r($this->moduleIR);
        print_r($this->moduleConstantDeclare);
         *
         */
    }

    public function clear() {
        unset($this->baseIRDeclare);
        unset($this->moduleConstantDeclare);
        unset($this->moduleIR);
        unset($this->modules);
    }

}
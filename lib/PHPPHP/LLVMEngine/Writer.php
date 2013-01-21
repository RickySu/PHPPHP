<?php

namespace PHPPHP\LLVMEngine;

class Writer {

    protected $baseIRDeclare;
    protected $moduleIRDeclare;
    protected $moduleIR;
    protected $modules=array();

    public function __construct() {

    }

    public function writeDeclareBlock($IR) {
        $this->baseIRDeclare[] = $IR;
    }

    public function writeModuleIRDeclare($entryName, $IR) {
        $this->moduleIRDeclare[$entryName] = $IR;
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
        foreach ($this->modules as $module) {
            $module->write();
        }
    }

    public function write() {
        $this->assignStructureDeclare();
        $this->writeModules();
        print_r($this->baseIRDeclare);
        print_r($this->moduleIRDeclare);
        print_r($this->moduleIR);
    }

}
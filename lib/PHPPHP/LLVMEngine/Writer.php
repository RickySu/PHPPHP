<?php

namespace PHPPHP\LLVMEngine;

class Writer {

    protected $baseIRDeclare = array();
    protected $moduleConstantDeclare = array();
    protected $moduleExternalDeclare = array();
    protected $modules = array();
    protected $functionIRDeclare = array();
    protected $functionIR = array();

    public function __construct() {
        $this->assignInternalModuleDefine();
    }

    protected function assignInternalModuleDefine() {
        $interalModules = Internal\Module::Define();
        foreach ($interalModules as $functionName => $functionDeclare) {
            list($return, $params) = $functionDeclare;
            $paramIR = implode(', ', $params);
            $IR = "declare fastcc $return @$functionName($paramIR)";
            $this->writeFunctionIRDeclare('internal', $functionName, $IR);
        }
    }

    public function writeDeclareBlock($IR) {
        $this->baseIRDeclare[] = $IR;
    }

    public function writeFunctionIRDeclare($moduleName, $functionName, $IR) {
        $this->functionIRDeclare[$moduleName][$functionName] = $IR;
    }

    public function writeModuleConstantDeclare($moduleName, $IR) {
        $this->moduleConstantDeclare[$moduleName][] = $IR;
    }

    public function writeUsedFunction($functionName) {
        $this->moduleExternalDeclare[$functionName] = true;
    }

    public function writeFunctionIR($moduleName, $functionName, $IR) {
        $this->functionIRs[$moduleName][$functionName] = $IR;
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

    protected function getFunctionIRDeclare($externalFunction) {
        foreach ($this->functionIRDeclare as $moduleName => $functions) {
            if (isset($functions[$externalFunction])) {
                return $functions[$externalFunction];
            }
        }
        return '';
    }

    public function write() {
        $this->assignStructureDeclare();
        $this->writeModules();
        $outputIR = '';
        $outputIR.=implode("\n", $this->baseIRDeclare) . "\n";
        foreach ($this->moduleConstantDeclare as $constantDeclare) {
            $outputIR.=implode("\n", $constantDeclare) . "\n";
        }
        foreach ($this->functionIRs as $moduleName => $functionIR) {
            $outputIR.=implode("\n", $functionIR) . "\n";
        }
        foreach ($this->moduleExternalDeclare as $externalFunction => $used) {
            $outputIR.="{$this->getFunctionIRDeclare($externalFunction)}\n";
        }

        return $outputIR;
    }

    public function clear() {
        $this->baseIRDeclare =
                $this->moduleConstantDeclare =
                $this->moduleExternalDeclar =
                $this->modules =
                $this->functionIRDeclare =
                $this->functionIR =
                array();
    }

}
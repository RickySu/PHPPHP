<?php

namespace PHPPHP\LLVMEngine;

class Writer {

    protected $baseIRDeclare = array();
    protected $moduleConstantDeclare = array();
    protected $moduleExternalDeclare = array();
    protected $modules = array();
    protected $functionIRDeclare = array();
    protected $functionIRs = array();
    protected $jumpTable = array();
    protected $jumpTableInitializerEntryName;

    public function __construct() {
        $this->assignInternalModuleDefine();
        $this->jumpTableInitializerEntryName="@PHPLLVM_jumptable_init_entry_".md5(microtime().rand());
    }

    public function getJumpTableInitializerEntryName(){
        if(!$this->jumpTable){
            return false;
        }
        return $this->jumpTableInitializerEntryName;
    }

    protected function assignInternalModuleDefine() {
        $interalModules = Internal\Module::Define();
        foreach ($interalModules as $functionName => $functionDeclare) {
            list($fastcc, $return, $params) = $functionDeclare;
            $paramIR = implode(', ', $params);
            $IR = "declare $fastcc $return @$functionName($paramIR)";
            $this->writeFunctionIRDeclare('internal', $functionName, $IR);
        }
    }

    public function writeDeclareBlock($IR) {
        $this->baseIRDeclare[] = $IR;
    }

    public function writeFunctionIRDeclare($moduleName, $entryName, $IR) {
        $this->functionIRDeclare[$moduleName][$entryName] = $IR;
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

    public function assignStructureDeclare() {
        $this->baseIRDeclare = array_merge($this->baseIRDeclare, Zval::getDeclare());
        $this->baseIRDeclare = array_merge($this->baseIRDeclare, TypeCast::getDeclare());
        $this->baseIRDeclare = array_merge($this->baseIRDeclare, JumpTable::getDeclare());
    }

    /**
     *
     * @param  \PHPPHP\LLVMEngine\Writer\Module $module
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

    protected function writeJumpTableInitializerEntryIRs(){
        if(!$this->jumpTable){
            return '';
        }
        $IRHead="define void {$this->getJumpTableInitializerEntryName()}() nounwind uwtable {\n";
        $IREnd="\n}";
        $IR[]="; init jumptable";
        foreach ($this->jumpTable as $functionName => $jumpTable) {
            $lenRegisterPtr="%lenRegisterPtr";
            $IR[]=$jumpTable::jumpTable('*')->getStructIR()->getElementPtrIR($lenRegisterPtr,$jumpTable->getIRRegister(),'len');
            $IR[]="store ".Type\Base::int()." ".strlen($functionName).", ".Type\Base::int('*')." $lenRegisterPtr";
            $fnameRegisterPtr="%fnameRegisterPtr";
            $IR[]=$jumpTable::jumpTable('*')->getStructIR()->getElementPtrIR($fnameRegisterPtr,$jumpTable->getIRRegister(),'fname');
            $IR[]="store ".Type\Base::char('*')." {$jumpTable->functionNameConstant->ptr()}, ".Type\Base::char('**')." $fnameRegisterPtr";
/*
            $realfunctionRegisterPtr="%realfunctionRegisterPtr";
            $IR[]=$jumpTable::jumpTable('*')->getStructIR()->getElementPtrIR($realfunctionRegisterPtr,$jumpTable->getIRRegister(),'realfunction');
            list($fastcc, $return, $argTypes) =Internal\Module::Define()[Internal\Module::PHPLLVM_FUNCTION_CALL_BY_NAME];
            $IR[]="store ".Type\Base::void('*')." bitcast( $return (".implode(", ",$argTypes).")* @".Internal\Module::PHPLLVM_FUNCTION_CALL_BY_NAME." to ".Type\Base::void('*')." ), ".Type\Base::void('**')." $realfunctionRegisterPtr";
 *
 */
        }
        $IR[]='ret void';
        return $IRHead.implode("\n\t",$IR).$IREnd;
    }

    public function write() {
        $this->assignStructureDeclare();
        $this->writeModules();
        $outputIR = '';
        $outputIR.=implode("\n", $this->baseIRDeclare) . "\n";
        foreach ($this->jumpTable as $functionName => $jumpTable) {
            $outputIR.="$jumpTable = global {$jumpTable::jumpTable()} zeroinitializer, align {$jumpTable::jumpTable('*')->size()}\n";
        }
        foreach ($this->moduleConstantDeclare as $constantDeclare) {
            $outputIR.=implode("\n", $constantDeclare) . "\n";
        }
        foreach ($this->functionIRs as $moduleName => $functionIR) {
            $outputIR.=implode("\n", $functionIR) . "\n";
        }
        $outputIR.=$this->writeJumpTableInitializerEntryIRs()."\n";
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

    public function getJumpTable($functionName) {
        $functionName = strtolower($functionName);
        if (isset($this->jumpTable[$functionName])) {
            return $this->jumpTable[$functionName];
        }
        $jumpTable = new JumpTable("@PHPLLVM_jumptable_$functionName");
        return ($this->jumpTable[$functionName] = $jumpTable);
    }

}

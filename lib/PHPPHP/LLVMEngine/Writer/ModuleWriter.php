<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\OpLines\OpLineInterface;
use PHPPHP\LLVMEngine\Writer;
use PHPPHP\LLVMEngine\Zval;

class ModuleWriter extends Base {

    protected $moduleName;
    protected $entryName;
    protected $opLines = array();
    protected $opLinesIR = array();
    protected $registerSerial = 0;

    public function __construct($moduleName) {
        $this->moduleName = $moduleName;
        $this->entryName = $this->generatEentryName($moduleName);
    }

    protected function generatEentryName($moduleName) {
        return "PHPLLVM_module_entry_" . md5($moduleName);
    }

    protected function writeDeclare() {
        $IR = "declare @{$this->entryName}()";
        $this->writer->writeModuleIRDeclare($this->entryName, $IR);
    }

    public function getDeclareIR() {
        return "declare @{$this->entryName}()";
    }

    /**
     *
     * @param \PHPPHP\LLVMEngine\OpLines\OpLineInterface $opLine
     * @return \PHPPHP\LLVMEngine\Writer\Module
     */
    public function addOpLine(OpLineInterface $opLine) {
        if (!in_array($opLine, $this->opLines)) {
            $this->opLines[] = $opLine;
            $opLine->setModule($this);
        }
        return $this;
    }

    public function setWriter(Writer $writer) {
        parent::setWriter($writer);
        $writer->addModuleWriter($this);
    }

    protected function writeOpLines() {
        $IR.=";module {$this->moduleName}\n";
        $IR.="define ".Zval::PtrIRDeclare()." @{$this->entryName}() nounwind uwtable {\n";
        foreach ($this->opLines as $lineNumber => $opLine) {
            $IR.="\t;line $lineNumber\n";
            foreach($this->opLinesIR[$lineNumber] as $IRLine){
                $IR.="\t$IRLine\n";
            }
        }
        $IR.="}\n";
        $this->writer->writeModuleIR($this->entryName, $IR);
    }

    public function write() {
        $this->writeDeclare();
        $this->writeOpLines();
    }

}
<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\OpLines\OpLine;
use PHPPHP\LLVMEngine\Writer;
use PHPPHP\LLVMEngine\Zval;
use PHPPHP\LLVMEngine\Type\Base as StringType;

class ModuleWriter extends Base {

    protected $moduleName;
    protected $entryName;
    protected $opLines = array();
    protected $opLinesIR = array();
    protected $registerSerial = 0;
    protected $constantSerial = 0;

    public function __construct($moduleName) {
        $this->moduleName = $moduleName;
        $this->entryName = $this->getEentryName();
    }

    protected function getEentryHash(){
        return md5($this->moduleName);
    }

    protected function getEentryName() {
        return "PHPLLVM_module_entry_{$this->getEentryHash($this->moduleName)}";
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
     * @param \PHPPHP\LLVMEngine\OpLines\OpLine $opLine
     * @return \PHPPHP\LLVMEngine\Writer\Module
     */
    public function addOpLine(OpLine $opLine) {
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
        foreach ($this->opLines as $opLine) {
            $opLine->write();
        }
    }

    public function writeOpLineIR($opLineIR){
        $this->opLinesIR[]=$opLineIR;
    }

    protected function writeIR(){
        $IR=";module {$this->moduleName}\n";
        $IR.="define ".Zval::PtrIRDeclare()." @{$this->entryName}() nounwind uwtable {\n";
        foreach($this->opLinesIR as $opLineIR){
            $IR.="\t$opLineIR\n";
        }
        $IR.="}\n";
        $this->writer->writeModuleIR($this->entryName, $IR);
    }

    /**
     *
     * @param string $constant
     * @return StringType
     */
    public function writeConstant($constant){
        $constantName="@str.{$this->getEentryHash()}.{$this->constantSerial}";
        $constantLen=strlen($constant);
        $this->constantSerial++;
        $IR="$constantName = private unnamed_addr constant [$constantLen x i8] c\"{$this->escapeString($constant)}\" , align 1";
        $this->writer->writeModuleConstantDeclare($this->entryName, $IR);
        return StringType::char('*',$constantLen,$constantName);
        //return new StringType($constantName,$constantLen);
    }

    public function write() {
        $this->writeDeclare();
        $this->writeOpLines();
        $this->writeIR();
    }

}
<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\Writer;
use PHPPHP\LLVMEngine\Type\Base as StringType;

class ModuleWriter extends Base {

    protected $moduleContext;
    protected $constantSerial = 0;
    protected $functions;
    protected $functionIR = array();
    protected $constant = array();

    public function getJumpTableInitializerEntryName() {
        return $this->writer->getJumpTableInitializerEntryName();
    }

    public function __construct($moduleContext) {
        $this->moduleContext = $moduleContext;
        $functionWriter = new ModuleEntryWriter($this->getModuleName());
        $functionWriter->setModuleWriter($this);
        $this->functions['*entryFunction*'] = $functionWriter;
    }

    public function getModuleName() {
        return 'module_' . md5($this->moduleContext);
    }

    public function getEntryName() {
        return $this->functions['*entryFunction*']->getEntryName();
    }

    /**
     *
     * @return ModuleEntryWriter
     */
    public function getEntryFunction() {
        return $this->functions['*entryFunction*'];
    }

    public function getJumpTable($functionName) {
        $functionNameConstant = $this->writeConstant($functionName);
        $jumpTable = $this->writer->getJumpTable($functionName);
        $jumpTable->functionNameConstant = $functionNameConstant;
        return $jumpTable;
    }

    public function getFunctions() {
        $functions = $this->functions;
        unset($functions['*entryFunction*']);
        return $functions;
    }

    /**
     *
     * @param  type           $functionName
     * @return FunctionWriter
     */
    public function addFunction($functionName, $params) {
        $functionWriter = new FunctionWriter($functionName, $params);
        $this->functions[$functionWriter->getEntryName()] = $functionWriter;
        $functionWriter->setModuleWriter($this);
        return $functionWriter;
    }

    /**
     *
     * @param  string     $constant
     * @return StringType
     */
    public function writeConstant($constant) {
        if (isset($this->constant[$constant])) {
            return $this->constant[$constant];
        }
        $constantSerial = $this->getConstantSerial();
        $constantName = "@str.{$this->getModuleName()}.$constantSerial";
        $constantLen = strlen($constant);
        $IR = "$constantName = private unnamed_addr constant [$constantLen x i8] c\"{$this->escapeString($constant)}\" , align 1";
        $this->writer->writeModuleConstantDeclare($this->getModuleName(), $IR);
        return ($this->constant[$constant] = StringType::char('*', $constantLen, $constantName));
    }

    protected function getConstantSerial() {
        return++$this->constantSerial;
    }

    public function setWriter(Writer $writer) {
        parent::setWriter($writer);
        $writer->addModuleWriter($this);
    }

    public function writeUsedFunction($functionName, $define = false) {
        $this->writer->writeUsedFunction($functionName, $define);
    }

    public function write() {
        foreach ($this->functions as $functionWriter) {
            $functionWriter->write();
        }
    }

    public function writeFunctionIRDeclare($entryName, $IR) {
        $this->writer->writeFunctionIRDeclare($this->getModuleName(), $entryName, $IR);
    }

    public function writeFunctionIR($entryName, $IR) {
        $this->writer->writeFunctionIR($this->getModuleName(), $entryName, $IR);
    }

}

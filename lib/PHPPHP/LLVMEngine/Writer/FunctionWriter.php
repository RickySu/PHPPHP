<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\Zval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;
use PHPPHP\LLVMEngine\OpLines\OpLine;
use PHPPHP\LLVMEngine\Type\Base as StringType;

class FunctionWriter {

    protected $opLines = array();
    protected $opLinesIR = array();
    protected $registerSerial = 0;
    protected $functionName;
    protected $varList = array();

    /**
     *
     * @var ModuleWriter
     */
    protected $moduleWriter;

    public function __construct($functionName) {
        $this->functionName = strtolower($functionName);
    }

    public function setModuleWriter(ModuleWriter $moduleWriter) {
        $this->moduleWriter = $moduleWriter;
    }

    public function getEntryName() {
        return "PHPLLVM_function_{$this->functionName}";
    }

    /**
     *
     * @param \PHPPHP\LLVMEngine\OpLines\OpLine $opLine
     */
    public function addOpLine(OpLine $opLine) {
        if (!in_array($opLine, $this->opLines)) {
            $this->opLines[] = $opLine;
            $opLine->setFunction($this);
        }
    }

    protected function writeOpLines() {
        foreach ($this->opLines as $opLine) {
            $opLine->write();
        }
    }

    public function writeOpLineIR($opLineIR) {
        $this->opLinesIR[] = $opLineIR;
    }

    protected function writeIR() {
        //write declare
        $EntryDeclareIR = "declare " . Zval::PtrIRDeclare() . " @{$this->getEntryName()}()";
        $this->moduleWriter->writeFunctionIRDeclare($this->getEntryName(), $EntryDeclareIR);

        $opLineIRs=array();
        $this->writeOpLines();
        foreach ($this->opLinesIR as $opLineIR) {
            $opLineIRs[] = "\t$opLineIR";
        }

        //write function content
        $IR[] = ";function {$this->functionName}";
        $IR[] = "define " . Zval::PtrIRDeclare() . " @{$this->getEntryName()}() nounwind uwtable {";
        $IR[] = implode("\n\t", $this->functionCtorIR());
        $varIRDeclare="\n\t".implode("\n\t",$this->writeVarDeclare());
        $IR[]=$varIRDeclare;
        $IR=array_merge($IR,$opLineIRs);
        $IR[] = implode("\n\t", $this->functionDtorIR());
        $IR[] = "}";
        $this->moduleWriter->writeFunctionIR($this->getEntryName(), implode("\n", $IR));
    }

    public function getRegisterSerial() {
        $tmp = ++$this->registerSerial;
        return "%r$tmp";
    }

    protected function functionCtorIR() {
        $IR[] = '';
        $IR[] = ";function entry";

        //prepare return value
        $IR[] = "%retval = alloca " . Zval::PtrIRDeclare() . ", align " . Zval::PtrIRAlign();
        $IR[] = "store " . Zval::PtrIRDeclare() . " null , " . Zval::PtrIRDeclare() . "* %retval, align " . Zval::PtrIRAlign();

        //prepare var list
        $IR[] = "%zvallist = " . InternalModule::call(InternalModule::ZVAL_LIST_INIT);
        $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_LIST_INIT);
        return $IR;
    }

    protected function functionDtorIR() {
        $IR[] = "";
        $IR[] = ";function end";
        $IR[] = "end_return:";

        //zval list gc
        $IR[] = ";prepare var list gc";
        $IR[] = InternalModule::call(InternalModule::ZVAL_LIST_GC, '%zvallist');
        $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_LIST_GC);

        //return
        $returnRegister = $this->getRegisterSerial();
        $IR[] = ";prepare return value";
        $IR[] = "$returnRegister = load " . Zval::PtrIRDeclare() . "* %retval, align " . Zval::PtrIRAlign();
        $IR[] = "ret %struct.zval* $returnRegister";
        return $IR;
    }

    public function writeConstant($constant) {
        return $this->moduleWriter->writeConstant($constant);
    }

    public function writeUsedFunction($functionName) {
        $this->moduleWriter->writeUsedFunction($functionName);
    }

    public function getModuleWriter() {
        return $this->moduleWriter;
    }

    public function write() {
        $this->writeIR();
    }

    public function isZvalIRDefined($varName) {
        return isset($this->varList[$varName]);
    }

    protected function writeVarDeclare(){
        $IR=array(";declare used Var");
        foreach($this->varList as $varZval){
            $IR[]="$varZval = alloca " . Zval::zval('*');
            $IR[]="store ".Zval::zval('*')." null, ".Zval::zval('**')." $varZval, align ".Zval::zval('*')->size();
        }
        return $IR;
    }

    public function getZvalIR($varName, $initZval = true,$isTmp=false) {
        if($isTmp){
            $varZval = "%PHPVarTemp_$varName";
        }
        else{
            $varZval = "%PHPVar_$varName";
        }
        if (isset($this->varList[$varName])) {
            return $this->varList[$varName];
        }
        $this->varList[$varName] = $varZval;
        if ($initZval) {
            $tmpRegister = $this->getRegisterSerial();
            $this->opLinesIR[] = "$tmpRegister = " . InternalModule::call(InternalModule::ZVAL_INIT, '%zvallist');
            $this->opLinesIR[] = "store " . Zval::zval('*') . " $tmpRegister, " . Zval::zval('**') . " $varZval, align " . Zval::zval('*')->size();
            $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_INIT);
        }
        return $this->varList[$varName];
    }

}
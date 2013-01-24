<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\OpLines\OpLine;
use PHPPHP\LLVMEngine\Writer;
use PHPPHP\LLVMEngine\Zval;
use PHPPHP\LLVMEngine\Type\Base as StringType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class ModuleWriter extends Base {

    protected $moduleContext;
    protected $opLines = array();
    protected $opLinesIR = array();
    protected $registerSerial = 0;
    protected $constantSerial = 0;

    public function __construct($moduleContext) {
        $this->moduleContext = $moduleContext;
    }

    public function getModuleName(){
        return 'module_'.md5($this->moduleContext);
    }

    public function getEntryName() {
        return "PHPLLVM_{$this->getModuleName()}_entry";
    }

    protected function writeDeclare() {
        $IR = "declare @{$this->getEntryName()}()";
        $this->writer->writeFunctionIRDeclare($this->getModuleName(),$this->getEntryName(), $IR);
    }

    public function getDeclareIR() {
        return "declare @{$this->getEntryName()}()";
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
        $IR=";module {$this->moduleContext}\n";
        $IR.="define ".Zval::PtrIRDeclare()." @{$this->getEntryName()}() nounwind uwtable {\n";
        $IR.=implode("\n\t",$this->moduleCtorIR())."\n";
        foreach($this->opLinesIR as $opLineIR){
            $IR.="\t$opLineIR\n";
        }
        $IR.=implode("\n\t",$this->moduleDtorIR())."\n";
        $IR.="}";
        $this->writer->writeFunctionIR($this->getModuleName(),$this->getEntryName(), $IR);
    }

    protected function getConstantSerial(){
        return ++$this->constantSerial;
    }

    protected function getRegisterSerial(){
        return ++$this->registerSerial;
    }
    /**
     *
     * @param string $constant
     * @return StringType
     */
    public function writeConstant($constant){
        $constantSerial=$this->getConstantSerial();
        $constantName="@str.{$this->getModuleName()}.$constantSerial";
        $constantLen=strlen($constant);
        $IR="$constantName = private unnamed_addr constant [$constantLen x i8] c\"{$this->escapeString($constant)}\" , align 1";
        $this->writer->writeModuleConstantDeclare($this->getModuleName(), $IR);
        return StringType::char('*',$constantLen,$constantName);
    }

    public function write() {
        $this->writeDeclare();
        $this->writeOpLines();
        $this->writeIR();
    }

    protected function moduleCtorIR(){
        $IR[]='';
        $IR[]=";function entry";

        //prepare return value
        $IR[]="%retval = alloca ".Zval::PtrIRDeclare().", align ".Zval::PtrIRAlign();
        $IR[]="store ".Zval::PtrIRDeclare()." null , ".Zval::PtrIRDeclare()."* %retval, align ".Zval::PtrIRAlign();

        //prepare var list
        $voidType=StringType::void('*');
        $IR[]="%varlist = alloca $voidType, align {$voidType->size()}";
        $IR[]="store $voidType null, $voidType* %varlist, align {$voidType->size()}";
        return $IR;
    }

    protected function moduleDtorIR(){
        $IR[]="";
        $IR[]=";function end";
        $IR[]="end_return:";

        //var list gc
        $varlistRegister="%{$this->getRegisterSerial()}";
        $voidType=StringType::void('*');
        $IR[]=";prepare var list gc";
        $IR[]="$varlistRegister = load $voidType* %varlist, align {$voidType->size()}";
        $IR[]=InternalModule::call(InternalModule::VAR_LIST_GC,$varlistRegister);
        $this->writer->writeUsedFunction(InternalModule::VAR_LIST_GC);

        //return
        $returnRegister="%{$this->getRegisterSerial()}";
        $IR[]=";prepare return value";
        $IR[]="$returnRegister = load ".Zval::PtrIRDeclare()."* %retval, align ".Zval::PtrIRAlign();
        $IR[]="ret %struct.zval* $returnRegister";
        return $IR;
    }
}
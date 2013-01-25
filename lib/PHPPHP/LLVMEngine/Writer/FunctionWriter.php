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
    protected $varList=array();
    /**
     *
     * @var ModuleWriter
     */
    protected $moduleWriter;

    public function __construct($functionName) {
        $this->functionName=  strtolower($functionName);
    }

    public function setModuleWriter(ModuleWriter $moduleWriter){
        $this->moduleWriter=$moduleWriter;
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

    public function writeOpLineIR($opLineIR){
        $this->opLinesIR[]=$opLineIR;
    }

    protected function writeIR(){
        //write declare
        $IR="declare ".Zval::PtrIRDeclare()." @{$this->getEntryName()}()";
        $this->moduleWriter->writeFunctionIRDeclare($this->getEntryName(), $IR);

        //write function content
        $IR=";function {$this->functionName}\n";
        $IR.="define ".Zval::PtrIRDeclare()." @{$this->getEntryName()}() nounwind uwtable {\n";
        $IR.=implode("\n\t",$this->functionCtorIR())."\n";
        foreach($this->opLinesIR as $opLineIR){
            $IR.="\t$opLineIR\n";
        }
        $IR.=implode("\n\t",$this->functionDtorIR())."\n";
        $IR.="}";
        $this->moduleWriter->writeFunctionIR($this->getEntryName(), $IR);
    }

    public function getRegisterSerial(){
        return ++$this->registerSerial;
    }


    protected function functionCtorIR(){
        $IR[]='';
        $IR[]=";function entry";

        //prepare return value
        $IR[]="%retval = alloca ".Zval::PtrIRDeclare().", align ".Zval::PtrIRAlign();
        $IR[]="store ".Zval::PtrIRDeclare()." null , ".Zval::PtrIRDeclare()."* %retval, align ".Zval::PtrIRAlign();

        //prepare var list
        $voidType=StringType::void('*');
        $IR[]="%zvallist = alloca $voidType, align {$voidType->size()}";
        $IR[]="store $voidType null, $voidType* %zvallist, align {$voidType->size()}";
        return $IR;
    }

    protected function functionDtorIR(){
        $IR[]="";
        $IR[]=";function end";
        $IR[]="end_return:";

        //zval list gc
        $zvallistRegister="%{$this->getRegisterSerial()}";
        $voidType=StringType::void('*');
        $IR[]=";prepare var list gc";
        $IR[]="$zvallistRegister = load $voidType* %zvallist, align {$voidType->size()}";
        $IR[]=InternalModule::call(InternalModule::ZVAL_LIST_GC,$zvallistRegister);
        $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_LIST_GC);

        //return
        $returnRegister="%{$this->getRegisterSerial()}";
        $IR[]=";prepare return value";
        $IR[]="$returnRegister = load ".Zval::PtrIRDeclare()."* %retval, align ".Zval::PtrIRAlign();
        $IR[]="ret %struct.zval* $returnRegister";
        return $IR;
    }

    public function writeConstant($constant){
        return $this->moduleWriter->writeConstant($constant);
    }

    public function writeUsedFunction($functionName){
        $this->moduleWriter->writeUsedFunction($functionName);
    }

    public function getModuleWriter(){
        return $this->moduleWriter;
    }

    public function write() {
        $this->writeOpLines();
        $this->writeIR();
    }

    public function getZvalIR($varName){
        if(isset($this->varList[$varName])){
            return $this->varList[$varName];
        }
        $varZval="%PHPVar_$varName";
        $this->varList[$varName]=$varZval;
        $voidType=StringType::void('*');
        $varZvalRegister="%{$this->getRegisterSerial()}";
        $this->opLinesIR[]="$varZval = alloca ".Zval::PtrIRDeclare().", align ".Zval::PtrIRAlign();
        $this->opLinesIR[]="$varZvalRegister = ".InternalModule::call(InternalModule::ZVAL_INIT);
        $this->opLinesIR[]="store ".Zval::PtrIRDeclare()." $varZvalRegister, ".Zval::PtrIRDeclare()."* $varZval, align ".Zval::PtrIRAlign();
        $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_INIT);
        return $this->varList[$varName];
    }

}
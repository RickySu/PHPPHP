<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\Zval;
use PHPPHP\LLVMEngine\Register;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;
use PHPPHP\LLVMEngine\OpLines\OpLine;
use PHPPHP\LLVMEngine\Type\Base as StringType;
use PHPPHP\LLVMEngine\Type\TypeDefine;

class FunctionWriter {

    protected $opLines = array();
    protected $opLinesIR = array();
    protected $registerSerial = 0;
    protected $functionName;
    protected $varList = array();
    protected $internalVarList = array();
    protected $opJumpLabel = array();

    const RETVAL = '%retval';

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

    public function setJumpLabel($opLineNo) {
        $this->writeOpLineIR(";set jump label $opLineNo");
        $this->opJumpLabel[$opLineNo] = true;
    }

    public function writeJumpLabelIR($opLineNo) {
        $this->setJumpLabel($opLineNo);
        $Label = substr($this->getJumpLabel($opLineNo), 0, -1);
        $this->writeOpLineIR("br label %$Label\n");
    }

    public function getJumpLabelIR($opLineNo) {
        $IR = '';
        if ($this->isSetJumpLable($opLineNo)) {
            $Label = substr($this->getJumpLabel($opLineNo), 0, -1);
            $IR.="br label %$Label\n";
        }
        return $IR;
    }

    public function getJumpLabel($opLineNo) {
        if ($this->isSetJumpLable($opLineNo)) {
            return "op_jump_label_$opLineNo:";
        }
        return '';
    }

    public function isSetJumpLable($opLineNo) {
        return isset($this->opJumpLabel[$opLineNo]);
    }

    public function writeOpLineIR($opLineIR) {
        $this->opLinesIR[] = $opLineIR;
    }

    protected function writeIR() {
        //write declare
        $EntryDeclareIR = "declare " . Zval::zval('*') . " @{$this->getEntryName()}()";
        $this->moduleWriter->writeFunctionIRDeclare($this->getEntryName(), $EntryDeclareIR);

        $opLineIRs = array();
        $this->writeOpLines();
        foreach ($this->opLinesIR as $opLineIR) {
            $opLineIRs[] = "\t$opLineIR";
        }

        //write function content
        $IR[] = ";function {$this->functionName}";
        $IR[] = "define " . Zval::zval('*') . " @{$this->getEntryName()}() nounwind uwtable {";
        $IR[] = implode("\n\t", $this->functionCtorIR());
        $varIRDeclare = "\n\t" . implode("\n\t", $this->writeVarDeclare());
        $IR[] = $varIRDeclare;
        $IR = array_merge($IR, $opLineIRs);
        $IR[] = implode("\n\t", $this->functionDtorIR());
        $IR[] = "}";
        $this->moduleWriter->writeFunctionIR($this->getEntryName(), implode("\n", $IR));
    }

    public function getRegisterSerial() {
        return new Register(++$this->registerSerial);
    }

    protected function functionCtorIR() {
        $IR[] = '';
        $IR[] = ";function entry";

        //prepare return value
        $IR[] = self::RETVAL . " = alloca " . Zval::zval('*') . ", align " . Zval::zval('*')->size();
        $IR[] = "store " . Zval::zval('*') . " null , " . Zval::zval('**') . " %retval, align " . Zval::zval('*')->size();

        //prepare var list
        $IR[] = Zval::ZVAL_GC_LIST . ' = ' . InternalModule::call(InternalModule::ZVAL_LIST_INIT);
        $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_LIST_INIT);
        $IR[] = Zval::ZVAL_TEMP_GC_LIST . ' = ' . InternalModule::call(InternalModule::ZVAL_TEMP_LIST_INIT);
        $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_TEMP_LIST_INIT);
        return $IR;
    }

    protected function functionDtorIR() {
        $IR[] = "";
        $IR[] = ";function end";
        $IR[] = "end_return:";

        //zval list gc
        $IR[] = ";prepare var list gc";
        $IR[] = InternalModule::call(InternalModule::ZVAL_LIST_GC, Zval::ZVAL_GC_LIST);
        $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_LIST_GC);
        $IR[] = InternalModule::call(InternalModule::ZVAL_TEMP_LIST_GC, Zval::ZVAL_TEMP_GC_LIST);
        $this->moduleWriter->writeUsedFunction(InternalModule::ZVAL_TEMP_LIST_GC);

        //return
        $returnRegister = $this->getRegisterSerial();
        $IR[] = ";prepare return value";
        $IR[] = "$returnRegister = load " . Zval::zval('**') . ' ' . self::RETVAL . ", align " . Zval::zval('*')->size();
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
        $zval = new Zval($varName, false, false, $this);
        return isset($this->varList[(string) $zval]);
    }

    protected function writeVarDeclare() {
        $IR = array(";declare internal var");
        foreach ($this->internalVarList as $interlanVar => $type) {
            $IR[] = "$interlanVar = alloca $type";
        }
        $IR[] = '';
        $IR[] = ";declare used var";
        foreach ($this->varList as $varZval) {
            $IR[] = "$varZval = alloca " . Zval::zval('*');
            $IR[] = "store " . Zval::zval('*') . " null, " . Zval::zval('**') . " $varZval, align " . Zval::zval('*')->size();
        }
        return $IR;
    }

    public function getZvalIR($varName, $initZval = true, $isTmp = false) {
        $zval = new Zval($varName, false, $isTmp, $this);
        if (isset($this->varList[(string) $zval])) {
            return $this->varList[(string) $zval];
        }
        $zval = new Zval($varName, $initZval, $isTmp, $this);
        $this->varList[(string) $zval] = $zval;
        return $zval;
    }

    public function getInternalVar($varName, TypeDefine $type) {
        $interlanVar = "%PHPVarInternal_$varName";
        if (isset($this->internalVarList[$interlanVar])) {
            return $interlanVar;
        }
        $this->internalVarList[$interlanVar] = $type;
        return $interlanVar;
    }

    public function InternalModuleCall($moduleName) {
        $args = func_get_args();
        $IR = forward_static_call_array(array('\PHPPHP\\LLVMEngine\\Internal\\Module', 'call'), $args);
        $this->moduleWriter->writeUsedFunction($moduleName);
        if (InternalModule::Define()[$moduleName][0] != StringType::void()) {
            $resultRegister = $this->getRegisterSerial();
            $this->writeOpLineIR("$resultRegister = $IR");
            return $resultRegister;
        }
        $this->writeOpLineIR($IR);
        return NULL;
    }

}
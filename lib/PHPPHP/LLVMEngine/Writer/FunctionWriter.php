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
    protected $params;

    const RETVAL = '%retval';
    const JUMPTABLEOBJ = '%jumptableobj';
    const ARGSCOUNT='%nArgs';

    /**
     *
     * @var ModuleWriter
     */
    protected $moduleWriter;

    public function __construct($functionName, $params = array()) {
        $this->functionName = strtolower($functionName);
        $this->params = $params;
    }

    public function setModuleWriter(ModuleWriter $moduleWriter) {
        $this->moduleWriter = $moduleWriter;
    }

    public function getFunctionName() {
        return $this->functionName;
    }

    public function getParamsTypeDefine() {
        if (!$this->params) {
            return '';
        }
        $paramString = '';
        foreach ($this->params as $param) {
            $paramString.=Zval::zval('*') . ', ';
        }
        return substr(trim($paramString), 0, -1);
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
        $paramIR = '';
        $paramIR.=StringType::void('*').' '.self::JUMPTABLEOBJ.", ".StringType::int().' '.self::ARGSCOUNT.' , ';
        if ($this->params) {
            foreach ($this->params as $index => $param) {
                $paramIR.=Zval::zval('*') . " %param_$index, ";
                $paramZval = $this->getZvalIR($param->name);
                $paramZval->setInitValue("%param_$index");
            }
        }
        $paramIR = substr(trim($paramIR), 0, -1);
        $EntryDeclareIR = "declare " . Zval::zval('*') . " @{$this->getEntryName()}($paramIR)";
        $this->moduleWriter->writeFunctionIRDeclare($this->getEntryName(), $EntryDeclareIR);

        $opLineIRs = array();
        $this->writeOpLines();
        foreach ($this->opLinesIR as $opLineIR) {
            $opLineIRs[] = "\t$opLineIR";
        }

        //write function content
        $IR[] = ";function {$this->functionName}";
        $IR[] = "define " . Zval::zval('*') . " @{$this->getEntryName()}($paramIR) nounwind uwtable {";
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
        foreach ($this->internalVarList as $interlanVar => $varDefine) {
            list($type, $defaultValue) = $varDefine;
            $IR[] = "$interlanVar = alloca $type, align {$type->size()}";
            if ($defaultValue !== NULL) {
                $IR[] = "store $type $defaultValue, $type* $interlanVar, align {$type->size()}";
            }
        }
        $IR[] = '';
        $IR[] = ";declare used var";
        foreach ($this->varList as $varZval) {
            $IR[] = "$varZval = alloca " . Zval::zval('*');
            $initValue = ($varZval->getInitValue() === false ? 'null' : $varZval->getInitValue());
            $IR[] = "store " . Zval::zval('*') . " $initValue, " . Zval::zval('**') . " $varZval, align " . Zval::zval('*')->size();
            if ($varZval->isStoreVarName()) {
                $varName = $varZval->getVarName();
                $Constant = $this->writeConstant($varName);
                $IR[] = $this->getInternalModuleCallIR(InternalModule::ZVAL_GC_REGISTER, Zval::getGCList(), $varZval, sizeof($varName), $Constant->ptr());
            } else {
                $IR[] = $this->getInternalModuleCallIR(InternalModule::ZVAL_GC_REGISTER, Zval::getGCList(), $varZval, 0, 'null');
            }
        }

        return $IR;
    }

    public function getZvalIR($varName, $initZval = false, $isTmp = false) {
        $zval = new Zval($varName, false, $isTmp, $this);
        if (isset($this->varList[(string) $zval])) {
            return $this->varList[(string) $zval];
        }
        $zval = new Zval($varName, $initZval, $isTmp, $this);
        $this->varList[(string) $zval] = $zval;

        return $zval;
    }

    public function getJumpTable($functionName) {
        return $this->moduleWriter->getJumpTable($functionName);
    }

    public function getInternalVar($varName, TypeDefine $type, $init = NULL) {
        $interlanVar = "%PHPVarInternal_$varName";
        if (isset($this->internalVarList[$interlanVar])) {
            return $interlanVar;
        }
        $this->internalVarList[$interlanVar] = array($type, $init);

        return $interlanVar;
    }

    protected function getInternalModuleCallIR($moduleName) {
        $args = func_get_args();
        $IR = forward_static_call_array(array('\PHPPHP\\LLVMEngine\\Internal\\Module', 'call'), $args);
        $this->moduleWriter->writeUsedFunction($moduleName);

        return $IR;
    }

    public function InternalModuleCall($moduleName) {
        $args = func_get_args();
        $IR = call_user_func_array(array($this, 'getInternalModuleCallIR'), $args);
        list($fastcc, $return, $argTypes) = InternalModule::Define()[$moduleName];
        if ($return != StringType::void()) {
            $resultRegister = $this->getRegisterSerial();
            $this->writeOpLineIR("$resultRegister = $IR");
            return $resultRegister;
        }
        $this->writeOpLineIR($IR);

        return NULL;
    }

}

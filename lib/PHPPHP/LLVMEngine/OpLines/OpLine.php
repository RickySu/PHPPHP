<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Writer\FunctionWriter;
use PHPPHP\Engine\OpLine as opCode;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

abstract class OpLine {

    /**
     *
     * @var FunctionWriter
     */
    protected $function;

    /**
     *
     * @var opCode
     */
    protected $opCode;
    protected $opLineNo;
    protected $tempZval = array();

    //private $resultRegister=NULL;

    protected function getResultRegister(){
        if(!isset($this->opCode->result->resultRegister)){
            $this->opCode->result->resultRegister=substr($this->function->getRegisterSerial(),1);
        }
        return $this->opCode->result->resultRegister;
    }

    public function __construct(opCode $opCode, $opLineNo) {
        $this->opCode = $opCode;
        $this->opLineNo = $opLineNo;
    }

    public function setFunction(FunctionWriter $function) {
        $this->function = $function;
    }

    public function write() {
        $className = explode('\\', get_class($this));
        $className = $className[count($className) - 1];
        $this->writeDebugInfo();
        $this->writeDebugInfo("line {$this->opCode->lineno} $className");
        $this->function->writeOpLineIR($this);
    }

    protected function writeDebugInfo($info = null) {
        if ($info === null) {
            $this->function->writeOpLineIR('');
            return;
        }
        $this->function->writeOpLineIR("; $info");
    }

    protected function writeGetIsRefIR($varZvalPtr) {
        $isRefRegisterPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMZval::zval()->getStructIR()->getElementPtrIR($isRefRegisterPtr, $varZvalPtr, 'is_ref'));
        $isRefRegister = $this->function->getRegisterSerial();
        $isRefType = LLVMZval::zval()->getStructIR()->getElement('is_ref');
        $this->function->writeOpLineIR("$isRefRegister = load $isRefType* $isRefRegisterPtr, align " . $isRefType->size());
        return array($isRefRegister, $isRefRegisterPtr);
    }

    protected function writeGetRefCountIR($varZvalPtr) {
        $refCountRegisterPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMZval::zval()->getStructIR()->getElementPtrIR($refCountRegisterPtr, $varZvalPtr, 'refcount'));
        $refCountRegister = $this->function->getRegisterSerial();
        $refCountType = LLVMZval::zval()->getStructIR()->getElement('refcount');
        $this->function->writeOpLineIR("$refCountRegister = load $refCountType* $refCountRegisterPtr, align " . $refCountType->size());
        return array($refCountRegister, $refCountRegisterPtr);
    }

    protected function gcVarZval(LLVMZval $varZval, $emptyVarZval = true) {
        $this->function->InternalModuleCall(InternalModule::ZVAL_GC, $varZval->getPtrRegister());
        if ($emptyVarZval) {
            $varZval->savePtrRegister(BaseType::null());
        }
        $this->writeDebugInfo("gc tmp zval $varZval\n");
    }

    protected function getStringValue(LLVMZval $varZval) {
        $InternalVarInt = $this->function->getInternalVar('getStringValue_len', BaseType::int());
        $InternalVarCharPtr = $this->function->getInternalVar('getStringValue_val', BaseType::char('*'));
        $this->function->InternalModuleCall(InternalModule::ZVAL_STRING_VALUE, $varZval->getPtrRegister(), $InternalVarInt, $InternalVarCharPtr);
        return array($InternalVarInt, $InternalVarCharPtr);
    }

    public function __toString() {
        $IR = '';
        $IR.=$this->function->getJumpLabelIR($this->opLineNo);
        $IR.=$this->function->getJumpLabel($this->opLineNo);
        return $IR;
    }

    protected function registTempZval(LLVMZval $opZval) {
        $this->tempZval[] = $opZval;
    }

    protected function gcTempZval() {
        foreach ($this->tempZval as $varZval) {
            $this->gcVarZval($varZval);
        }
    }

}
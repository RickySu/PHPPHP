<?php
namespace PHPPHP\LLVMEngine\OpLines;
use PHPPHP\LLVMEngine\Writer\FunctionWriter;
use PHPPHP\Engine\OpLine as opCode;
use PHPPHP\LLVMEngine\Zval as LLVMZval;

abstract class OpLine{
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

    public function __construct(opCode $opCode) {
        $this->opCode=$opCode;
    }

    public function setFunction(FunctionWriter $function){
        $this->function=$function;
    }

    public function write(){
        $className=explode('\\',get_class($this));
        $className=$className[count($className)-1];
        $this->writeDebugInfo();
        $this->writeDebugInfo("line {$this->opCode->lineno} $className");
    }

    protected function writeDebugInfo($info=null){
        if($info===null){
            $this->function->writeOpLineIR('');
            return;
        }
        $this->function->writeOpLineIR("; $info");
    }

    protected function writeGetIsRefIR($varZvalPtr){
        $isRefRegisterPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMZval::zval()->getStructIR()->getElementPtrIR($isRefRegisterPtr, $varZvalPtr, 'is_ref'));
        $isRefRegister = $this->function->getRegisterSerial();
        $isRefType = LLVMZval::zval()->getStructIR()->getElement('is_ref');
        $this->function->writeOpLineIR("$isRefRegister = load $isRefType* $isRefRegisterPtr, align " . $isRefType->size());
        return array($isRefRegister,$isRefRegisterPtr);
    }

    protected function writeGetRefCountIR($varZvalPtr){
        $refCountRegisterPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMZval::zval()->getStructIR()->getElementPtrIR($refCountRegisterPtr, $varZvalPtr, 'refcount'));
        $refCountRegister = $this->function->getRegisterSerial();
        $refCountType = LLVMZval::zval()->getStructIR()->getElement('refcount');
        $this->function->writeOpLineIR("$refCountRegister = load $refCountType* $refCountRegisterPtr, align " . $refCountType->size());
        return array($refCountRegister,$refCountRegisterPtr);
    }

    public function gcVarZval($varZval,$emptyVarZval=true){
        $varZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$varZvalPtr = load " . LLVMZval::zval('**') . " $varZval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_GC,'%zvallist',$varZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_GC);
        if($emptyVarZval){
            $this->function->writeUsedFunction("store " . LLVMZval::zval('*') . " null , " . LLVMZval::zval('**') . " $varZval, align " . Zval::PtrIRAlign());
        }
    }
}
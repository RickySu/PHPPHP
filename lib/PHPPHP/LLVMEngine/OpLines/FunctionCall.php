<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class FunctionCall extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $functionName = strtolower($this->opCode->InitFCallByNameOp->op2->getImmediateZval()->getValue());
        $jumpTable = $this->function->getJumpTable($functionName);
        $jumpTablePtrRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$jumpTablePtrRegister = bitcast ".$jumpTable::jumpTable('*')." {$jumpTable->getIRRegister()} to ".BaseType::void('*'));
        $this->function->InternalModuleCall(InternalModule::PHPLLVM_FUNCTION_CALL_BY_NAME,$jumpTablePtrRegister);
        $callParams = array(BaseType::int().' '.count($this->opCode->InitFCallByNameOp->FCallParams));
        foreach ($this->opCode->InitFCallByNameOp->FCallParams as $paramZval) {
            if($paramZval->isTemp()){
                $this->registTempZval($paramZval);
            }
            $callParams[] = $paramZval::zval('*') . ' ' . $paramZval->getPtrRegister();
        }
        $remoteFunctionCallRegister = $this->function->getRegisterSerial();
        $realfunctionRegisterPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR($jumpTable::jumpTable('*')->getStructIR()->getElementPtrIR($realfunctionRegisterPtr, $jumpTable->getIRRegister(), 'realfunction'));
        $argTypes=LLVMZval::zval('*')." (".BaseType::int().",  ...)*";
        $returnZvalRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("{$remoteFunctionCallRegister}_ptr = load " . BaseType::void('**') . " $realfunctionRegisterPtr, align " . BaseType::void('*')->size());
        $this->function->writeOpLineIR("{$remoteFunctionCallRegister} = bitcast ".BaseType::void('*')." {$remoteFunctionCallRegister}_ptr to $argTypes");
        $this->function->writeOpLineIR("$returnZvalRegister = call $argTypes {$remoteFunctionCallRegister}(".implode(', ',$callParams).")");
        $returnZval = new LLVMZval(NULL, false, true, $this->function);
        $returnZval->savePtrRegister($returnZvalRegister);
        $this->setResult($returnZval);
        if($this->opCode->result->markUnUsed){
            $this->function->InternalModuleCall(InternalModule::ZVAL_GC,$returnZvalRegister);
        }
        $this->gcTempZval();
    }

}

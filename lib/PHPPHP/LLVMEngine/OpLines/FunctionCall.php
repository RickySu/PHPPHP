<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class FunctionCall extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->function->writeUsedFunction(InternalModule::PHPLLVM_FUNCTION_CALL_BY_NAME);
        $functionName = $this->opCode->InitFCallByNameOp->op2->getImmediateZval()->getValue();
        $jumpTable = $this->function->getJumpTable($functionName);
        $jumpTablePtrRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$jumpTablePtrRegister = bitcast ".$jumpTable::jumpTable('*')." {$jumpTable->getIRRegister()} to ".BaseType::void('*'));
        $callParams = array(BaseType::void('*')." $jumpTablePtrRegister", BaseType::int().' '.count($this->opCode->InitFCallByNameOp->FCallParams));
        foreach ($this->opCode->InitFCallByNameOp->FCallParams as $paramZval) {
            $this->registTempZval($paramZval);
            $callParams[] = $paramZval::zval('*') . ' ' . $paramZval->getPtrRegister();
        }
        $remoteFunctionCallRegister = $this->function->getRegisterSerial();
        $realfunctionRegisterPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR($jumpTable::jumpTable('*')->getStructIR()->getElementPtrIR($realfunctionRegisterPtr, $jumpTable->getIRRegister(), 'realfunction'));
        list($fastcc, $return, $argTypes) = InternalModule::Define()[InternalModule::PHPLLVM_FUNCTION_CALL_BY_NAME];
        $argTypes=implode(', ',$argTypes);
        $this->function->writeOpLineIR("{$remoteFunctionCallRegister}_ptr = load " . BaseType::void('**') . " $realfunctionRegisterPtr, align " . BaseType::void('*')->size());
        $this->function->writeOpLineIR("{$remoteFunctionCallRegister} = bitcast ".BaseType::void('*')." {$remoteFunctionCallRegister}_ptr to $return ($argTypes)*");
        $this->function->writeOpLineIR("%tmp = call $return ($argTypes)* {$remoteFunctionCallRegister}(".implode(', ',$callParams).")");
        $this->gcTempZval();
    }

}

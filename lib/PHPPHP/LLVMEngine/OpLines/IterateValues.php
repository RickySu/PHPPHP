<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class IterateValues extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $this->opCode->op1->iterateObjectVarName;
        $iterateObjectVarRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$iterateObjectVarRegister = load ".BaseType::void('**')." {$this->opCode->op1->iterateObjectVarName}, align ".BaseType::void('**')->size());
        $this->writeSetKey($iterateObjectVarRegister);
        $this->writeSetValue($iterateObjectVarRegister);
        $this->gcTempZval();
    }

    protected function writeSetKey($iterateObjectVarRegister){
        if(!$this->opCode->op2){
            return;
        }
        $keyZval = $this->function->getZvalIR($this->opCode->op2->getImmediateZval()->getName());
        $keyRegister=$this->function->InternalModuleCall(InternalModule::ZVAL_ITERATE_CURRENT_KEY,$iterateObjectVarRegister);
        $keyZval->savePtrRegister($keyRegister);
        $this->opCode->op1->iterateKeyZval=$keyZval;
    }

    protected function writeSetValue($iterateObjectVarRegister){
        $valueZval = $this->function->getZvalIR($this->opCode->result->getImmediateZval()->getName());
        $valueRegister=$this->function->InternalModuleCall(InternalModule::ZVAL_ITERATE_CURRENT_VALUE,$iterateObjectVarRegister);
        $valueZvalRegister=$this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ZVAL,$valueZval->getPtrRegister(),$valueRegister);
        $valueZval->savePtrRegister($valueZvalRegister);
    }
}

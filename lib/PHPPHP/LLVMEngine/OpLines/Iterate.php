<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;
use PHPPHP\LLVMEngine\Zval as LLVMZval;

class Iterate extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $iterateObjectVarName=$this->function->getInternalVar(substr($this->function->getRegisterSerial(),1), BaseType::void('*'), 'null');
        $this->opCode->result->iterateObjectVarName=$iterateObjectVarName;
        $this->writeExitLoopIfEndofElement($iterateObjectVarName);
        $this->gcTempZval();
    }

    protected function writeExitLoopIfEndofElement($iterateObjectVarName)
    {
        $iterateObjectVarRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$iterateObjectVarRegister = load ".BaseType::void('**')." $iterateObjectVarName, align ".BaseType::void('**')->size());
        $isNULLResult = $this->function->getRegisterSerial();
        $ifSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfTrue = "Label_IfTrue_$ifSerial";
        $LabelEndIf = "Label_EndIf_$ifSerial";
        $this->function->writeOpLineIR("$isNULLResult = icmp eq ".BaseType::void('*')." $iterateObjectVarRegister, null");
        $this->function->writeOpLineIR("br i1 $isNULLResult, label  %$LabelIfTrue, label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelIfTrue:");
        $arrayZval = $this->function->getZvalIR($this->opCode->op1->getImmediateZval()->getName());
        $iterateObjectRegister=$this->function->InternalModuleCall(InternalModule::ZVAL_ITERATE_INIT,$arrayZval->getPtrRegister());
        $this->function->writeOpLineIR("store ".BaseType::void('*')." $iterateObjectRegister, ".BaseType::void('**')." $iterateObjectVarName, align ".BaseType::void('*')->size());
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
        $iterateObjectVarRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$iterateObjectVarRegister = load ".BaseType::void('**')." $iterateObjectVarName, align ".BaseType::void('**')->size());
        $keyZval = new LLVMZval(NULL, false, false, $this->function);
        $keyZvalRegister=$this->function->InternalModuleCall(InternalModule::ZVAL_ITERATE_CURRENT_KEY,$iterateObjectVarRegister);
        $keyZval->savePtrRegister($keyZvalRegister);

        $isNULLResult = $this->function->getRegisterSerial();
        $ifSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfTrue = "Label_IfTrue_$ifSerial";
        $LabelEndIf = "Label_EndIf_$ifSerial";
        $this->function->writeOpLineIR("$isNULLResult = icmp eq {$keyZval->zval('*')} $keyZvalRegister, null");
        $this->function->writeOpLineIR("br i1 $isNULLResult, label  %$LabelIfTrue, label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelIfTrue:");
        $this->function->InternalModuleCall(InternalModule::ZVAL_ITERATE_FREE,$iterateObjectVarRegister);
        $this->function->writeOpLineIR("store ".BaseType::void('*')." null, ".BaseType::void('**')." $iterateObjectVarName, align ".BaseType::void('*')->size());
        $this->gcTempZval();
        $this->function->writeJumpLabelIR($this->opCode->op2);
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");

        $this->opCode->result->iterateKeyZval=$keyZval;
    }
}

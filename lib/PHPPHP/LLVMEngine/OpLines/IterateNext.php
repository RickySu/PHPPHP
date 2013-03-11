<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;
use PHPPHP\LLVMEngine\Zval as LLVMZval;


class IterateNext extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $this->opCode->op1->iterateObjectVarName;
        $iterateObjectVarRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$iterateObjectVarRegister = load ".BaseType::void('**')." {$this->opCode->op1->iterateObjectVarName}, align ".BaseType::void('**')->size());

        if (isset($this->opCode->op1->iterateKeyZval)) {
            $this->gcVarZval($this->opCode->op1->iterateKeyZval);
        }

        $this->function->InternalModuleCall(InternalModule::ZVAL_ITERATE_NEXT,$iterateObjectVarRegister);
        $this->gcTempZval();

        $this->function->writeJumpLabelIR($this->opCode->op2-1); //jump to Iterate Op
    }

}

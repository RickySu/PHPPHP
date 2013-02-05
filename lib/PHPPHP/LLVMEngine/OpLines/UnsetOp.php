<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class UnsetOp extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $op1Var = $this->opCode->op1->getImmediateZval();

        if (!$this->function->isZvalIRDefined($op1Var->getName())) {
            return;
        }
        $op1Zval=$this->function->getZvalIR($op1Var->getName(),false);
        $this->gcVarZval($op1Zval);
        $this->gcTempZval();
    }

}

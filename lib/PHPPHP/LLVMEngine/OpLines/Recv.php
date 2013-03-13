<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Recv extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $this->prepareParam($this->opCode->op1->getValue());
        $this->gcTempZval();
    }

    public function prepareParam($index){
        $param=$this->function->getParam($index);
        $paramZval=$this->function->getZvalIR($param->name);
        $srcZval=new LLVMZval(NULL,false,true,$this->function);
        $srcZval->savePtrRegister("%param_$index");
        $this->writeVarAssign($paramZval, $srcZval);
    }
}

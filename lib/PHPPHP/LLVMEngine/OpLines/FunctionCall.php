<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class FunctionCall extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write()
    {
        parent::write();
        $callParams=array(InternalModule::PHPLLVM_FUNCTION_CALL_BY_NAME,'null',count($this->opCode->InitFCallByNameOp->FCallParams));
        foreach($this->opCode->InitFCallByNameOp->FCallParams as $paramZval){
            $this->registTempZval($paramZval);
            $callParams[]=$paramZval::zval('*').' '.$paramZval->getPtrRegister();
        }
//        print_r($callParams);
        call_user_func_array(array($this->function,'InternalModuleCall'), $callParams);
        //$IR=$this->function->InternalModuleStackCall(InternalModule::PHPLLVM_FUNCTION_CALL_BY_NAME,'null',10,'a');
        //$this->function->writeOpLineIR($IR);
        //print_r($this->opCode->InitFCallByNameOp->FCallParams);
        //die;
        $this->gcTempZval();
    }

}

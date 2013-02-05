<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Equal extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();

        list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);

        if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {
            $this->writeEqual($resultZval, $op1Zval, $op2Zval);
        } else {
            $this->writeImmediateValueAssign($resultZval, $op1Zval == $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function writeEqual(LLVMZval $resultZval, LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $resultZvalRegister=$this->function->InternalModuleCall(InternalModule::ZVAL_EQUAL,$op1Zval->getPtrRegister(),$op2Zval->getPtrRegister());
        $this->writeAssignBoolean($resultZval, $resultZvalRegister);
    }

}

<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class IssetOp extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $op1Var = $this->opCode->op1->getImmediateZval();

        if (!$this->function->isZvalIRDefined($op1Var->getName())) {
            $this->writeImmediateValueAssign($resultZval, false);
            return;
        }
        $op1Zval=$this->function->getZvalIR($op1Var->getName(),false);
        $this->testNULL($resultZval, $op1Zval);
        $this->gcTempZval();
    }

    protected function testNULL(LLVMZval $resultZval, LLVMZval $op1Zval) {
        $op1ZvalPtr = $op1Zval->getPtrRegister();
        $isNULL = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$isNULL = icmp eq ".LLVMZval::zval('*')." {$op1Zval->getPtrRegister()}, null");
        $isNotNULLLabel=substr($this->function->getRegisterSerial(),1)."_is_NOT_NULL";
        $isNULLLabel=substr($this->function->getRegisterSerial(),1)."_isNULL";
        $isNULLEndifLabel=substr($this->function->getRegisterSerial(),1)."_Endif";
        $this->function->writeOpLineIR("br i1 $isNULL, label  %$isNULLLabel, label %$isNotNULLLabel");
        $this->function->writeOpLineIR("$isNULLLabel:");
        $this->writeImmediateValueAssign($resultZval, false);
        $this->function->writeOpLineIR("br label %$isNULLEndifLabel");
        $this->function->writeOpLineIR("$isNotNULLLabel:");
        $this->writeImmediateValueAssign($resultZval, true);
        $this->function->writeOpLineIR("br label %$isNULLEndifLabel");
        $this->function->writeOpLineIR("$isNULLEndifLabel:");
    }

}

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
        if (!$this->opCode->result->markUnUsed) {
            $this->prepareOpZval($this->opCode->op1);
        }
        $this->gcTempZval();
    }

    protected function writeZval(LLVMZval $opZval) {
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, true, true);
        $isNULL = $this->function->InternalModuleCall(InternalModule::ZVAL_TEST_NULL, $opZval->getPtrRegister());
        $resultRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultRegister = xor " . BaseType::long() . " $isNULL, 1");
        $this->writeAssignBoolean($resultZval, $resultRegister);
        $this->setResult($resultZval);
    }

}

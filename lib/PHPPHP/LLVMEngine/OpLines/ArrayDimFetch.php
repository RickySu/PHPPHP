<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class ArrayDimFetch extends OpLine
{
    use Parts\TypeCast,
        Parts\PrepareOpZval,
        Parts\ArrayOp;

    public function write()
    {
        parent::write();
        if (!$this->opCode->result->markUnUsed) {
            $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        }
        $this->gcTempZval();
    }

    protected function prepareForWrite(LLVMZval $op1Zval)
    {
        $forWrite = (isset($this->opCode->write) && ($this->opCode->write == true));
        if ($forWrite) {
            $op1Zval->savePtrRegister($this->function->InternalModuleCall(InternalModule::ZVAL_INIT_ARRAY, $op1Zval->getPtrRegister()));
        }

        return $forWrite;
    }

    protected function writeZvalZval(LLVMZval $op1Zval, LLVMZval $dimZval)
    {
        $this->assignResultZval($this->writeFetchVarElementArray($op1Zval, $dimZval,$this->prepareForWrite($op1Zval)));
    }

    protected function writeZvalValue(LLVMZval $op1Zval, $dimValue)
    {
        $forWrite= $this->prepareForWrite($op1Zval);
        if (!is_numeric($dimValue)) {
            $this->assignResultZval($this->writeFetchStringElementArray($op1Zval, $dimValue, $forWrite));

            return;
        }
        $dimValue = (int) $dimValue;
        if ($dimValue < 0) {
            $this->assignResultZval($this->writeFetchStringElementArray($op1Zval, $dimValue, $forWrite));

            return;
        }
        $this->assignResultZval($this->writeFetchIntegerElementArray($op1Zval, $dimValue, $forWrite));
    }

    protected function assignResultZval($resultRegister)
    {
        $resultZval = $this->function->getZvalIR($this->getResultRegister(), false, true);
        $resultZvalRegister = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ZVAL, $resultZval->getPtrRegister(), $resultRegister);
        $resultZval->savePtrRegister($resultZvalRegister);
        $this->setResult($resultZval);
    }

}

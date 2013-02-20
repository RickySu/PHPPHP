<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Concat extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        if (!$this->opCode->result->markUnUsed) {
            $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
        }
        $this->gcTempZval();
    }

    protected function writeZvalZval(LLVMZval $op1Zval, LLVMZval $op2Zval) {
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeVarAssign($resultZval, $op1Zval);
        $resultZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, $resultZval->getGCList(), $resultZval->getPtrRegister(), $op2Zval->getPtrRegister());
        $resultZval->savePtrRegister($resultZvalPtr);
        $this->setResult($resultZval);
    }

    protected function writeZvalValue(LLVMZval $op1Zval, $value) {
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeVarAssign($resultZval, $op1Zval);
        if ($value !== '' && $value !== NULL && $value!==false) {
            $constant = $this->function->writeConstant($value);
            $resultZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_STRING, $resultZval->getGCList(), $resultZval->getPtrRegister(), strlen($value), $constant->ptr());
            $resultZval->savePtrRegister($resultZvalPtr);
        }
        $this->setResult($resultZval);
    }

    protected function writeValueValue($value1, $value2) {
        $this->setResult($value1 . $value2);
    }

    protected function writeValueZval($value, LLVMZval $op1Zval) {
        $resultZvalRegister = $this->getResultRegister();
        $resultZval = $this->function->getZvalIR($resultZvalRegister, false, true);
        $this->writeImmediateValueAssign($resultZval, $value);
        $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, $resultZval->getGCList(), $resultZval->getPtrRegister(), $op1Zval->getPtrRegister());
        $this->setResult($resultZval);
    }

    /*
      use Parts\PrepareOpZval;

      public function write() {

      parent::write();
      list($op1Zval, $op2Zval) = $this->prepareOpZval($this->opCode->op1, $this->opCode->op2);
      $resultZval = $this->prepareResultZval();
      if ($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval) {

      $this->writeVarAssign($resultZval, $op1Zval);
      $resultZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, $resultZval->getGCList(), $resultZval->getPtrRegister(), $op2Zval->getPtrRegister());
      $resultZval->savePtrRegister($resultZvalPtr);
      } else {
      $this->writeImmediateValueAssign($resultZval, $op1Zval . $op2Zval);
      }
      $this->gcTempZval();
      }
     */
}
<?php

namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

trait ArrayOp {

    protected function writeAssignEmptyArray(LLVMZval $op1Zval) {
        $this->writeDebugInfo("$op1Zval <= (array)");
        $this->gcVarZval($op1Zval, false);
        $op1ZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_INIT);
        $this->function->InternalModuleCall(InternalModule::ZVAL_INIT_ARRAY, $op1ZvalPtr);
        $op1Zval->savePtrRegister($op1ZvalPtr);
        return $op1ZvalPtr;
    }

    protected function writeAssignNextElementArrayVar(LLVMZval $arrayZval, LLVMZval $valueZval) {
        $this->writeDebugInfo("{$arrayZval}[] <= (var)");
        $arrayZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ARRAY_NEXT_ELEMENT, $arrayZval->getPtrRegister(), $valueZval->getPtrRegister());
        $arrayZval->savePtrRegister($arrayZvalPtr);
        return $arrayZvalPtr;
    }

    protected function writeAssignIntegerElementArrayVar(LLVMZval $arrayZval, LLVMZval $valueZval, $index) {
        $this->writeDebugInfo("{$arrayZval}[(int) $index] <= (var)");
        $arrayZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ARRAY_INTEGER_ELEMENT, $arrayZval->getPtrRegister(), $valueZval->getPtrRegister(), $index);
        $arrayZval->savePtrRegister($arrayZvalPtr);
        return $arrayZvalPtr;
    }

    protected function writeAssignStringElementArrayVar(LLVMZval $arrayZval, LLVMZval $valueZval, $index) {
        $this->writeDebugInfo("{$arrayZval}[(string) $index] <= (var)");
        $constant = $this->function->writeConstant($index);
        $arrayZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ARRAY_STRING_ELEMENT, $arrayZval->getPtrRegister(), $valueZval->getPtrRegister(), strlen($index), $constant->ptr());
        $arrayZval->savePtrRegister($arrayZvalPtr);
        return $arrayZvalPtr;
    }

    protected function writeAssignVarElementArrayVar(LLVMZval $arrayZval, LLVMZval $valueZval, LLVMZval $indexZval) {
        $this->writeDebugInfo("{$arrayZval}[(var) $indexZval] <= (var)");
        $arrayZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_ARRAY_ZVAL_ELEMENT, $arrayZval->getPtrRegister(), $valueZval->getPtrRegister(), $indexZval->getPtrRegister());
        $arrayZval->savePtrRegister($arrayZvalPtr);
        return $arrayZvalPtr;
    }

    protected function writeFetchIntegerElementArray(LLVMZval $arrayZval, $index) {
        $this->writeDebugInfo("fetch {$arrayZval}[(int) $index]");
        return $this->function->InternalModuleCall(InternalModule::ZVAL_FETCH_ARRAY_INTEGER_ELEMENT, $arrayZval->getPtrRegister(), $index);
    }

    protected function writeFetchStringElementArray(LLVMZval $arrayZval, $index) {
        $this->writeDebugInfo("fetch {$arrayZval}[(string) $index]");
        $constant = $this->function->writeConstant($index);
        return $this->function->InternalModuleCall(InternalModule::ZVAL_FETCH_ARRAY_STRING_ELEMENT, $arrayZval->getPtrRegister(), strlen($index), $constant->ptr());
    }

    protected function writeFetchVarElementArray(LLVMZval $arrayZval, LLVMZval $indexZval) {
        $this->writeDebugInfo("fetch {$arrayZval}[(var) $indexZval]");
        return $this->function->InternalModuleCall(InternalModule::ZVAL_FETCH_ARRAY_ZVAL_ELEMENT, $arrayZval->getPtrRegister(), $indexZval->getPtrRegister());
    }
}
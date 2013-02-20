<?php
namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;
use PHPPHP\LLVMEngine\Type\Base;

trait Convert {
    protected function convertString(LLVMZval $toZval,LLVMZval $fromZval){
        $fromZvalPtr= $fromZval->getPtrRegister();

        //force  convert cache
        $this->function->InternalModuleCall(InternalModule::ZVAL_STRING_VALUE, $fromZvalPtr,Base::null(),Base::null());
        $toZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_COPY,LLVMZval::getGCList(), $fromZvalPtr);
        $this->function->InternalModuleCall(InternalModule::ZVAL_CONVERT_STRING, $toZvalPtr);
        $toZval->savePtrRegister($toZvalPtr);
        return $toZvalPtr;
    }

    protected function convertInteger(LLVMZval $toZval,LLVMZval $fromZval){
        $fromZvalPtr= $fromZval->getPtrRegister();

        //force  convert cache
        $this->function->InternalModuleCall(InternalModule::ZVAL_INTEGER_VALUE, $fromZvalPtr);
        $toZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_COPY, LLVMZval::getGCList(), $fromZvalPtr);
        $this->function->InternalModuleCall(InternalModule::ZVAL_CONVERT_INTEGER, $toZvalPtr);
        $toZval->savePtrRegister($toZvalPtr);
        return $toZvalPtr;
    }

    protected function convertDouble(LLVMZval $toZval,LLVMZval $fromZval){
        $fromZvalPtr= $fromZval->getPtrRegister();

        //force  convert cache
        $this->function->InternalModuleCall(InternalModule::ZVAL_DOUBLE_VALUE, $fromZvalPtr);
        $toZvalPtr = $this->function->InternalModuleCall(InternalModule::ZVAL_COPY, LLVMZval::getGCList(), $fromZvalPtr);
        $this->function->InternalModuleCall(InternalModule::ZVAL_CONVERT_DOUBLE, $toZvalPtr);
        $toZval->savePtrRegister($toZvalPtr);
        return $toZvalPtr;
    }

}
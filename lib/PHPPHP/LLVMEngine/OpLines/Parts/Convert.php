<?php
namespace PHPPHP\LLVMEngine\OpLines\Parts;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

trait Convert {
    protected function convertString($toZval,$fromZval){
        $fromZvalPtr= $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$fromZvalPtr = load " . LLVMZval::zval('**') . " $fromZval, align " . LLVMZval::zval('*')->size());

        //force  convert cache
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_STRING_VALUE, $fromZvalPtr,'null','null'));
        $this->function->writeUsedFunction(InternalModule::ZVAL_STRING_VALUE);

        $toZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$toZvalPtr = " . InternalModule::call(InternalModule::ZVAL_COPY, '%zvallist', $fromZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_COPY);

        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_CONVERT_STRING, $toZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_CONVERT_STRING);

        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $toZvalPtr, " . LLVMZval::zval('**') . " $toZval, align " . LLVMZval::zval('*')->size());
        return $toZvalPtr;
    }

    protected function convertInteger($toZval,$fromZval){
        $fromZvalPtr= $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$fromZvalPtr = load " . LLVMZval::zval('**') . " $fromZval, align " . LLVMZval::zval('*')->size());

        //force  convert cache
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_INTEGER_VALUE, $fromZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_INTEGER_VALUE);

        $toZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$toZvalPtr = " . InternalModule::call(InternalModule::ZVAL_COPY, '%zvallist', $fromZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_COPY);

        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_CONVERT_INTEGER, $toZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_CONVERT_INTEGER);

        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $toZvalPtr, " . LLVMZval::zval('**') . " $toZval, align " . LLVMZval::zval('*')->size());
        return $toZvalPtr;
    }

    protected function convertDouble($toZval,$fromZval){
        $fromZvalPtr= $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$fromZvalPtr = load " . LLVMZval::zval('**') . " $fromZval, align " . LLVMZval::zval('*')->size());

        //force  convert cache
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_DOUBLE_VALUE, $fromZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_DOUBLE_VALUE);

        $toZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$toZvalPtr = " . InternalModule::call(InternalModule::ZVAL_COPY, '%zvallist', $fromZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_COPY);

        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_CONVERT_DOUBLE, $toZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_CONVERT_DOUBLE);

        $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $toZvalPtr, " . LLVMZval::zval('**') . " $toZval, align " . LLVMZval::zval('*')->size());
        return $toZvalPtr;
    }

}
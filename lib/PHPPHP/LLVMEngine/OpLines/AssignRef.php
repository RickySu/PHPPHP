<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignRef extends OpLine {

    public function write() {
        $op1VarName = $this->opCode->op1->getImmediateZval()->getName();
        $op2VarName = $this->opCode->op2->getImmediateZval()->getName();

        $op1Zval = $this->function->getZvalIR($op1VarName,false);
        $op2Zval = $this->function->getZvalIR($op2VarName);

        $this->writeDebugInfo("$op1Zval <= (var ref) $op2Zval");

        $op1ZvalPtr = $this->function->getRegisterSerial();
        $op2ZvalPtr = $this->function->getRegisterSerial();
        $cmpResult = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$op1ZvalPtr = load " . LLVMZval::zval('**') . " $op1Zval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR("$op2ZvalPtr = load " . LLVMZval::zval('**') . " $op2Zval, align " . LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR("$cmpResult = icmp ne ".LLVMZval::zval('*')." $op1ZvalPtr, null");
        $LabelTrue="{$this->function->getRegisterSerial()}_label_true";
        $LabelFalse="{$this->function->getRegisterSerial()}_label_false";
        $this->function->writeOpLineIR("br i1 $cmpResult, label $LabelTrue, label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelTrue,1).':');
        //$op1Zval need GC
        $this->function->writeOpLineIR(InternalModule::call(InternalModule::ZVAL_GC, '%zvallist', $op1ZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_GC);
        $this->function->writeOpLineIR("br label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelFalse,1).':');
        $newop1ZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$newop1ZvalPtr = ".InternalModule::call(InternalModule::ZVAL_ASSIGN_REF, '%zvallist', $op2ZvalPtr));
        $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_REF);
        $this->function->writeOpLineIR("store ".LLVMZval::zval('*')." $newop1ZvalPtr, ".LLVMZval::zval('**')." $op1Zval, align ".LLVMZval::zval('*')->size());
        $this->function->writeOpLineIR("store ".LLVMZval::zval('*')." $newop1ZvalPtr, ".LLVMZval::zval('**')." $op2Zval, align ".LLVMZval::zval('*')->size());
    }

}
<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class AssignRef extends OpLine {

    public function write() {
        parent::write();
        $op1VarName = $this->opCode->op1->getImmediateZval()->getName();
        $op2VarName = $this->opCode->op2->getImmediateZval()->getName();
        if($op1VarName==$op2VarName){   // $a=&$a;
            //do nothing
            return;
        }

        $op1Zval = $this->function->getZvalIR($op1VarName,false);
        $op2Zval = $this->function->getZvalIR($op2VarName);

        $this->writeDebugInfo("$op1Zval <= (var ref) $op2Zval");

        $cmpResult = $this->function->getRegisterSerial();
        $op1ZvalPtr=$op1Zval->getPtrRegister();

        $this->function->writeOpLineIR("$cmpResult = icmp ne ".LLVMZval::zval('*')." $op1ZvalPtr, null");
        $LabelTrue="{$this->function->getRegisterSerial()}_label_true";
        $LabelFalse="{$this->function->getRegisterSerial()}_label_false";
        $this->function->writeOpLineIR("br i1 $cmpResult, label $LabelTrue, label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelTrue,1).':');
        //$op1Zval need GC
        $this->function->InternalModuleCall(InternalModule::ZVAL_GC, LLVMZval::getGCList(), $op1ZvalPtr);
        $this->function->writeOpLineIR("br label $LabelFalse");

        $this->function->writeOpLineIR(substr($LabelFalse,1).':');
        $op1ZvalPtr=$this->function->InternalModuleCall(InternalModule::ZVAL_ASSIGN_REF, LLVMZval::getGCList(), $op2Zval->getPtrRegister());
        $op1Zval->savePtrRegister($op1ZvalPtr);
        $op2Zval->savePtrRegister($op1ZvalPtr);
   }

}
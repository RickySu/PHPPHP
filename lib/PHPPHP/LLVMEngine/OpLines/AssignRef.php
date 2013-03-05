<?php

namespace PHPPHP\LLVMEngine\OpLines;

class AssignRef extends OpLine {

    use Parts\VarAssign;

    public function write() {
        parent::write();
        $op1VarName = $this->opCode->op1->getImmediateZval()->getName();
        $op2VarName = $this->opCode->op2->getImmediateZval()->getName();
        if ($op1VarName == $op2VarName) {   // $a=&$a;
            if(isset($this->opCode->dim)){
                $LLVMOp=new AssignDimRef($this->opCode,$this->opLineNo);
                $LLVMOp->setFunction($this->function);
                $LLVMOp->write();
            }
            return;
        }
        $op1Zval = $this->function->getZvalIR($op1VarName, false);
        $op2Zval = $this->function->getZvalIR($op2VarName);
        $this->writeVarAssignRef($op1Zval, $op2Zval);
    }

}
<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;

class Assign extends OpLine {

    public function write() {
        parent::write();
        $op1VarName = $this->opCode->op1->getImmediateZval()->getName();
        $op2Zval = $this->opCode->op2->getImmediateZval();
        if ($op2Zval instanceof Zval\Value) {
            $this->writeImmediateValueAssign($op1VarName, $op2Zval->getValue());
        } else {
            $this->writeVarAssign($op1VarName, $op2Zval->getName());
        }
    }

    protected function writeImmediateValueAssign($varName, $value) {
        $valueType = gettype($value);
        $this->writeDebugInfo("$varName <= ($valueType)");
        switch($valueType){
            case 'integer':
                $this->writeIntegerAssign($varName, $value);
        }
    }

    protected function writeIntegerAssign($varName,$value){

    }

    protected function writeVarAssign($op1VarName, $op2VarName) {
        $this->writeDebugInfo("$op1VarName <= (var) $op2VarName");
    }

}
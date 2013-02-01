<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;

class Add extends OpLine {

    use Parts\VarAssign;

use Parts\TypeCast;

    protected $tmpZval = array();

    public function write() {
        parent::write();
        $resultZval = $this->prepareResultZval();
        $writeIntegerAdd = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister) use($resultZval) {
                    $this->writeIntegerAdd($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };
        $writeDoubleAdd = function($typeCastOp1ValueRegister, $typeCastOp2ValueRegister)use($resultZval) {
                    $this->writeDoubleAdd($resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister);
                };

        $this->prepareOpZval($op1Zval, $op2Zval);
        if($op1Zval instanceof LLVMZval && $op2Zval instanceof LLVMZval){
            $this->TypeCast($op1Zval, $op2Zval, $writeIntegerAdd, $writeDoubleAdd);
        }
        else{
            $this->writeImmediateValueAssign($resultZval, $op1Zval + $op2Zval);
        }
        $this->gcTempZval();
    }

    protected function prepareOpZval(&$op1Zval, &$op2Zval) {
        $op1Var = $this->opCode->op1->getImmediateZval();
        $op2Var = $this->opCode->op2->getImmediateZval();
        if ($op1Var instanceof Zval\Value && $op1Var instanceof Zval\Value) {
            $op1Zval=$op1Var->getValue();
            $op2Zval=$op2Var->getValue();
            return;
        }
        if ($op1Var instanceof Zval\Value) {
            $op1Zval = $this->makeTempZval($op1Var->getValue());
        } else {
            $op1Zval = $this->function->getZvalIR($op1Var->getName());
        }
        if ($op2Var instanceof Zval\Value) {
            $op2Zval = $this->makeTempZval($op2Var->getValue());
        } else {
            $op2Zval = $this->function->getZvalIR($op2Var->getName());
        }
    }

    protected function gcTempZval(){
        foreach($this->tmpZval as $Zval){
            $this->gcVarZval($Zval);
        }
    }

    /**
     *
     * @return LLVMZval
     */
    protected function makeTempZval($value) {
        $op1Zval=$this->function->getZvalIR(LLVMZval::ZVAL_TEMP_OP, true,true);
        $this->tmpZval[]=$op1Zval;
        $this->writeImmediateValueAssign($op1Zval, $value);
        return $op1Zval;
    }

    /**
     *
     * @return LLVMZval
     */
    protected function prepareResultZval() {
        if (!isset($this->opCode->result->TempVarName)) {
            $resultVarName = substr($this->function->getRegisterSerial(), 1);
            $this->opCode->result->getImmediateZval()->TempVarName = $resultVarName;
        }
        return $this->function->getZvalIR($resultVarName, true, true);
    }

    protected function writeIntegerAdd(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = add " . BaseType::long() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignInteger($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

    protected function writeDoubleAdd(LLVMZval $resultZval, $typeCastOp1ValueRegister, $typeCastOp2ValueRegister) {
        $resultZvalRegister = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fadd " . BaseType::double() . " $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignDouble($resultZval, $resultZvalRegister);
        return $resultZvalRegister;
    }

}

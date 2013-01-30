<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Concat extends OpLine {

    use Parts\VarAssign;

    public function write() {
        parent::write();
        $op1Var = $this->opCode->op1->getImmediateZval();
        $op2Var = $this->opCode->op2->getImmediateZval();

        if (!isset($this->opCode->result->TempVarName)) {
            $resultVarName = substr($this->function->getRegisterSerial(), 1);
            $this->opCode->result->getImmediateZval()->TempVarName = $resultVarName;
        }
        $resultZval = $this->function->getZvalIR($resultVarName, true, true);
        $resultZvalPtr = $this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalPtr = load " . LLVMZval::zval('**') . " $resultZval, align " . LLVMZval::zval('*')->size());
        $op1Value = $this->getValue($op1Var);
        $op2Value = $this->getValue($op2Var);
        if (is_string($op1Value) && is_string($op2Value)) {
            $str = $op1Value . $op2Value;
            $this->writeAssignString($resultZval, $resultZvalPtr, $op1Value . $op2Value);
        } elseif (is_string($op1Value) && ($op2Value === null)) {
            $resultZvalPtr = $this->writeAssignString($resultZval, $resultZvalPtr, $op1Value);
            $op2Zval = $this->function->getZvalIR($op2Var->getName());
            $op2ZvalPtr = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR("$op2ZvalPtr = load " . LLVMZval::zval('**') . " $op2Zval, align " . LLVMZval::zval('*')->size());
            $returnResultZvalPtr = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR("$returnResultZvalPtr = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, '%zvallist', $resultZvalPtr, $op2ZvalPtr));
            $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL);
            $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnResultZvalPtr, " . LLVMZval::zval('**') . " $resultZval, align " . LLVMZval::zval('*')->size());
        } elseif (($op1Value === null) && is_string($op2Value)) {
            $op1Zval = $this->function->getZvalIR($op1Var->getName());
            $resultZvalPtr = $this->writeVarAssign($resultZval, $op1Zval);

            $constant = $this->function->writeConstant($op2Value);

            $returnResultZvalPtr = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR("$returnResultZvalPtr = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_CONCAT_STRING, '%zvallist', $resultZvalPtr, strlen($op2Value), $constant->ptr()));
            $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_CONCAT_STRING);
            $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnResultZvalPtr, " . LLVMZval::zval('**') . " $resultZval, align " . LLVMZval::zval('*')->size());
        } elseif (($op1Value === null) && ($op2Value === null)) {
            $op1Zval = $this->function->getZvalIR($op1Var->getName());
            $resultZvalPtr = $this->writeVarAssign($resultZval, $op1Zval);
            $op2Zval = $this->function->getZvalIR($op2Var->getName());
            $op2ZvalPtr = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR("$op2ZvalPtr = load " . LLVMZval::zval('**') . " $op2Zval, align " . LLVMZval::zval('*')->size());
            $returnResultZvalPtr = $this->function->getRegisterSerial();
            $this->function->writeOpLineIR("$returnResultZvalPtr = " . InternalModule::call(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL, '%zvallist', $resultZvalPtr, $op2ZvalPtr));
            $this->function->writeUsedFunction(InternalModule::ZVAL_ASSIGN_CONCAT_ZVAL);
            $this->function->writeOpLineIR("store " . LLVMZval::zval('*') . " $returnResultZvalPtr, " . LLVMZval::zval('**') . " $resultZval, align " . LLVMZval::zval('*')->size());
        }
    }

    protected function getValue($opVar) {
        if ($opVar instanceof Zval\Value) {
            $value = (string) $opVar->getValue();
            return $value;
        }
        return null;
    }

}
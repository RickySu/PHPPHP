<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\Engine\Zval;
use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\TypeCast as LLVMTypeCast;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Add extends OpLine {

    use Parts\VarAssign;

    public function write() {
        parent::write();
        $op1Var = $this->opCode->op1->getImmediateZval();
        $op2Var = $this->opCode->op2->getImmediateZval();
        $op1Zval = $this->function->getZvalIR($op1Var->getName());
        $op2Zval = $this->function->getZvalIR($op2Var->getName());
        $this->TypeCast($typeCastOp1ValueRegister, $typeCastOp2ValueRegister, $op1Zval, $op2Zval);
        if (!isset($this->opCode->result->TempVarName)) {
            $resultVarName = substr($this->function->getRegisterSerial(), 1);
            $this->opCode->result->getImmediateZval()->TempVarName = $resultVarName;
        }
        $resultZval = $this->function->getZvalIR($resultVarName, true, true);
        $resultZvalRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$resultZvalRegister = fadd ".BaseType::double()." $typeCastOp1ValueRegister, $typeCastOp2ValueRegister");
        $this->writeAssignDouble($resultZval, $resultZvalRegister);

    }

    protected function TypeCast(&$typeCastOp1ValueRegister, &$typeCastOp2ValueRegister, LLVMZval $op1Zval = NULL, LLVMZval $op2Zval = NULL) {
        $typeCastOp1 = new LLVMTypeCast($this->function->getInternalVar(LLVMTypeCast::TYPE_CAST_OP1, LLVMTypeCast::typeCast()), $this->function);
        $typeCastOp2 = new LLVMTypeCast($this->function->getInternalVar(LLVMTypeCast::TYPE_CAST_OP2, LLVMTypeCast::typeCast()), $this->function);
        $castResultType = $this->function->InternalModuleCall(InternalModule::ZVAL_TYPE_CAST, $op1Zval->getPtrRegister(), $op2Zval->getPtrRegister(), $typeCastOp1, $typeCastOp2);
        $typeCastOp1Value=$this->function->getRegisterSerial();
        $typeCastOp2Value=$this->function->getRegisterSerial();
        $typeCastOp1ValuePtr=$this->function->getRegisterSerial();
        $typeCastOp2ValuePtr=$this->function->getRegisterSerial();
        $typeCastOp1ValueRegister=$this->function->getRegisterSerial();
        $typeCastOp2ValueRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR(LLVMTypeCast::typeCast()->getStructIR()->getElementPtrIR($typeCastOp1ValuePtr,$typeCastOp1,'dval'));
        $this->function->writeOpLineIR("$typeCastOp1Value = load ".LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval')."* $typeCastOp1ValuePtr");
        $this->function->writeOpLineIR(LLVMTypeCast::typeCast()->getStructIR()->getElementPtrIR($typeCastOp2ValuePtr,$typeCastOp2,'dval'));
        $this->function->writeOpLineIR("$typeCastOp2Value = load ".LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval')."* $typeCastOp2ValuePtr");
        $this->function->writeOpLineIR("$typeCastOp1ValueRegister = bitcast ".LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval')." $typeCastOp1Value to ".BaseType::double());
        $this->function->writeOpLineIR("$typeCastOp2ValueRegister = bitcast ".LLVMTypeCast::typeCast()->getStructIR()->getElementEffectiveType('dval')." $typeCastOp2Value to ".BaseType::double());
    }

}

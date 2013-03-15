<?php

namespace PHPPHP\LLVMEngine\OpLines;

use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Zval as LLVMZval;

class Recv extends OpLine {

    use Parts\TypeCast,
        Parts\PrepareOpZval;

    public function write() {
        parent::write();
        $index = $this->opCode->op1->getValue();
        $params = $this->function->getParams();
        if ($index == 0) {
            $this->prepareParamPre();
        }
        $this->prepareParam($index, $params[$index]);
        if ($index == count($params) - 1) {
            $this->prepareParamPost();
        }
        $this->gcTempZval();
    }

    protected function prepareParamPre() {
        $valistType="[32 x ".BaseType::char()."]";
        $this->function->writeUsedFunction('@llvm.va_start', array('', BaseType::void(), array(BaseType::void('*'))));
        $this->function->writeOpLineIR("%va_list = alloca $valistType, align 8");
        $this->function->writeOpLineIR("%va_list_ptr = getelementptr inbounds $valistType* %va_list, ".BaseType::int()." 0, ".BaseType::int()." 0");
        $this->function->writeOpLineIR("%va_list_addr = bitcast $valistType* %va_list to ".BaseType::void('**'));
        $this->function->writeOpLineIR("call void @llvm.va_start(".BaseType::void('*')." %va_list_ptr)");
    }

    protected function prepareParamPost() {
        $this->function->writeUsedFunction('@llvm.va_end', array('', BaseType::void(), array(BaseType::void('*'))));
        $this->function->writeOpLineIR("call void @llvm.va_end(".BaseType::void('*')." %va_list_ptr)");
    }

    protected function prepareParamInit(LLVMZval $paramZval) {
    }

    public function prepareParam($index, $param) {
        $paramZval = $this->function->getZvalIR($param->name);
        $ifSerial = substr($this->function->getRegisterSerial(), 1);
        $LabelIfTrue = "Label_IfTrue_$ifSerial";
        $LabelIfElse = "Label_IfElse_$ifSerial";
        $LabelEndIf = "Label_EndIf_$ifSerial";
        $ifLessCmpResult = "%$ifSerial";
        $this->function->writeOpLineIR("$ifLessCmpResult = icmp slt " . BaseType::int() . " %nArgs, ".($index+1));
        $this->function->writeOpLineIR("br i1 $ifLessCmpResult, label %$LabelIfTrue, label %$LabelIfElse");
        $this->function->writeOpLineIR("$LabelIfTrue:");

        $this->prepareParamInit($paramZval);

        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelIfElse:");

        $paramRegister=$this->function->getRegisterSerial();
        $this->function->writeOpLineIR("$paramRegister = va_arg i8** %va_list_addr, ".LLVMZval::zval('*'));
        $srcZval = new LLVMZval(NULL, false, true, $this->function);
        $srcZval->savePtrRegister($paramRegister);
        if($param->isRef){
            $this->writeVarAssignRef($paramZval, $srcZval);
        }
        else{
            $this->writeVarAssign($paramZval, $srcZval);
        }
        $this->function->writeOpLineIR("br label %$LabelEndIf");
        $this->function->writeOpLineIR("$LabelEndIf:");
    }

}

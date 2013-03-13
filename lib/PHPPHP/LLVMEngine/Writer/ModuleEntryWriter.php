<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\Zval as LLVMZval;
use PHPPHP\LLVMEngine\Type\Base as BaseType;
use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class ModuleEntryWriter extends FunctionWriter {

    public function getEntryName() {
        return "PHPLLVM_{$this->moduleWriter->getModuleName()}_entry";
    }

    protected function functionCtorIR() {
        $registerFunctionsIR = $this->writeRegisterFunctions();
        $IR = parent::functionCtorIR();
        if($this->moduleWriter->getJumpTableInitializerEntryName()){
            $IR[]="call void {$this->moduleWriter->getJumpTableInitializerEntryName()}()";
        }
        return array_merge($registerFunctionsIR, $IR);
    }

    protected function writeRegisterFunctions() {
        $IR[] = '';
        $IR[] = ";regist function";
        foreach ($this->moduleWriter->getFunctions() as $function) {
            $functionParamTypeDefine = array(BaseType::int());
            if($function->getParamsTypeDefine()){
                $functionParamTypeDefine[]=$function->getParamsTypeDefine();
            }
            $functionParamTypeDefine=implode(", ",$functionParamTypeDefine);
            $functionNameConstant = $this->writeConstant($function->getFunctionName());
            $functionPtrRegister = $this->getRegisterSerial();
            $IR[] = "$functionPtrRegister = bitcast " . LLVMZval::zval('*') . " ($functionParamTypeDefine)* @{$function->getEntryName()} to " . BaseType::void('*');
            $IR[] = $this->getInternalModuleCallIR(InternalModule::PHPLLVM_FUNCTION_REGISTER, strlen($function->getFunctionName()), $functionNameConstant->ptr(), $functionPtrRegister);
        }
        return $IR;
    }

}

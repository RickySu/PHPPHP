<?php
namespace PHPPHP\LLVMEngine\Writer;

class FunctionWriter extends Module {

    protected function generatEentryName($functionName){
        return "PHPLLVM_function_".md5($functionName);
    }

}
<?php
namespace PHPPHP\LLVMEngine\OpLines;
use PHPPHP\LLVMEngine\Zval;

class ReturnOp extends OpLine{

    public function write() {
        parent::write();
        if($this->opCode->op1==null){
            $this->writeReturnNull();
        }
        else{

        }
    }
    protected function writeReturnNull(){
        //'ret '.Zval::PtrIRDeclare()." null"
        $IR="br label %end_return";
        $this->module->writeOpLineIR($IR);
    }
    protected function writeRetuenWithValue(){

    }
}
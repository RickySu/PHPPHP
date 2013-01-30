<?php
namespace PHPPHP\LLVMEngine\OpLines;
use PHPPHP\LLVMEngine\Zval;

class ReturnOp extends OpLine{

    public function write() {
        if($this->opCode->op1==null){
            $this->writeReturnNull();
        }
        else{

        }
    }
    protected function writeReturnNull(){
        //'ret '.Zval::PtrIRDeclare()." null"
        $IR="br label %end_return";
        $this->function->writeOpLineIR($IR);
    }
    protected function writeRetuenWithValue(){

    }
}
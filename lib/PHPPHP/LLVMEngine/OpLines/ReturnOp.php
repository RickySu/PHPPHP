<?php
namespace PHPPHP\LLVMEngine\OpLines;

class ReturnOp extends OpLine
{
    public function write()
    {
        parent::write();
        if ($this->opCode->op1==null) {
            $this->writeReturnNull();
        } else {

        }
    }
    protected function writeReturnNull()
    {
        $IR="br label %end_return";
        $this->function->writeOpLineIR($IR);
    }
    protected function writeRetuenWithValue()
    {
    }
}

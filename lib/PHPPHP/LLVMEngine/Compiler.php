<?php

namespace PHPPHP\LLVMEngine;

use PHPPHP\LLVMEngine\OpLines;
use PHPPHP\Engine\OpArray;
use PHPPHP\Engine\Zval;

dl('llvm_bind.so');

class Compiler
{
    /**
     *
     * @var Writer
     */
    protected $writer;
    protected $context;
    protected $llvmBind;

    public function __construct()
    {
        $this->writer = new Writer();
        $this->llvmBind = new \LLVMBind();
    }

    public function compile($compiledData, $context)
    {
        $opArray = $compiledData['opcode'];
        //print_r($opArray);die;
        $module = new Writer\ModuleWriter($context);
        $this->writer->addModuleWriter($module);
        $this->compileOpLine($module, $opArray);
        $IR = $this->writer->write();
        echo $IR;
        $this->writer->clear();
        $bitcode = Internal\Module::getBitcode();
        $this->llvmBind->loadBitcode($bitcode);
        $bitcode = $this->llvmBind->compileAssembly($IR, 3);
        echo $this->llvmBind->getLastError();
        $this->llvmBind->loadBitcode($bitcode);
        $this->llvmBind->execute('jit_init');
        $this->llvmBind->execute($module->getEntryName());
        $this->llvmBind->execute('jit_shutdown');
        //echo $module->getEntryName();
        die;
        echo $bitcode;
    }

    protected function compileOpLine(Writer\ModuleWriter $module, OpArray $opArray)
    {
        $function = $module->getEntryFunction();
        $opResult=NULL;
        foreach ($opArray as $opLineNo => $opCode) {
            if ($opResult) {
                $unUsedOpResult=true;
                if (($opCode->op1 instanceof Zval\Ptr) && ($opCode->op1->getImmediateZval()===$opResult->getImmediateZval())) {
                    $unUsedOpResult=false;
                }
                if (($opCode->op2 instanceof Zval\Ptr) && ($opCode->op2->getImmediateZval()===$opResult->getImmediateZval())) {
                    $unUsedOpResult=false;
                }
                if (($opCode->result instanceof Zval\Ptr) && ($opCode->result->getImmediateZval()===$opResult->getImmediateZval())) {
                    $unUsedOpResult=false;
                }
                $opResult->markUnUsed=$unUsedOpResult;
            }
            $opResult=$opCode->result;
            $className = explode('\\', get_class($opCode));
            $className = $className[count($className) - 1];
            $opLineClassName = '\\PHPPHP\\LLVMEngine\\OpLines\\' . $className;
            $opLine = new $opLineClassName($opCode, $opLineNo);
            $function->addOpLine($opLine);
        }
    }

}

<?php

namespace PHPPHP\LLVMEngine;

use PHPPHP\LLVMEngine\OpLines;
use PHPPHP\Engine\OpArray;
use PHPPHP\Engine\Zval;
use PHPPHP\Engine\FunctionData;

dl('llvm_bind.so');

class Compiler {

    /**
     *
     * @var Writer
     */
    protected $writer;
    protected $context;
    protected $llvmBind;

    public function __construct() {
        $this->writer = new Writer();
        $this->llvmBind = new \LLVMBind();
    }

    public function compile($compiledData, $context) {
        $opArray = $compiledData['opcode'];
        //print_r($opArray);die;
        $module = new Writer\ModuleWriter($context);
        $this->writer->addModuleWriter($module);

        foreach ($compiledData['functionData'] as $functionData) {
            $this->compileFunction($module, $functionData);
        }

        $this->compileOpLine($module, $module->getEntryFunction(), $opArray);
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

    public function compileFunction(Writer\ModuleWriter $module, FunctionData\User $userFunctionData) {
        $functionWriter = $module->addFunction($userFunctionData->getName(), $userFunctionData->getParams());
        //print_r($userFunctionData->getOpArray());die;
        $this->compileOpLine($module, $functionWriter, $userFunctionData->getOpArray());
    }

    protected function compileOpLine(Writer\ModuleWriter $module, Writer\FunctionWriter $function, OpArray $opArray) {
        $opResult = NULL;
        foreach ($opArray as $opLineNo => $opCode) {
            $opResult = $opCode->result;
            if ($opResult instanceof Zval\Ptr) {
                $opZval = $opResult->getImmediateZval();
                if (!isset($opZval->usedCount)) {
                    $opZval->usedCount=0;
                }
            }
            if ($opCode->op1 instanceof Zval\Ptr) {
                $opZval = $opCode->op1->getImmediateZval();
                if (isset($opZval->usedCount)) {
                    $opZval->usedCount++;
                }
            }
            if ($opCode->op2 instanceof Zval\Ptr) {
                $opZval = $opCode->op2->getImmediateZval();
                if (isset($opZval->usedCount)) {
                    $opZval->usedCount++;
                }
            }
        }
        foreach ($opArray as $opLineNo => $opCode) {
            $opResult = $opCode->result;
            if ($opResult && $opResult instanceof Zval\Ptr) {
                $opResult->markUnUsed = false;
                $opZval = $opResult->getImmediateZval();
                if (isset($opZval->usedCount) && ($opZval->usedCount == 0)) {
                    $opResult->markUnUsed = true;
                }
            }
            if (isset($opArray[$opLineNo + 1])) {
                $opCode->nextOpCode = $opArray[$opLineNo + 1];
            }
            $className = explode('\\', get_class($opCode));
            $className = $className[count($className) - 1];
            $opLineClassName = '\\PHPPHP\\LLVMEngine\\OpLines\\' . $className;
            $opLine = new $opLineClassName($opCode, $opLineNo);
            $function->addOpLine($opLine);
        }
    }

}

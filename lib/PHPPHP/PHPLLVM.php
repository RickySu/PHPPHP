<?php

namespace PHPPHP;

use PHPPHP\Engine;

dl('llvm_bind.so');

class PHPLLVM {

    protected $executor;

    /**
     *
     * @var Engine\Compiler
     */
    protected $opcodeCompiler;

    /**
     *
     * @var LLVMEngine\Compiler
     */
    protected $bitcodeCompiler;

    /**
     *
     * @var Engine\Parser
     */
    protected $parser;

    protected $llvmBind;

    public function __construct() {
        $functionStore = new Engine\FunctionStore;
        $constantStore = new Engine\ConstantStore;
        $classeStore = new Engine\ClassStore;
        $this->llvmBind = new \LLVMBind();
        $this->parser = new Engine\Parser();
        $this->opcodeCompiler = new Engine\Compiler($functionStore);
        $this->bitcodeCompiler = new LLVMEngine\Compiler($this->llvmBind);
        $this->executor = new LLVMEngine\Executor($this->llvmBind);
    }

    protected function parseCode($code,$context){
        try {
            return $this->parser->parse($code);
        } catch (Engine\PHPParser_Error $e) {
            $message = 'syntax error, ' . str_replace('Unexpected', 'unexpected', $e->getMessage());
            $line = $e->getRawLine();
            $this->errorHandler->handle($this, E_PARSE, $message, $context, $line);
            throw new Engine\ErrorOccurredException($message, E_PARSE);
        }
    }

    /**
     *
     * @param string $code
     * @param string $context
     * @return Engine\OpArray
     */
    public function compile($code,$context){
        $ast = $this->parseCode($code, $context);
        $this->opcodeCompiler->setFileName($context,'/');
        $opcode=$this->opcodeCompiler->compile($ast);
        $functionStore=$this->opcodeCompiler->getFunctionStore();
        $UserFunctions=$functionStore->getUserFunctions();
        return array(
            'opcode'=>$opcode,
            'functionData' => $UserFunctions,
        );
    }

    public function setCWD($dir) {
    }

    public function execute($code,$context) {
        $compiledData=$this->compile($code, $context);
        list($bitcode,$entryName)=$this->bitcodeCompiler->compile($compiledData,$context);
        $this->executor->execute($bitcode, $entryName);
    }

    public function executeFile($file) {

        if (empty($file)) {
            throw new \RuntimeException('Filename must not be empty');
        }

        if (!file_exists($file)) {
            throw new \RuntimeException("File not found $file");
        }

        $this->setCWD(dirname($file));

        $this->execute(file_get_contents($file),$file);

    }

}

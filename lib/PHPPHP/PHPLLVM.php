<?php

namespace PHPPHP;

use PHPPHP\Engine;

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

    public function __construct() {
        $functionStore = new Engine\FunctionStore;
        $constantStore = new Engine\ConstantStore;
        $classeStore = new Engine\ClassStore;
        $this->opcodeCompiler = new Engine\Compiler($functionStore);
        $this->bitcodeCompiler = new LLVMEngine\Compiler();
        $this->parser = new Engine\Parser();
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
        return $this->opcodeCompiler->compile($ast);
    }

    public function setCWD($dir) {
    }

    public function execute($code,$context) {
        $opcode=$this->compile($code, $context);
        $bitcode=$this->bitcodeCompiler->compile($opcode,$context);
       print_r($bitcode);
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

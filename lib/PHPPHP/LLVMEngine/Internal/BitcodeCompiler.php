<?php

namespace PHPPHP\LLVMEngine\Internal;

class BitcodeCompiler {

    protected $files=array();
    protected $sourcePath;
    protected $outputPath;
    protected $sourceFiles;

    public function __construct($sourceFiles) {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'c';
        $this->outputPath = __DIR__ . DIRECTORY_SEPARATOR . 'bitcode';
        $this->sourceFiles = $sourceFiles;
    }

    public function __destruct() {
        foreach ($this->files as $file) {
            @unlink($file);
            @unlink("$file.s");
            @unlink("$file.bc");
        }
    }

    public function compileAll() {
        $outputHash = md5(implode(',', $this->sourceFiles));
        $outputFile = $this->outputPath . DIRECTORY_SEPARATOR . $outputHash . '.bc';
        if (!$this->isNeedRebuild($outputFile)) {
            return file_get_contents($outputFile);
        }
        foreach ($this->sourceFiles as $file) {
            $this->compile($file);
        }
        $this->link($outputFile);
        return file_get_contents($outputFile);
    }

    protected function isNeedRebuild($outputFile) {
        if (!file_exists($outputFile)) {
            return true;
        }
        $outputMTime = filemtime($outputFile);
        foreach ($this->sourceFiles as $file) {
            if ($outputMTime < filemtime($this->sourcePath . DIRECTORY_SEPARATOR . $file)) {
                return true;
            }
        }
        return false;
    }

    protected function link($output) {
        $linkfiles = '';
        foreach ($this->files as $file) {
            $linkfiles.="$file.bc ";
        }
        system("llvm-link-3.0 $linkfiles -o $output");
    }

    protected function compile($file) {
        $this->files[$file] = $tmpfile = $this->setupOutput();
        system("clang -emit-llvm -O4 -c " . $this->sourcePath . DIRECTORY_SEPARATOR . $file . " -S -o $tmpfile.s");
        $this->convertFastCC("$tmpfile.s");
        system("llvm-as-3.0 $tmpfile.s -o $tmpfile.bc");
    }

    protected function convertFastCC($file) {
        $IR = file_get_contents($file);
        $IR = str_replace('x86_fastcallcc', 'fastcc', $IR);
        file_put_contents($file, $IR);
    }

    protected function setupOutput() {
        $tmpOutputPath = sys_get_temp_dir();
        return tempnam($tmpOutputPath, 'phpllvm_tmp_');
    }

}
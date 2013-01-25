<?php

namespace PHPPHP\LLVMEngine\Internal;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Zval;

final class Module {

    const T_ECHO = 'PHPLLVM_T_ECHO';
    const ZVAL_LIST_GC = 'ZVAL_LIST_GC';
    const ZVAL_INIT ='ZVAL_INIT';

    public static function Define() {
        return array(
            self::T_ECHO => array(Base::void(), array(Base::int(), Base::char('*'))),
            self::ZVAL_LIST_GC => array(Base::void(), array(Base::void('*'))),
            self::ZVAL_INIT => array('%struct.zval*',array()),
        );
    }

    public static function returnType($moduleName){
        $define=self::Define();
        return $define[$moduleName][0];
    }

    public static function getBitcode() {
        $bitcodeCompiler = new BitcodeCompiler(array(
            self::T_ECHO . '.c',
            self::ZVAL_LIST_GC.'.c',
            self::ZVAL_INIT.'.c',
            ));
        return $bitcodeCompiler->compileAll();
    }

    public static function call() {
        $args = func_get_args();
        $moduleName = array_shift($args);
        $define = self::Define();
        if (!isset($define[$moduleName])) {
            return '';
        }
        $argIR = '';
        list($return, $argTypes) = $define[$moduleName];
        foreach ($args as $index => $arg) {
            $argIR.=", $argTypes[$index] $arg";
        }
        if (isset($argIR[0]) && $argIR[0] == ',') {
            $argIR = substr($argIR, 1);
        }
        $argIR = trim($argIR);
        return "call ".($argIR==''?'':'fastcc')." $return @$moduleName($argIR)";
    }

}

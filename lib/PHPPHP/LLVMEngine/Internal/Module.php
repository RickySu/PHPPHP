<?php

namespace PHPPHP\LLVMEngine\Internal;

use PHPPHP\LLVMEngine\Type\Base;

final class Module {

    const T_ECHO = 'PHPLLVM_T_ECHO';
    const VAR_LIST_GC = 'VAR_LIST_GC';

    public static function Define() {
        return array(
            self::T_ECHO => array(Base::void(), array(Base::int(), Base::char('*'))),
            self::VAR_LIST_GC => array(Base::void(), array(Base::void('*'))),
        );
    }

    public static function getBitcode() {
        $bitcodeCompiler = new BitcodeCompiler(array(
            self::T_ECHO . '.c',
            self::VAR_LIST_GC.'.c',
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
        foreach ($args as $index => $arg) {
            list($return, $argTypes) = $define[$moduleName];
            $argIR.=", $argTypes[$index] $arg";
        }
        if ($argIR[0] == ',') {
            $argIR = substr($argIR, 1);
        }
        $argIR = trim($argIR);
        return "call fastcc $return @$moduleName($argIR)";
    }

}

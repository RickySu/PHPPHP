<?php

namespace PHPPHP\LLVMEngine\Internal;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Zval;

final class Module {

    const T_ECHO = 'PHPLLVM_T_ECHO';
    const T_ECHO_ZVAL = 'PHPLLVM_T_ECHO_ZVAL';
    const ZVAL_LIST_INIT='ZVAL_LIST_INIT';
    const ZVAL_LIST_GC = 'ZVAL_LIST_GC';
    const ZVAL_INIT ='ZVAL_INIT';
    const ZVAL_GC ='ZVAL_GC';
    const ZVAL_GC_REGIST ='ZVAL_GC_REGIST';
    const ZVAL_ASSIGN_INTEGER='ZVAL_ASSIGN_INTEGER';
    const ZVAL_ASSIGN_DOUBLE='ZVAL_ASSIGN_DOUBLE';
    const ZVAL_ASSIGN_STRING='ZVAL_ASSIGN_STRING';
    const ZVAL_ASSIGN_CONCAT_STRING='ZVAL_ASSIGN_CONCAT_STRING';
    const ZVAL_ASSIGN_CONCAT_ZVAL='ZVAL_ASSIGN_CONCAT_ZVAL';
    const ZVAL_ASSIGN_REF='ZVAL_ASSIGN_REF';

    public static function Define() {
        return array(
            self::T_ECHO => array(Base::void(), array(Base::int(), Base::char('*'))),
            self::T_ECHO_ZVAL => array(Base::void(),array(Zval::zval('*'))),
            self::ZVAL_LIST_INIT => array(Base::void('*'), array()),
            self::ZVAL_LIST_GC => array(Base::void(), array(Base::void('*'))),
            self::ZVAL_INIT => array(Zval::zval('*'),array(Base::void('*'))),
            self::ZVAL_GC => array(Base::void(),array(Base::void('*'),Zval::zval('*'))),
            self::ZVAL_GC_REGIST => array(Base::void(),array(Base::void('*'),Zval::zval('*'))),
            self::ZVAL_ASSIGN_INTEGER => array(Zval::zval('*'),array(Base::void('*'),Zval::zval('*'),Base::int())),
            self::ZVAL_ASSIGN_DOUBLE => array(Zval::zval('*'),array(Base::void('*'),Zval::zval('*'),Base::double())),
            self::ZVAL_ASSIGN_STRING => array(Zval::zval('*'),array(Base::void('*'),Zval::zval('*'),Base::int(),Base::char('*'))),
            self::ZVAL_ASSIGN_CONCAT_STRING => array(Zval::zval('*'),array(Base::void('*'),Zval::zval('*'),Base::int(),Base::char('*'))),
            self::ZVAL_ASSIGN_CONCAT_ZVAL => array(Zval::zval('*'),array(Base::void('*'),Zval::zval('*'),Zval::zval('*'))),
            self::ZVAL_ASSIGN_REF => array(Zval::zval('*'),array(Base::void('*'),Zval::zval('*'))),
        );
    }

    public static function returnType($moduleName){
        $define=self::Define();
        return $define[$moduleName][0];
    }

    public static function getBitcode() {
        $bitcodeCompiler = new BitcodeCompiler(array(
            self::T_ECHO . '.c',
            'ZVAL_LIST.c',
            'ZVAL.c',
            'dtoa.c',
            'cvt.c',
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

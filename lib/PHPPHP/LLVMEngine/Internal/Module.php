<?php

namespace PHPPHP\LLVMEngine\Internal;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Zval;
use PHPPHP\LLVMEngine\TypeCast;

final class Module {

    const T_ECHO = 'PHPLLVM_T_ECHO';
    const T_ECHO_ZVAL = 'PHPLLVM_T_ECHO_ZVAL';
    const ZVAL_LIST_INIT = 'ZVAL_LIST_INIT';
    const ZVAL_LIST_GC = 'ZVAL_LIST_GC';
    const ZVAL_INIT = 'ZVAL_INIT';
    const ZVAL_INIT_ARRAY = 'ZVAL_INIT_ARRAY';
    const ZVAL_GC = 'ZVAL_GC';
    const ZVAL_GC_REGISTER = 'ZVAL_GC_REGISTER';
    const ZVAL_COPY_ON_WRITE = 'ZVAL_COPY_ON_WRITE';
    const ZVAL_COPY = 'ZVAL_COPY';
    const ZVAL_ASSIGN_BOOLEAN = 'ZVAL_ASSIGN_BOOLEAN';
    const ZVAL_ASSIGN_INTEGER = 'ZVAL_ASSIGN_INTEGER';
    const ZVAL_ASSIGN_DOUBLE = 'ZVAL_ASSIGN_DOUBLE';
    const ZVAL_ASSIGN_STRING = 'ZVAL_ASSIGN_STRING';
    const ZVAL_ASSIGN_ZVAL = 'ZVAL_ASSIGN_ZVAL';
    const ZVAL_ASSIGN_CONCAT_STRING = 'ZVAL_ASSIGN_CONCAT_STRING';
    const ZVAL_ASSIGN_CONCAT_ZVAL = 'ZVAL_ASSIGN_CONCAT_ZVAL';
    const ZVAL_ASSIGN_REF = 'ZVAL_ASSIGN_REF';
    const ZVAL_STRING_VALUE = 'ZVAL_STRING_VALUE';
    const ZVAL_CONVERT_STRING = 'ZVAL_CONVERT_STRING';
    const ZVAL_INTEGER_VALUE = 'ZVAL_INTEGER_VALUE';
    const ZVAL_CONVERT_INTEGER = 'ZVAL_CONVERT_INTEGER';
    const ZVAL_DOUBLE_VALUE = 'ZVAL_DOUBLE_VALUE';
    const ZVAL_CONVERT_DOUBLE = 'ZVAL_CONVERT_DOUBLE';
    const ZVAL_TYPE_CAST_NUMBER = 'ZVAL_TYPE_CAST_NUMBER';
    const ZVAL_TYPE_CAST_NUMBER_SINGLE = 'ZVAL_TYPE_CAST_NUMBER_SINGLE';
    const ZVAL_TYPE_CAST_SINGLE = 'ZVAL_TYPE_CAST_SINGLE';
    const ZVAL_TYPE_GUESS = 'ZVAL_TYPE_GUESS';
    const ZVAL_TYPE_GUESS_NUMBER = 'ZVAL_TYPE_GUESS_NUMBER';
    const ZVAL_EQUAL_STRING = 'ZVAL_EQUAL_STRING';
    const ZVAL_EQUAL = 'ZVAL_EQUAL';
    const ZVAL_EQUAL_EXACT = 'ZVAL_EQUAL_EXACT';
    const ZVAL_TEST_NULL = 'ZVAL_TEST_NULL';
    const ZVAL_TEST_FALSE = 'ZVAL_TEST_FALSE';

    public static function Define() {
        return array(
            self::T_ECHO => array(Base::void(), array(Base::int(), Base::char('*'))),
            self::T_ECHO_ZVAL => array(Base::void(), array(Zval::zval('*'))),
            self::ZVAL_INIT_ARRAY => array(Base::void(), array(Zval::zval('*'))),
            self::ZVAL_LIST_INIT => array(Base::void('*'), array()),
            self::ZVAL_LIST_GC => array(Base::void(), array(Base::void('*'))),
            self::ZVAL_INIT => array(Zval::zval('*'), array(Base::void('*'))),
            self::ZVAL_GC => array(Base::void(), array(Base::void('*'), Zval::zval('*'))),
            self::ZVAL_GC_REGISTER => array(Base::void(), array(Base::void('*'), Zval::zval('*'))),
            self::ZVAL_COPY_ON_WRITE => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'))),
            self::ZVAL_COPY => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'))),
            self::ZVAL_ASSIGN_INTEGER => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'), Base::long())),
            self::ZVAL_ASSIGN_BOOLEAN => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'), Base::long())),
            self::ZVAL_ASSIGN_DOUBLE => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'), Base::double())),
            self::ZVAL_ASSIGN_STRING => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'), Base::int(), Base::char('*'))),
            self::ZVAL_ASSIGN_ZVAL => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_ASSIGN_CONCAT_STRING => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'), Base::int(), Base::char('*'))),
            self::ZVAL_ASSIGN_CONCAT_ZVAL => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_ASSIGN_REF => array(Zval::zval('*'), array(Base::void('*'), Zval::zval('*'))),
            self::ZVAL_STRING_VALUE => array(Base::void(), array(Zval::zval('*'), Base::int('*'), Base::char('**'))),
            self::ZVAL_CONVERT_STRING => array(Base::void(), array(Zval::zval('*'))),
            self::ZVAL_INTEGER_VALUE => array(Base::int(), array(Zval::zval('*'))),
            self::ZVAL_CONVERT_INTEGER => array(Base::void(), array(Zval::zval('*'))),
            self::ZVAL_DOUBLE_VALUE => array(Base::double(), array(Zval::zval('*'))),
            self::ZVAL_CONVERT_DOUBLE => array(Base::void(), array(Zval::zval('*'))),
            self::ZVAL_TYPE_CAST_NUMBER => array(Base::int(), array(Zval::zval('*'), Zval::zval('*'), TypeCast::typeCast('*'), TypeCast::typeCast('*'))),
            self::ZVAL_TYPE_CAST_NUMBER_SINGLE => array(Base::int(), array(Zval::zval('*'), TypeCast::typeCast('*'))),
            self::ZVAL_TYPE_CAST_SINGLE => array(Base::int(), array(Zval::zval('*'), TypeCast::typeCast('*'))),
            self::ZVAL_TYPE_GUESS => array(Base::int(), array(Zval::zval('*'))),
            self::ZVAL_TYPE_GUESS_NUMBER => array(Base::int(), array(Zval::zval('*'))),
            self::ZVAL_EQUAL_STRING => array(Base::long(), array(Zval::zval('*'), Base::int(), Base::char('*'))),
            self::ZVAL_EQUAL => array(Base::long(), array(Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_EQUAL_EXACT => array(Base::long(), array(Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_TEST_NULL => array(Base::long(), array(Zval::zval('*'))),
            self::ZVAL_TEST_FALSE => array(Base::long(), array(Zval::zval('*'))),
            'single_debug' => array(Base::void(), array(Base::int())),
        );
    }

    public static function returnType($moduleName) {
        $define = self::Define();
        return $define[$moduleName][0];
    }

    public static function getBitcode() {
        $bitcodeCompiler = new BitcodeCompiler(array(
                    self::T_ECHO . '.c',
                    'ZVAL_LIST.c',
                    'ZVAL.c',
                    'dtoa.c',
                    'cvt.c',
                    'hashtable.c',
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
        return "call " . ($argIR == '' ? '' : 'fastcc') . " $return @$moduleName($argIR)";
    }

}

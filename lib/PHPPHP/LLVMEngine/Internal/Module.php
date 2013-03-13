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
    const ZVAL_ASSIGN_ARRAY_NEXT_ELEMENT = 'ZVAL_ASSIGN_ARRAY_NEXT_ELEMENT';
    const ZVAL_ASSIGN_ARRAY_INTEGER_ELEMENT = 'ZVAL_ASSIGN_ARRAY_INTEGER_ELEMENT';
    const ZVAL_ASSIGN_ARRAY_STRING_ELEMENT = 'ZVAL_ASSIGN_ARRAY_STRING_ELEMENT';
    const ZVAL_ASSIGN_ARRAY_ZVAL_ELEMENT = 'ZVAL_ASSIGN_ARRAY_ZVAL_ELEMENT';
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
    const ZVAL_FETCH_ARRAY_INTEGER_ELEMENT = 'ZVAL_FETCH_ARRAY_INTEGER_ELEMENT';
    const ZVAL_FETCH_ARRAY_STRING_ELEMENT = 'ZVAL_FETCH_ARRAY_STRING_ELEMENT';
    const ZVAL_FETCH_ARRAY_ZVAL_ELEMENT = 'ZVAL_FETCH_ARRAY_ZVAL_ELEMENT';
    const ZVAL_ITERATE_INIT = 'ZVAL_ITERATE_INIT';
    const ZVAL_ITERATE_CURRENT_KEY = 'ZVAL_ITERATE_CURRENT_KEY';
    const ZVAL_ITERATE_CURRENT_VALUE = 'ZVAL_ITERATE_CURRENT_VALUE';
    const ZVAL_ITERATE_FREE = 'ZVAL_ITERATE_FREE';
    const ZVAL_ITERATE_IS_END = 'ZVAL_ITERATE_IS_END';
    const ZVAL_ITERATE_NEXT = 'ZVAL_ITERATE_NEXT';
    const PHPLLVM_FUNCTION_REGISTER = 'PHPLLVM_FUNCTION_REGISTER';
    const PHPLLVM_FUNCTION_CALL_BY_NAME = 'PHPLLVM_FUNCTION_CALL_BY_NAME';

    public static function Define() {
        return array(
            self::T_ECHO => array('fastcc', Base::void(), array(Base::int(), Base::char('*'))),
            self::T_ECHO_ZVAL => array('fastcc', Base::void(), array(Zval::zval('*'))),
            self::ZVAL_INIT_ARRAY => array('fastcc', Zval::zval('*'), array(Zval::zval('*'))),
            self::ZVAL_LIST_INIT => array('', Base::void('*'), array()),
            self::ZVAL_LIST_GC => array('fastcc', Base::void(), array(Base::void('*'))),
            self::ZVAL_INIT => array('', Zval::zval('*'), array()),
            self::ZVAL_GC => array('fastcc', Base::void(), array(Zval::zval('*'))),
            self::ZVAL_GC_REGISTER => array('fastcc', Base::void(), array(Base::void('*'), Zval::zval('**'), Base::int(), Base::char('*'))),
            self::ZVAL_COPY_ON_WRITE => array('fastcc', Zval::zval('*'), array(Zval::zval('*'))),
            self::ZVAL_COPY => array('fastcc', Zval::zval('*'), array(Zval::zval('*'))),
            self::ZVAL_ASSIGN_ARRAY_NEXT_ELEMENT => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_ASSIGN_ARRAY_INTEGER_ELEMENT => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Zval::zval('*'), Base::long())),
            self::ZVAL_ASSIGN_ARRAY_STRING_ELEMENT => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Zval::zval('*'), Base::int(), Base::char('*'))),
            self::ZVAL_ASSIGN_ARRAY_ZVAL_ELEMENT => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_ASSIGN_INTEGER => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Base::long())),
            self::ZVAL_ASSIGN_BOOLEAN => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Base::long())),
            self::ZVAL_ASSIGN_DOUBLE => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Base::double())),
            self::ZVAL_ASSIGN_STRING => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Base::int(), Base::char('*'))),
            self::ZVAL_ASSIGN_ZVAL => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_ASSIGN_CONCAT_STRING => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Base::int(), Base::char('*'))),
            self::ZVAL_ASSIGN_CONCAT_ZVAL => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_ASSIGN_REF => array('fastcc', Zval::zval('*'), array(Zval::zval('*'))),
            self::ZVAL_STRING_VALUE => array('fastcc', Base::void(), array(Zval::zval('*'), Base::int('*'), Base::char('**'))),
            self::ZVAL_CONVERT_STRING => array('fastcc', Base::void(), array(Zval::zval('*'))),
            self::ZVAL_INTEGER_VALUE => array('fastcc', Base::int(), array(Zval::zval('*'))),
            self::ZVAL_CONVERT_INTEGER => array('fastcc', Base::void(), array(Zval::zval('*'))),
            self::ZVAL_DOUBLE_VALUE => array('fastcc', Base::double(), array(Zval::zval('*'))),
            self::ZVAL_CONVERT_DOUBLE => array('fastcc', Base::void(), array(Zval::zval('*'))),
            self::ZVAL_TYPE_CAST_NUMBER => array('fastcc', Base::int(), array(Zval::zval('*'), Zval::zval('*'), TypeCast::typeCast('*'), TypeCast::typeCast('*'))),
            self::ZVAL_TYPE_CAST_NUMBER_SINGLE => array('fastcc', Base::int(), array(Zval::zval('*'), TypeCast::typeCast('*'))),
            self::ZVAL_TYPE_CAST_SINGLE => array('fastcc', Base::int(), array(Zval::zval('*'), TypeCast::typeCast('*'))),
            self::ZVAL_TYPE_GUESS => array('fastcc', Base::int(), array(Zval::zval('*'))),
            self::ZVAL_TYPE_GUESS_NUMBER => array('fastcc', Base::int(), array(Zval::zval('*'))),
            self::ZVAL_EQUAL_STRING => array('fastcc', Base::long(), array(Zval::zval('*'), Base::int(), Base::char('*'))),
            self::ZVAL_EQUAL => array('fastcc', Base::long(), array(Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_EQUAL_EXACT => array('fastcc', Base::long(), array(Zval::zval('*'), Zval::zval('*'))),
            self::ZVAL_TEST_NULL => array('fastcc', Base::long(), array(Zval::zval('*'))),
            self::ZVAL_TEST_FALSE => array('fastcc', Base::long(), array(Zval::zval('*'))),
            self::ZVAL_FETCH_ARRAY_INTEGER_ELEMENT => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Base::long(), Base::int())),
            self::ZVAL_FETCH_ARRAY_STRING_ELEMENT => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Base::int(), Base::char('*'), Base::int())),
            self::ZVAL_FETCH_ARRAY_ZVAL_ELEMENT => array('fastcc', Zval::zval('*'), array(Zval::zval('*'), Zval::zval('*'), Base::int())),
            self::ZVAL_ITERATE_INIT => array('fastcc', Base::void('*'), array(Zval::zval('*'))),
            self::ZVAL_ITERATE_FREE => array('fastcc', Base::void(), array(Base::void('*'))),
            self::ZVAL_ITERATE_CURRENT_KEY => array('fastcc', Zval::zval('*'), array(Base::void('*'))),
            self::ZVAL_ITERATE_CURRENT_VALUE => array('fastcc', Zval::zval('*'), array(Base::void('*'))),
            self::ZVAL_ITERATE_IS_END => array('fastcc', Base::int(), array(Base::void('*'))),
            self::ZVAL_ITERATE_NEXT => array('fastcc', Base::void(), array(Base::void('*'))),
            self::PHPLLVM_FUNCTION_REGISTER => array('fastcc', Base::void(), array(Base::int(), Base::char('*'), Base::void('*'))),
            self::PHPLLVM_FUNCTION_CALL_BY_NAME => array('fastcc', Base::void(), array(Base::void('*'))),
            'single_debug' => array('fastcc', Base::void(), array(Base::int())),
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
            'gc.c',
            'functions.c',
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
        list($fastcc, $return, $argTypes) = $define[$moduleName];
        foreach ($args as $index => $arg) {
            if (isset($argTypes[$index]) && ($argTypes[$index] != '...')) {
                $argIR.=", $argTypes[$index] $arg";
            } else {
                $argIR.=", $arg";
            }
        }
        if (isset($argIR[0]) && $argIR[0] == ',') {
            $argIR = substr($argIR, 1);
        }
        $argIR = trim($argIR);
        if (count($argTypes) && $argTypes[count($argTypes)-1]=='...') {
            return "call $return (".implode(", ",$argTypes).")* @$moduleName($argIR)";
        }
        return "call $fastcc $return @$moduleName($argIR)";
    }

}

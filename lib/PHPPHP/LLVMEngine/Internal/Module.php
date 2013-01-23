<?php

namespace PHPPHP\LLVMEngine\Internal;

use PHPPHP\LLVMEngine\Type\Base;

final class Module {
    const T_ECHO='PHPLLVM_T_ECHO';

    public static function Define() {
        return array(
            self::T_ECHO => array(Base::void(), array(Base::int(), Base::char('*'))),
        );
    }

    public static function call(){
        $args=func_get_args();
        $moduleName=array_shift($args);
        $define=self::Define();
        if(!isset($define[$moduleName])){
            return '';
        }
        $argIR='';
        foreach ($args as $index => $arg){
            list($return,$argTypes)=$define[$moduleName];
            $argIR.=", $argTypes[$index] $arg";
        }
        if($argIR[0]==','){
           $argIR=substr($argIR,1);
        }
        $argIR=trim($argIR);
        return "call fastcc $return @$moduleName($argIR)";
    }
}

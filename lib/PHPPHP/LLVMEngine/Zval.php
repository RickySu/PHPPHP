<?php

namespace PHPPHP\LLVMEngine;

class Zval {

    protected static $type;

    /**
     *
     * @return Type\Base
     */
    protected static function getType($ptr=''){
        if(isset(self::$type[$ptr])){
            return self::$type[$ptr];
        }
        self::$type[$ptr] = Type\Base::structure(new Zval\Type(),$ptr);
        return self::$type[$ptr];
    }

    public static function getDeclare() {
        return self::getType()->getStructIR()->getIR();
    }

    public static function zval($ptr='') {
        return self::getType($ptr);
    }

    public static function PtrIRAlign() {
        return PHP_INT_SIZE;
    }

    public static function PtrIRDeclare() {
        return self::getType()->getStructIR()->getStructureIRName() . '*';
    }

}
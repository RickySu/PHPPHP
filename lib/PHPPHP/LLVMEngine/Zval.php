<?php
namespace PHPPHP\LLVMEngine;

class Zval extends Writer\Base {

    protected static $ptr;

    public function writeDeclare(){
        $this->writer->writeDeclare($value=new Zval\Value());
        $this->writer->writeDeclare($ptr=new Zval\Struct());
        self::$ptr=$ptr;
    }

    public static function PtrIRAlign(){
        return PHP_INT_SIZE;
    }

    public static function PtrIRDeclare(){
        return self::$ptr->getStructureIRName().'*';;
    }

}
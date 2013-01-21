<?php
namespace PHPPHP\LLVMEngine;

class Zval extends Writer\Base {

    protected static $PtrIRDeclare=null;

    public function writeDeclare(){
        $this->writer->writeDeclare($value=new Zval\Value());
        $this->writer->writeDeclare($ptr=new Zval\Ptr());
        self::$PtrIRDeclare=$ptr->getStructureIRName().'*';
    }

    public static function PtrIRDeclare(){
        return self::$PtrIRDeclare;
    }

}
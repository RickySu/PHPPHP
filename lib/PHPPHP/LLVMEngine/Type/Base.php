<?php

namespace PHPPHP\LLVMEngine\Type;
use PHPPHP\LLVMEngine\Writer;

class Base implements TypeDefine{

    protected $typeString;
    protected $size;

    public static function int($ptr=false) {
        return new self('i32'.($ptr?' *':''),($ptr?PHP_INT_SIZE:4));
    }

    public static function char($ptr=false){
        return new self('i8'.($ptr?' *':''),($ptr?PHP_INT_SIZE:1));
    }

    public static function long($ptr=false){
        $Size=PHP_INT_SIZE*8;
        return new self("i$Size".($ptr?' *':''),PHP_INT_SIZE);
    }

    public static function float($ptr=false){
        return new self('float'.($ptr?' *':''),($ptr?PHP_INT_SIZE:4));
    }

    public static function double($ptr=false){
        return new self('double'.($ptr?' *':''),($ptr?PHP_INT_SIZE:8));
    }

    public static function structure(Structure $structure,$ptr=false){
        $structure->setWriter(new Writer());
        $structure->writeDeclare();
        $structureIRName=$structure->getStructureIRName();
        if($ptr){
            $size=PHP_INT_SIZE;
            $structureIRName.="*";
        }
        else {
            $size=$structure->getStructureIRSize();
        }
        return new self($structureIRName,$size);
    }

    public function __construct($typeString,$size) {
        $this->typeString=$typeString;
        $this->size=$size;
    }

    public function __toString() {
        return $this->typeString;
    }

    public function size(){
        return $this->size;
    }
}
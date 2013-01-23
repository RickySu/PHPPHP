<?php

namespace PHPPHP\LLVMEngine\Type;
use PHPPHP\LLVMEngine\Writer;

class Base implements TypeDefine{

    protected $typeString;
    protected $size;
    protected $elements;
    protected $elementName;

    public static function void($ptr=false,$elements=1,$elementName='') {
        return new self(($ptr?"i8$ptr":'void'),($ptr?PHP_INT_SIZE:4),$elements,$elementName);
    }

    public static function int($ptr=false,$elements=1,$elementName='') {
        return new self('i32'.($ptr?$ptr:''),($ptr?PHP_INT_SIZE:4),$elements,$elementName);
    }

    public static function char($ptr=false,$elements=1,$elementName=''){
        return new self('i8'.($ptr?$ptr:''),($ptr?PHP_INT_SIZE:1),$elements,$elementName);
    }

    public static function long($ptr=false,$elements=1,$elementName=''){
        $Size=PHP_INT_SIZE*8;
        return new self("i$Size".($ptr?$ptr:''),PHP_INT_SIZE,$elements,$elementName);
    }

    public static function float($ptr=false,$elements=1,$elementName=''){
        return new self('float'.($ptr?$ptr:''),($ptr?PHP_INT_SIZE:4),$elements,$elementName);
    }

    public static function double($ptr=false,$elements=1,$elementName=''){
        return new self('double'.($ptr?$ptr:''),($ptr?PHP_INT_SIZE:8));
    }

    public static function structure(Structure $structure,$ptr=false,$elements=1,$elementName=''){
        $structure->setWriter(new Writer());
        $structure->writeDeclare();
        $structureIRName=$structure->getStructureIRName();
        if($ptr){
            $size=PHP_INT_SIZE;
            $structureIRName.=$ptr;
        }
        else {
            $size=$structure->getStructureIRSize();
        }
        return new self($structureIRName,$size,$elements,$elements);
    }

    public function __construct($typeString,$size,$elements=1,$elementName=null) {
        $this->typeString=$typeString;
        $this->size=$size;
        $this->elements=$elements;
        $this->elementName=$elementName;
    }

    public function __toString() {
        return $this->typeString;
    }

    public function size(){
        return $this->size;
    }

    public function elementName(){
        return $this->elementName;
    }
    public function ptr(){
        $typeString=strrev(trim($this->typeString));
        $typeString=trim(strrev(substr($typeString,1)));
        return "getelementptr inbounds ([{$this->elements} x {$typeString}]* {$this->elementName}, ".self::int()." 0, ".self::int()." 0)";
    }
}
<?php

namespace PHPPHP\LLVMEngine\Type;
use PHPPHP\LLVMEngine\Writer;

class Base implements TypeDefine{

    protected $typeString;
    protected $size;
    protected $elements;
    protected $elementName;
    protected $struct;
    protected $position=0;

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

    public static function null($ptr=false,$elements=1,$elementName=''){
        return new self('null',0);
    }

    /**
     *
     * @param \PHPPHP\LLVMEngine\Type\Structure $structure
     * @param type $ptr
     * @param type $elements
     * @param type $elementName
     * @return Base
     */
    public static function structure(Structure $structure,$ptr=false,$elements=1,$elementName=''){
        $structure->analyzeStruct();
        $structureIRName=$structure->getStructureIRName();
        if($ptr){
            $size=PHP_INT_SIZE;
            $structureIRName.=$ptr;
        }
        else {
            $size=$structure->getStructureIRSize();
        }
        return new self($structureIRName,$size,$elements,$elementName,$structure);
    }

    public function __construct($typeString,$size,$elements=1,$elementName=null,$struct=null) {
        $this->typeString=$typeString;
        $this->size=$size;
        $this->elements=$elements;
        $this->elementName=$elementName;
        $this->struct=$struct;
    }

    public function getStructIR(){
        return $this->struct;
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
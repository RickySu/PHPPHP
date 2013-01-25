<?php

namespace PHPPHP\LLVMEngine\Type;

abstract class Structure implements TypeDefine {

    protected $structureIRSize;
    protected $structureIRName;
    protected $IR = array();


    public function __toString() {
        return implode("\n", $this->getIR())."\n";
    }

    public function size() {
        return $this->getStructureIRSize();
    }

    public function getDeclareStructure(array &$structure, $structureName) {
        switch ($structure['type']) {
            case 'union':
                return $this->getDeclareStructureunion($structure, "union.$structureName");
                break;
            case 'struct':
                return $this->getDeclareStructureStructure($structure, "struct.$structureName");
                break;
        }
    }

    protected function getDeclareStructureStructure(array &$structure, $structureName) {
        $struct = array();
        $structSize = 0;

        $index=0;
        foreach ($structure['struct'] as $name => $item) {
            $structure['struct']["$name.pos"]=$index;
            if ($item instanceof Base) {
                $struct[] = (string) $item;
                $structSize+=$item->size();
            } else {
                list($size, $define) = $this->getDeclareStructure($structure['struct'][$name], "{$structureName}_{$name}");
                $struct[] = (string) $define;
                $structSize+=$size;
            }
            $index++;
        }
        $structureDefine = implode(", ", $struct);
        $this->IR[] = "%$structureName = type { $structureDefine }";
        return array($structSize, "%$structureName");
    }

    protected function getDeclareStructureunion(array &$structure, $structureName) {
        $struct = '';
        $structSize = 0;
        $index=0;
        foreach ($structure['struct'] as $name => $item) {
            $structure['struct']["$name.pos"]=0;
            if ($item instanceof Base) {
                if ($item->size() > $structSize) {
                    $structSize = $item->size();
                    $struct = (string) $item;
                }
            } else {
                list($size, $define) = $this->getDeclareStructure($structure['struct'][$name], "{$structureName}_{$name}");
                if ($size > $structSize) {
                    $structSize = $size;
                    $struct = $define;
                }
            }
        }
        $this->IR[]="%$structureName = type { $struct }";
        return array($structSize, "%$structureName");
    }

    public function analyzeStruct(){
        list($this->structureIRSize, $this->structureIRName) = $this->getDeclareStructure($this->structureDefine, $this->structName);
    }

    public function getIR() {
        return $this->IR;
    }

    public function getStructureIRSize() {
        return $this->structureIRSize;
    }

    public function getStructureIRName() {
        return $this->structureIRName;
    }

    public function getElementPosition(){
        $args = func_get_args();
        $position=0;
        $define=&$this->structureDefine;
        foreach($args as $index){
            if(!is_array($position)){
                $position=array();
            }
            $position[]=$define['struct']["$index.pos"];
            $define=&$define['struct'][$index];
        }
        return $position;
    }

    public function getElement(){
        $args = func_get_args();
        $define=&$this->structureDefine;
        foreach($args as $index){
            $define=&$define['struct'][$index];
        }
        return $define;
    }

    public function getElementPtrIR(){
        $args = func_get_args();
        //$toRegister,$fromRegister;
        $toRegister = array_shift($args);
        $fromRegister = array_shift($args);
        $Position=implode(', i32 ',call_user_func_array(array($this,'getElementPosition'), $args));
        $define=call_user_func_array(array($this,'getElement'), $args);
        return "$toRegister = getelementptr inbounds {$this->structureIRName}* $fromRegister,{$define} 0, i32 $Position";
    }

}
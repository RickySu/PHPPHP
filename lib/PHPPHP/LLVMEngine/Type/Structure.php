<?php

namespace PHPPHP\LLVMEngine\Type;

use PHPPHP\LLVMEngine\Writer;

abstract class Structure extends Writer\Base {
    protected $structureIRSize;
    protected $structureIRName;

    public function writeDefineStructure(array $structure, $structureName) {
        switch ($structure['type']) {
            case 'union':
                return $this->writeDefineStructureunion($structure, "union.$structureName");
                break;
            case 'struct':
                return $this->writeDefineStructureStructure($structure, "struct.$structureName");
                break;
        }
    }

    protected function writeDefineStructureStructure(array $structure, $structureName) {
        $struct = array();
        $structSize = 0;

        foreach ($structure['struct'] as $name => $item) {
            if ($item instanceof Base) {
                $struct[]=(string)$item;
                $structSize+=$item->size();
            }
            else{
                $this->writeDefine($item, "{$structureName}_{$name}");
            }
        }
        $structureDefine=implode(", ",$struct);
        $this->writer->writeDefineBlock("%$structureName = type { $structureDefine }");
        return array($structSize,"%$structureName");
    }

    protected function writeDefineStructureunion(array $structure, $structureName) {
        $struct = '';
        $structSize = 0;
        foreach ($structure['struct'] as $name => $item) {
            if ($item instanceof Base) {
                if ($item->size() > $structSize) {
                    $structSize = $item->size();
                    $struct = (string) $item;
                }
            }
            else{
                list($size,$define)=$this->writeDefineStructure($item, "{$structureName}_{$name}");
                if ($size > $structSize) {
                    $structSize=$size;
                    $struct=$define;
                }
            }
        }
        $this->writer->writeDefineBlock("%$structureName = type { $struct }");
        return array($structSize,"%$structureName");
    }

    public function writeDefine() {
        list($this->structureIRSize,$this->structureIRName)=$this->writeDefineStructure($this->structureDefine,$this->structName);
    }

    public function getStructureIRSize(){
        return $this->structureIRSize;
    }

    public function getStructureIRName(){
        return $this->structureIRName;
    }
}
<?php

namespace PHPPHP\LLVMEngine\Type;

use PHPPHP\LLVMEngine\Writer;

abstract class Structure extends Writer\Base {
    protected $structureIRSize;
    protected $structureIRName;

    public function writeDeclareStructure(array $structure, $structureName) {
        switch ($structure['type']) {
            case 'union':
                return $this->writeDeclareStructureunion($structure, "union.$structureName");
                break;
            case 'struct':
                return $this->writeDeclareStructureStructure($structure, "struct.$structureName");
                break;
        }
    }

    protected function writeDeclareStructureStructure(array $structure, $structureName) {
        $struct = array();
        $structSize = 0;

        foreach ($structure['struct'] as $name => $item) {
            if ($item instanceof Base) {
                $struct[]=(string)$item;
                $structSize+=$item->size();
            }
            else{
                $this->writeDeclare($item, "{$structureName}_{$name}");
            }
        }
        $structureDefine=implode(", ",$struct);
        $this->writer->writeDeclareBlock("%$structureName = type { $structureDefine }");
        return array($structSize,"%$structureName");
    }

    protected function writeDeclareStructureunion(array $structure, $structureName) {
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
                list($size,$define)=$this->writeDeclareStructure($item, "{$structureName}_{$name}");
                if ($size > $structSize) {
                    $structSize=$size;
                    $struct=$define;
                }
            }
        }
        $this->writer->writeDeclareBlock("%$structureName = type { $struct }");
        return array($structSize,"%$structureName");
    }

    public function writeDeclare() {
        list($this->structureIRSize,$this->structureIRName)=$this->writeDeclareStructure($this->structureDefine,$this->structName);
    }

    public function getStructureIRSize(){
        return $this->structureIRSize;
    }

    public function getStructureIRName(){
        return $this->structureIRName;
    }
}
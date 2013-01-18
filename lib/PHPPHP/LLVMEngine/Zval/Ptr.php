<?php

namespace PHPPHP\LLVMEngine\Zval;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Type\Structure;

class Ptr extends Structure {

    protected $structName = "zvalue_ptr";
    protected $structureDefine;

    protected function defineStructure() {
        $this->structureDefine = array(
            'type' => 'struct',
            'struct' => array(
                'value' => Base::structure(new Value(), true),
                'refcount' => Base::int(),
                'type' => Base::char(),
                'is_ref' => Base::char(),
            ),
        );
    }

    public function __construct() {
        $this->defineStructure();
    }

}
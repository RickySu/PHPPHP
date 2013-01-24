<?php

namespace PHPPHP\LLVMEngine\Zval;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Type\Structure;

class Struct extends Structure {

    protected $structName = "zval";
    protected $structureDefine;

    const TYPE_NULL = 0;
    const TYPE_INTEGER = 1;
    const TYPE_STRING = 2;
    const TYPE_DOUBLE = 3;
    const TYPE_BOOLEAN = 4;

    protected function defineStructure() {
        $this->structureDefine = array(
            'type' => 'struct',
            'struct' => array(
                'value' => Base::structure(new Value()),
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
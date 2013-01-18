<?php

namespace PHPPHP\LLVMEngine\Zval;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Type\Structure;

class Value extends Structure {

    protected $structName="zvalue_value";
    protected $structureDefine;
    protected $writer;

    protected function defineStructure() {
        $this->structureDefine = array(
            'type' => 'union',
            'struct' => array(
                'lval' => Base::long(),
                'dval' => Base::double(),
                'str' => array(
                    'type' => 'struct',
                    'struct' => array(
                        'val' => Base::char($ptr = true),
                        'len' => Base::int(),
                    ),
                ),
            ),
        );
    }

    public function __construct() {
        $this->defineStructure();
    }

}
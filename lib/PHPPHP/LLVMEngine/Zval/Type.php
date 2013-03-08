<?php

namespace PHPPHP\LLVMEngine\Zval;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Type\Structure;

class Type extends Structure
{
    protected $structName = "zval";
    protected $structureDefine;

    const TYPE_NULL     =   0;
    const TYPE_INTEGER  =   1;
    const TYPE_STRING   =   2;
    const TYPE_DOUBLE   =   3;
    const TYPE_BOOLEAN  =   4;
    const TYPE_ARRAY    =   5;
    const TYPE_OBJECT   =   6;
    const TYPE_RESOURCE =   7;

    protected function defineStructure()
    {
        $this->structureDefine = array(
            'type' => 'struct',
            'struct' => array(
                'value' => array(
                    'type' => 'union',
                    'struct' => array(
                        'lval' => Base::long(),
                        'dval' => Base::double(),
                        'str' => array(
                            'type' => 'struct',
                            'struct' => array(
                                'val' => Base::char('*'),
                                'len' => Base::int(),
                            ),
                        ),
                    ),
                ),
                'refcount' => Base::int(),
                'type' => Base::char(),
                'is_ref' => Base::char(),
            ),
        );
    }

    public function __construct()
    {
        $this->defineStructure();
    }

}

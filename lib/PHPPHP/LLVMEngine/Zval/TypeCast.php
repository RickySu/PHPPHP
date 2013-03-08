<?php

namespace PHPPHP\LLVMEngine\Zval;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Type\Structure;

class TypeCast extends Structure
{
    protected $structName="zvalue_type_cast";
    protected $structureDefine;

    protected function defineStructure()
    {
        $this->structureDefine = array(
            'type' => 'union',
            'struct' => array(
                'lval' => Base::long(),
                'dval' => Base::double(),
            ),
        );
    }

    public function __construct()
    {
        $this->defineStructure();
    }

}

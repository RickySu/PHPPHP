<?php

namespace PHPPHP\LLVMEngine\Functions;

use PHPPHP\LLVMEngine\Type\Base;
use PHPPHP\LLVMEngine\Type\Structure;

class JumpTable extends Structure
{
    protected $structName = "jumptable";
    protected $structureDefine;

    protected function defineStructure()
    {
        $this->structureDefine = array(
            'type' => 'struct',
            'struct' => array(
                'len' => Base::int(),
                'fname' => Base::char('*'),
                'realfunction' => Base::void('*'),
            ),
        );
    }

    public function __construct()
    {
        $this->defineStructure();
    }

}

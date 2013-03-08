<?php

namespace PHPPHP\LLVMEngine\Writer;

use PHPPHP\LLVMEngine\Writer;

abstract class Base
{
    /**
     *
     * @var Writer
     */
    protected $writer = null;

    public function setWriter(Writer $writer)
    {
        $this->writer = $writer;
    }

    public function getWriter()
    {
        return $this->writer;
    }

    public function escapeString($str)
    {
        $output = '';
        $parsedBytes=unpack('c*', $str);
        foreach ($parsedBytes as $Byte) {
            if ($Byte < 32 || $Byte > 126) {
                $output.=sprintf('\\%02X',$Byte);
            } else {
                $output.=pack('c',$Byte);
            }
        }

        return $output;
    }

}

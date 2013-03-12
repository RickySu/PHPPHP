<?php

namespace PHPPHP\LLVMEngine;

class JumpTable
{
    protected static $jumpTable;
    protected $varName;
    protected $isTmp;
    protected $IRWriter;
    
    /**
     *
     * @return Type\Base
     */
    protected static function getJumpTable($ptr = '')
    {
        if (isset(self::$jumpTable[$ptr])) {
            return self::$jumpTable[$ptr];
        }
        self::$jumpTable[$ptr] = Type\Base::structure(new Functions\JumpTable(), $ptr);

        return self::$jumpTable[$ptr];
    }

    public static function getDeclare()
    {
        return self::getJumpTable()->getStructIR()->getIR();
    }

    public static function jumpTable($ptr = '')
    {
        return self::getJumpTable($ptr);
    }

    public function __construct($varName)
    {
        $this->varName = $varName;
    }

    public function getIRRegister()
    {
        return $this->varName;
    }

    public function __toString()
    {
        return $this->getIRRegister();
    }

    public function savePtrRegister($ptrRegister)
    {
        $this->IRWriter->writeOpLineIR("store " . self::jumpTable('*') . " $ptrRegister, " . self::jumpTable('**') . " {$this->getIRRegister()}, align " . self::jumpTable('*')->size());
    }

    public function getPtrRegister()
    {
        $returnRegister=$this->IRWriter->getRegisterSerial();
        $this->IRWriter->writeOpLineIR("$returnRegister = load " . self::jumpTable('*') . " {$this->getIRRegister()}");

        return $returnRegister;
    }

}

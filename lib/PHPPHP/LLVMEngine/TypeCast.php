<?php

namespace PHPPHP\LLVMEngine;

class TypeCast
{
    protected static $typeCast;
    protected $varName;
    protected $isTmp;
    protected $IRWriter;
    const TYPE_CAST_OP1='type_cast_op1';
    const TYPE_CAST_OP2='type_cast_op2';

    /**
     *
     * @return Type\Base
     */
    protected static function getTypeCast($ptr = '')
    {
        if (isset(self::$typeCast[$ptr])) {
            return self::$typeCast[$ptr];
        }
        self::$typeCast[$ptr] = Type\Base::structure(new Zval\TypeCast(), $ptr);

        return self::$typeCast[$ptr];
    }

    public static function getDeclare()
    {
        return self::getTypeCast()->getStructIR()->getIR();
    }

    public static function typeCast($ptr = '')
    {
        return self::getTypeCast($ptr);
    }

    public function __construct($varName, $IRWriter)
    {
        $this->varName = $varName;
        $this->IRWriter = $IRWriter;
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
        $this->IRWriter->writeOpLineIR("store " . self::typeCast('*') . " $ptrRegister, " . self::typeCast('**') . " {$this->getIRRegister()}, align " . self::typeCast('*')->size());
    }

    public function getPtrRegister()
    {
        $returnRegister=$this->IRWriter->getRegisterSerial();
        $this->IRWriter->writeOpLineIR("$returnRegister = load " . self::typeCast('*') . " {$this->getIRRegister()}");

        return $returnRegister;
    }

}

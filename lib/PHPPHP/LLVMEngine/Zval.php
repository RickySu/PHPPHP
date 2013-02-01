<?php

namespace PHPPHP\LLVMEngine;

use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Zval {

    protected static $type;
    protected $varName;
    protected $isTmp;
    protected $IRWriter;

    const ZVAL_GC_LIST = '%zval_gc_list';

    /**
     *
     * @return Type\Base
     */
    protected static function getType($ptr = '') {
        if (isset(self::$type[$ptr])) {
            return self::$type[$ptr];
        }
        self::$type[$ptr] = Type\Base::structure(new Zval\Type(), $ptr);
        return self::$type[$ptr];
    }

    public static function getDeclare() {
        return self::getType()->getStructIR()->getIR();
    }

    public static function zval($ptr = '') {
        return self::getType($ptr);
    }

    public function __construct($varName, $initZval, $isTmp, $IRWriter) {
        $this->varName = $varName;
        $this->isTmp = $isTmp;
        $this->IRWriter = $IRWriter;
        if ($initZval) {
            $ptrRegister=$this->IRWriter->InternalModuleCall(InternalModule::ZVAL_INIT, self::ZVAL_GC_LIST);
            $this->savePtrRegister($ptrRegister);
        }
    }

    public function getIRRegister() {
        return "%PHPVar" . ($this->isTmp ? '_tmp' : '') . "_{$this->varName}";
    }

    public function __toString() {
        return $this->getIRRegister();
    }

    public function savePtrRegister($ptrRegister) {
        $this->IRWriter->writeOpLineIR("store " . self::zval('*') . " $ptrRegister, " . self::zval('**') . " {$this->getIRRegister()}, align " . self::zval('*')->size());
    }

    public function getPtrRegister() {
        $returnRegister=$this->IRWriter->getRegisterSerial();
        $this->IRWriter->writeOpLineIR("$returnRegister = load " . self::zval('**') . " {$this->getIRRegister()}, align " . self::zval('*')->size());
        return $returnRegister;
    }

}
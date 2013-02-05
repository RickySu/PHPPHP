<?php

namespace PHPPHP\LLVMEngine;

use PHPPHP\LLVMEngine\Internal\Module as InternalModule;

class Zval {

    protected static $type;
    protected $varName;
    protected $isTemp;
    protected $IRWriter;

    const ZVAL_GC_LIST = '%zval_gc_list';
    const ZVAL_TEMP_GC_LIST = '%zval_temp_gc_list';
    const ZVAL_TEMP_OP = 'zval_temp_op';

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

    public function getGCList(){
        if($this->isTemp){
            return self::ZVAL_TEMP_GC_LIST;
        }
        return self::ZVAL_GC_LIST;
    }
    public function __construct($varName, $initZval, $isTemp, $IRWriter) {
        $this->varName = $varName;
        $this->isTemp = $isTemp;
        $this->IRWriter = $IRWriter;
        if ($initZval) {
            if ($isTemp) {
                $ptrRegister = $this->IRWriter->InternalModuleCall(InternalModule::ZVAL_TEMP_INIT,self::ZVAL_TEMP_GC_LIST);
            }else{
                $ptrRegister = $this->IRWriter->InternalModuleCall(InternalModule::ZVAL_INIT, self::ZVAL_GC_LIST);
            }
            $this->savePtrRegister($ptrRegister);
        }
    }

    public function isTemp(){
        return $this->isTemp;
    }

    public function getIRRegister() {
        return "%PHPVar" . ($this->isTemp ? '_temp' : '') . "_{$this->varName}";
    }

    public function __toString() {
        return $this->getIRRegister();
    }

    public function savePtrRegister($ptrRegister) {
        $this->IRWriter->writeOpLineIR("store " . self::zval('*') . " $ptrRegister, " . self::zval('**') . " {$this->getIRRegister()}, align " . self::zval('*')->size());
    }

    public function getPtrRegister() {
        $returnRegister = $this->IRWriter->getRegisterSerial();
        $this->IRWriter->writeOpLineIR("$returnRegister = load " . self::zval('**') . " {$this->getIRRegister()}, align " . self::zval('*')->size());
        return $returnRegister;
    }

}
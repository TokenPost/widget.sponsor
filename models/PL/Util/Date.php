<?php
namespace PL\Models\Util;

class Date {

    protected $_date;

    public function __construct($date = null) {
        if(is_null($date) == true){
            $this->_date = time();
        } elseif(is_numeric($date) == true) {
            $this->_date = $date;
        } else {
            $this->_date = strtotime($date);
        }
    }

    public function getDate($format = 'Y-m-d H:i:s') {
        return date($format, $this->_date);
    }

    public function getTimeStamp() {
        return $this->_date;
    }

    public function getDateWithTimezone($zone = 'UTC', $format = 'Y-m-d H:i:s') {
        if($zone == 'UTC'){
            return date($format, $this->_date);
        } else {
            switch ($zone){
                case 'CST':
                    return Util::convertTimezone($this->_date, 'Asia/Shanghai', $format);
                    break;
                case 'KST':
                    return Util::convertTimezone($this->_date, 'Asia/Seoul', $format);
                    break;
            }
            return date($format, $this->_date);
        }
    }

    public function getTimeStampWithTimezone($zone) {
        return strtotime($this->getDateWithTimezone($zone));
    }

    public function setDate($date, $calc, $format = 'Y-m-d H:i:s') {
        return Util::getDate($date, $calc, $format);
    }

    public function getDateDiff($abs = 'Y', $format = 'd', $int = 'ceil'){
        return Util::getDateDiff($this->getDate($abs, $format, $int));
    }
}

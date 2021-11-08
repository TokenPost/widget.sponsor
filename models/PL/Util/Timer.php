<?php
namespace PL\Models\Util;

class Timer {

    private $_start;
    private $_recode = array();

    public function __construct(){
        $this->clear();
    }

    public function clear(){
        $start = microtime();
        $start = explode(" ",$start);
        $this->_start = $start[1] + $start[0];
        $this->_recode = null;
        unset($this->_recode);
        $this->_recode = array();
    }

    public function getDurationTime(){
        $now = microtime();
        $now = explode(" ",$now);
        $now = $now[1] + $now[0];
        $result = ($now - $this->_start);
        return $result;
    }

    public function setRecodeTime(){
        $now = microtime();
        $now = explode(" ",$now);
        $now = $now[1] + $now[0];
        if(sizeof($this->_recode) >= 1){
            $result = ($now - end($this->_recode));
        } else {
            $result = ($now - $this->_start);
        }
        array_push($this->_recode, $now);

        //$now = ($now - $this->_start);
        return $result;
        //$totalTime = explode(".",$check);
    }
} 
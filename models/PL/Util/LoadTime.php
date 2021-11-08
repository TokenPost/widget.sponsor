<?php
namespace PL\Models\Util;

use PL\Models\Util\Util;

class LoadTime{
    private $_time_start     = 0;
    private $_time_end       = 0;
    private $_time           = 0;
    private $_uri            = '';
    private $_module         = 'admin';
    private $_writeMode      = 'slow';

    public function __construct(){
        $this->_time_start= microtime(true);
    }
    public function __destruct(){
        $this->_time_end = microtime(true);
        $this->_time = $this->_time_end - $this->_time_start;
        //echo "Loaded in $this->time seconds\n";
        $type = 'Y-m-d H:i:s';

        if($this->getWriteMode() == 'all'){
            if(defined(SITE_BRANCH_CODE)){
                $fp = fopen( "/var/log/publishlink/" . SITE_BRANCH_CODE . "/allPage.log", "a");
            } else {
                $fp = fopen( "/var/log/publishlink/allPage.log", "a");
            }
            fwrite($fp, "Now : " . date($type, time()) . " Module : " . $this->getModule() . " URI : " . $this->_uri . "\n");
            fwrite($fp, "Loaded in $this->_time seconds\n\n");
            fclose(($fp));
        }

        if($this->_time >= 1){
            if(defined(SITE_BRANCH_CODE)){
                $fp = fopen( "/var/log/publishlink/" . SITE_BRANCH_CODE . "/slowPage.log", "a");
            } else {
                $fp = fopen( "/var/log/publishlink/slowPage.log", "a");
            }

            fwrite($fp, "Now : " . date($type, time()) . " Module : " . $this->getModule() . " URI : " . $this->_uri . "\n");
            fwrite($fp, "Loaded in $this->_time seconds\n\n");
            fclose(($fp));
        }
    }

    public function setUrl($uri){
        $this->_uri = $uri;
    }

    public function setModule($module){
        $this->_module = substr($module, 1);
    }

    public function getModule(){
        return $this->_module;
    }

    public function setWriteMode($mode){
        $this->_writeMode = $mode;
    }

    public function getWriteMode(){
        return $this->_writeMode;
    }
}
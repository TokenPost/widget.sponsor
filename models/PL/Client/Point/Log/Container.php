<?php
namespace PL\Models\Client\Point\Log;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Client\Client;
use PL\Models\Client\Point\Point;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;


class Container extends AbstractContainer {

    protected $_pointId;
    protected $_pointInstance;

    public function __construct(Point $pointInstance = null) {

        if(is_null($pointInstance) != true){
            $this->setPointInstance($pointInstance);
        }
        parent::__construct(Log::tableName);
        $this->setTableName(Log::tableName);
    }

    public static function getTableNameStatic(){
        return Log::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Log::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Log::getInstance($date);
    }


    public function getPointId(){
        return $this->_pointId;
    }

    private function setPointId($pointId){
        $this->_pointId = $pointId;
    }

    public function getPointInstance(){
        return $this->_pointInstance;
    }

    public function setPointInstance(Point $pointInstance){
        $this->_pointInstance = $pointInstance;
        $this->setPointId($pointInstance->getId());
    }


    public function recordLog($typeId, $position, $point, $latestPoint, $log, $comment, $regIp, $adminId) {
        if(is_numeric($point) == false || $point == 0) return false;
        if(is_numeric($this->getPointId()) == false || $this->getPointId() < 1) return false;
        if(is_numeric($adminId) == false || $adminId < 1) return false;
        if(($position == Log::Position_Add || $position == Log::Position_Subtract) == false) return false;

        $newLogItem['clientId'] = $this->getPointId();
        $newLogItem['adminId'] = $adminId;
        $newLogItem['typeId'] = $typeId;
        $newLogItem['position'] = $position;
        $newLogItem['point'] = $point;
        $newLogItem['before'] = $latestPoint;
        $newLogItem['after'] = $latestPoint + $point;
        $newLogItem['log'] = $log;
        $newLogItem['comment'] = $comment;
        $newLogItem['regIp'] = $regIp;
        $newLogItem['regDate'] = Util::getDbNow();

        $result  = $this->addNew($newLogItem);
    }




    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }

}

<?php
namespace PL\Models\Site\Item\Point\Log;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;

use PL\Models\Site\Item\Point\Point as SiteItemPoint;


class Container extends AbstractContainer {

    protected $_pointId;
    protected $_siteItemPointInstance;

    public function __construct(SiteItemPoint $siteItemPointInstance = null) {
        if(is_null($siteItemPointInstance) == false) {
            $this->setSiteItemPointInstance($siteItemPointInstance);
        }

        parent::__construct(Log::tableName);
        $this->setTableName(Log::tableName);

    }

    public static function getTableNameStatic(){
        return Log::tableName;
    }

    public static function getObjectInstanceStatic($data) : Log {
        return Log::getInstance($data);
    }

    public function getObjectInstance($data) : Log {
        return Log::getInstance($data);
    }

    public function getSiteItemPointInstance(){
        return $this->_siteItemPointInstance;
    }

    public function setSiteItemPointInstance(SiteItemPoint $siteItemPointInstance){
        $this->_siteItemPointInstance = $siteItemPointInstance;
        $this->setPointId($siteItemPointInstance->getId());
    }

    public function getPointId(){
        return $this->_pointId;
    }

    public function setPointId($pointId) {
        $this->_pointId = $pointId;
    }

    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getPointId()) == true) $where[] = '`pointId` = "' . $this->getPointId() . '"';
        return $where;
    }

    public function recordLog($typeId, $position, $point, $before, $after, $log, $comment, $regIp, $requesterType, $requesterId = 0, $approverId = 0, $date = '') {
        if(is_numeric($this->getPointId()) == false || $this->getPointId() < 1) return false;
        if(!($requesterType == Log::RequesterType_Self || $requesterType == Log::RequesterType_Admin || $requesterType == Log::RequesterType_Bot)){
            // requester type error
            return false;
        }
        // bot 0 허용
        if(is_numeric($requesterId) == false || $requesterId < 0) return false;
        if(is_numeric($approverId) == false || $approverId < 0) return false;
        if(($position == Log::Position_Add || $position == Log::Position_Subtract) == false) return false;
        if($date == '') {
            $date = Util::getLocalTime();
        }


        $newLogItem['pointId']          = $this->getPointId();
        $newLogItem['requesterType']    = $requesterType;
        $newLogItem['requesterId']      = $requesterId;
        $newLogItem['approverId']       = $approverId;
        $newLogItem['typeId']           = $typeId;
        $newLogItem['position']         = $position;
        $newLogItem['point']            = $point;
        $newLogItem['date']             = $date;
        $newLogItem['before']           = $before;
        $newLogItem['after']            = $after;
        $newLogItem['log']              = $log;
        $newLogItem['comment']          = $comment;
        $newLogItem['regIp']            = $regIp;
        $newLogItem['regDate']          = Util::getDbNow();

        $ret = $this->addNew($newLogItem);
        if($ret == false){
            return false;
        }

        return Log::getInstance($ret);
    }

    public function getDailyConsumPPoint($pointId){

        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT SUM(`point`) as `pointDaily`  
            FROM `SiteItemPointLog` 
            WHERE `pointId` = ?
            AND DATE_FORMAT(`regDate`, '%Y%m%d') = DATE_FORMAT(NOW(), '%Y%m%d')
EOD;
        $var = array($pointId);
        $data1 = $db->query($query, $var)->fetchAll();

        if($data1[0]['pointDaily'] >= 0){
            $dailyPoint = 0;
        } else {
            $dailyPoint = $data1[0]['pointDaily'];
        }

        return array(
            'pointDaily'    => $dailyPoint,
        );
    }

    public function getDailyPoint($pointId){

        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            (SELECT SUM(`point`) as `point`,  `after` , `regDate` 
            FROM `SiteItemPointLog` 
            WHERE `pointId` = ?
            AND DATE_FORMAT(`regDate`, '%Y%m%d') IN (DATE_FORMAT(NOW(), '%Y%m%d'))
            ORDER BY `regDate` DESC
            )
            
            UNION
            
            (SELECT  `point`,  `after` , `regDate` 
            FROM `SiteItemPointLog` 
            WHERE `pointId` = ?
            AND DATE_FORMAT(`regDate`, '%Y%m%d') IN (DATE_FORMAT(NOW(), '%Y%m%d'))
            ORDER BY `regDate` DESC
            LIMIT 1
            )
            
            UNION
            
            (SELECT `point`,  `after` , `regDate` 
            FROM `SiteItemPointLog` 
            WHERE `pointId` = ?
            AND DATE_FORMAT(`regDate`, '%Y%m%d') IN (DATE_FORMAT(NOW() - INTERVAL 1 DAY, '%Y%m%d'))
            ORDER BY `regDate` DESC
            LIMIT 1
            )
EOD;
        $var = array($pointId, $pointId, $pointId);
        $data1 = $db->query($query, $var)->fetchAll();

        var_dump($data1[0]['point']);
        var_dump($data1[1]['after']);
        var_dump($data1[2]['after']);


        if($data1[0]['point'] >= 0){
            $dailyPoint = 0;
        } else {
            $dailyPoint = $data1[0]['point'];
        }

        if($data1[1]['after'] <= 0 || $data1[2]['after'] <=0 ){
            $dayToDay = '-';
        } else {
            $dayToDay = round($data1[1]['after'] / $data1[2]['after'] * 100) - 100;
        }

        if($dayToDay > 0){
            $fontColor = 'red';
        } else if ($dayToDay < 0) {
            $fontColor = 'blue';
        } else if ($dayToDay == 0) {
            $fontColor = 'grey';
            $dayToDay = '-';
        }

        return array(
            'pointDaily'    => $dailyPoint,
            'dayToDay'    => $dayToDay,
            'fontColor'    => $fontColor,
        );
    }




}
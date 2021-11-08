<?php
namespace PL\Models\Client\Activity;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Client\Client;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

use PL\Models\Reward\Reward;
use PL\Models\Reward\Container as RewardContainer;

use PL\Models\Site\Item\Reward\Reward as SiteReward;
use PL\Models\Site\Item\Reward\Container as SiteRewardContainer;

class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    protected $_date;
    protected $_rewardId;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(Activity::tableName);
        $this->setTableName(Activity::tableName);
    }

    public static function getTableNameStatic(){
        return Activity::tableName;
    }

    public static function getObjectInstanceStatic($date) : Activity {
        return Activity::getInstance($date);
    }

    public function getObjectInstance($date) : Activity {
        return Activity::getInstance($date);
    }


    public function setClientInstance(Client $clientInstance){
        $this->_clientInstance = $clientInstance;
        $this->setClientId($clientInstance->getId());
    }

    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getDate(){
        return $this->_date;
    }

    public function setDate($date){
        $this->_date = $date;
    }

    public function getRewardId(){
        return $this->_rewardId;
    }

    public function setRewardId($rewardId){
        $this->_rewardId = $rewardId;
    }

    public function getTotalCount() {
        if($this->getClientId() == '') return null;

        $result = array();
        $db     = DI::getDefault()->getShared('db');
        $query = <<<EOD
            SELECT count(*) activity FROM `ClientActivity` WHERE `clientId` = ?
EOD;
        $data = $db->query($query, array($this->getClientId()))->fetch();

        if(empty($data['activity']) == true) {
            // 답변이 없을 경우
            $result = 0;
        } else {
            $result = $data['activity'];
        }
        return $result;
    }

    // origin
    public function addActivity2($date, $rewardId, $targetId, $regIp){
        if(Util::isInteger($this->getClientId()) != true) return false;
        if($this->getClientInstance()->getStatus() != Client::Status_Active) return false;
        //if($this->getClientInstance()->getCertificationId() < 1) return false;
        // 임시로 본인인증 패스

        $exist = $this->isActivity($date, $rewardId, $targetId);
        if($exist >= 1){
            // 이미 있음.
            return false;
        }
        $size = $this->countActivity($date, $rewardId);

        $rewardInstance = RewardContainer::isItem($rewardId);
        if($rewardInstance == false || $rewardInstance == null) return false;
        if($rewardInstance->getStatus() != Reward::Status_Active) return false;
        if($size >= $rewardInstance->getLimitPerDay()) return false;


        $newActivityItem = array();
        $newActivityItem['clientId'] = $this->getClientId();
        $newActivityItem['date'] = $date;
        $newActivityItem['rewardId'] = $rewardId;
        $newActivityItem['targetId'] = $targetId;
        $newActivityItem['regIp'] = $regIp;
        $newActivityItem['regDate'] = Util::getLocalTime();

        $ret = $this->addNew($newActivityItem);

        if($ret){
            $rewardInstance->addTodayActivity();
            $rewardInstance->addTodayPoint();
        }

        return $ret;
    }

    public function addActivity($date, $siteId = 0, $rewardId, $targetId, $regIp){
        if(Util::isInteger($this->getClientId()) != true) return false;
        if($this->getClientInstance()->getStatus() == Client::Status_Inactive || $this->getClientInstance()->getStatus() == Client::Status_Block) return false;
        //if($this->getClientInstance()->getCertificationId() < 1) return false;
        // 임시로 본인인증 패스

        $exist = $this->isActivity($date, $rewardId, $targetId);
        if($exist >= 1){
            // 이미 있음.
            return false;
        }
        $size = $this->countActivity($date, $rewardId);

        // @fixme: 추가 수정 필요...
        // 리워드 아이디가 0일때는 후원하기
        if($rewardId != 0) {
            $rewardInstance = SiteRewardContainer::isItem($rewardId);
            if($rewardInstance == false || $rewardInstance == null) return false;
            if($rewardInstance->getStatus() != SiteReward::Status_Active) return false;
            if($size >= $rewardInstance->getLimit()) return false;
        }

        $newActivityItem = array();
        $newActivityItem['clientId'] = $this->getClientId();
        $newActivityItem['siteId'] = $siteId;
        $newActivityItem['rewardId'] = $rewardId;
        $newActivityItem['date'] = $date;
        $newActivityItem['targetId'] = $targetId;
        $newActivityItem['regIp'] = $regIp;
        $newActivityItem['regDate'] = Util::getLocalTime();

        $ret = $this->addNew($newActivityItem);

        if($ret > 0) {
            return self::getObjectInstanceStatic($ret);
        }
        return false;
    }

    public function isActivity($date, $rewardId, $targetId){
        if(Util::isNumeric($this->getClientId()) != true) return false;
        /*
        $rewardInstance = RewardContainer::isItem($rewardId);
        if($rewardInstance == false || $rewardInstance == null) return false;
        */

        $query = 'SELECT COUNT(id) FROM `' . $this->getTableName() . '` WHERE clientId = ? AND `date` = ? AND rewardId = ? AND targetId = ? ';
        $data = $this->db->query($query, array($this->getClientId(), addslashes($date), $rewardId, $targetId))->fetch();
        $size = $data[0] > 0 ? $data[0] : 0;

        if($size < 1) return 0;
        return $size;
    }

    public function countActivity($date, $rewardId = 0){
        if(Util::isNumeric($this->getClientId()) != true) return false;
        if($this->getClientInstance()->getStatus() != Client::Status_Active) return false;
        //if($this->getClientInstance()->getCertificationId() < 1) return false;
        /*
        $rewardInstance = RewardContainer::isItem($rewardId);
        if($rewardInstance == false || $rewardInstance == null) return false;
        */

        $condition = array($this->getClientId(), addslashes($date));
        $query = 'SELECT COUNT(id) FROM `' . $this->getTableName() . '` WHERE clientId = ? AND `date` = ?';
        if($rewardId >= 1){
            $query .= ' AND rewardId = ? ';
            $condition[] = $rewardId;
        }
        $data = $this->db->query($query, $condition)->fetch();
        $size = $data[0] > 0 ? $data[0] : 0;

        if($size < 1) return 0;
        return $size;
    }

    public function getTodayActivityPoint($rewardId = 0){
        $today = Util::convertTimezone(Util::getDbNow(), 'Asia/Seoul', 'Y-m-d');
        return $this->getActivityPoint($today, $rewardId);
    }

    public function getActivityPoint($date, $rewardId = 0){
        $items = $this->getActivityItems($date, $rewardId);

        $amount = 0;
        foreach ($items as $var){
            $amount = Util::decimalMath($amount, '+', $var->getSiteRewardPoint());
        }

        return $amount;
    }


    public function getTodayActivity($rewardId = 0){
        $today = Util::getLocalTime('Y-m-d');
        return $this->getActivityItems($today, $rewardId);
    }

    public function getActivityItems($date, $rewardId = 0){
        if(Util::isNumeric($this->getClientId()) != true) return array();
        if($date == '') return array();

        $this->setDate($date);
        if(Util::isNumeric($rewardId) == true && $rewardId >= 1) $this->setRewardId($rewardId);

        $this->setListSize(9999);
        return $this->getItems();
    }

    // 월간 통계
    // 4 = 기사 읽기, 2 = 기사 공유
    public function getMonthlyActivity($rewardId=0, $interval=0) {
        if($rewardId == 0) return 0;
        if(Util::isNumeric($this->getClientId()) != true) return 0;

        $db     = DI::getDefault()->getShared('db');
        $query = <<<EOD
            SELECT 
                COUNT(A.`id`) AS `count`
            FROM 
                `ClientActivity` A
                inner join `SiteItemReward` B on B.`id` = A.`rewardId`
                inner join `SiteReward` C on C.`id` = B.`rewardId` 
            WHERE 
                DATE_FORMAT(`date`,'%Y-%m') = DATE_FORMAT(DATE_ADD(NOW(), INTERVAL ? MONTH),'%Y-%m')
                AND A.`clientId` = ?
                AND C.`id` = ?
EOD;
        $condition = array($interval, intval($this->getClientId()), intval($rewardId));
        $data = $db->query($query, $condition)->fetch();
        if(!empty($data)) {
            return $data['count'];
        }
        return 0;
    }

    public function getMonthlyActivityRate($rewardId=0){
        $thisMonthlyActivity = $this->getMonthlyActivity($rewardId);
        $preMonthlyActivity = $this->getMonthlyActivity($rewardId,-1);
        $monthlyActivityRate = '-';
        if($preMonthlyActivity != 0){
            $monthlyActivityRate = round($thisMonthlyActivity/$preMonthlyActivity*100, 2);
        }
        return $monthlyActivityRate;
    }

    // 주간 통계
    public function getWeeklyActivity($rewardId=0, $interval = 0) {
        if($rewardId == 0) return 0;
        if(Util::isNumeric($this->getClientId()) != true) return 0;

        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT 
                COUNT(A.`id`) AS `count`
            FROM 
                `ClientActivity` A
                inner join `SiteItemReward` B on B.`id` = A.`rewardId`
                inner join `SiteReward` C on C.`id` = B.`rewardId` 
            WHERE 
                YEARWEEK(DATE_FORMAT(`date`,'%Y-%m-%d')) = YEARWEEK(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL ? WEEK),'%Y-%m-%d'))
                AND A.`clientId` = ?
                AND C.`id` = ?

EOD;
        $condition = array($interval, $this->getClientId(), $rewardId);
        $data = $db->query($query, $condition)->fetch();
        if(!empty($data)) {
            return $data['count'];
        }
        return 0;
    }

    public function getWeeklyActivityRate($rewardId=0){
        $thisWeeklyActivity = $this->getWeeklyActivity($rewardId);
        $preWeeklyActivity = $this->getWeeklyActivity($rewardId,-1);
        $weeklyActivityRate = '-';
        if($preWeeklyActivity != 0){
            $weeklyActivityRate = round($thisWeeklyActivity/$preWeeklyActivity*100, 2);
        }
        return $weeklyActivityRate;
    }


    // 일간 통계
    public function getDailyActivity($rewardId=0, $interval = 0) {
        if($rewardId == 0) return 0;
        if(Util::isNumeric($this->getClientId()) != true) return 0;

        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT 
                COUNT(A.`id`) AS `count`
            FROM 
                `ClientActivity` A
                inner join `SiteItemReward` B on B.`id` = A.`rewardId`
                inner join `SiteReward` C on C.`id` = B.`rewardId` 
            WHERE 
                DATE_FORMAT(`date`,'%Y-%m-%d') = DATE_FORMAT(DATE_ADD(NOW(), INTERVAL ? DAY),'%Y-%m-%d')
                AND A.`clientId` = ?
                AND C.`id` = ?
EOD;
        $condition = array($interval, $this->getClientId(), $rewardId);
        $data = $db->query($query, $condition)->fetch();
        if(!empty($data)) {
            return $data['count'];
        }
        return 0;
    }

    public function getDailyActivityRate($rewardId=0){
        $thisDailyActivity = $this->getDailyActivity($rewardId);
        $preDailyActivity = $this->getDailyActivity($rewardId,-1);
        $dailyActivityRate = '-';
        if($preDailyActivity != 0){
            $dailyActivityRate = round($thisDailyActivity/$preDailyActivity*100, 2);
        }
        return $dailyActivityRate;
    }




    public function getItemsPBF() {
        $where = array();
        if(Util::isNumeric($this->getClientId()) == true) $where[] = '`clientId` = ' . $this->getClientId();
        if($this->getDate() != '') $where[] = '`date` = "' . $this->getDate() . '"';
        if(Util::isNumeric($this->getRewardId()) == true) $where[] = '`rewardId` = ' . $this->getRewardId();
        return $where;
    }

}

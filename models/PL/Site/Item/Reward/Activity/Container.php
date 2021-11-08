<?php
namespace PL\Models\Site\Item\Reward\Activity;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;

use PL\Models\Site\Reward\Reward as SiteReward;


use PL\Models\Site\Item\Container as SiteItemContainer;
use PL\Models\Site\Item\Reward\Reward;
use PL\Models\Site\Item\Reward\Container as RewardContainer;

use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;


class Container extends AbstractContainer {

    protected $_rewardInstance;
    protected $_rewardId;

    protected $_typeId;

    public function __construct(Reward $rewardInstance = null) {
        parent::__construct(Activity::tableName);
        $this->setTableName(Activity::tableName);

        if(is_null($rewardInstance) == false) {
            $this->setRewardInstance($rewardInstance);
        }
    }

    public static function getTableNameStatic(){
        return Activity::tableName;
    }

    public static function getObjectInstanceStatic($data) : Activity {
        return Activity::getInstance($data);
    }

    public function getObjectInstance($data) : Activity {
        return Activity::getInstance($data);
    }

    public function getRewardInstance(){
        return $this->_rewardInstance;
    }

    public function setRewardInstance(Reward $rewardInstance){
        $this->_rewardInstance = $rewardInstance;
        $this->setRewardId($rewardInstance->getId());
    }

    public function getRewardId(){
        return $this->_rewardId;
    }

    public function setRewardId($rewardId) {
        $this->_rewardId = $rewardId;
    }

    public function _checkActivity($clientId, $date, $identifier){
        return self::checkActivity($clientId, $date, $this->getRewardId(), $identifier);
    }

    public static function checkActivity($clientId, $date, $rewardId, $identifier){

        $query = "SELECT * FROM `" . self::getTableNameStatic() . "` WHERE `clientId` = ? AND `date` = ? AND `rewardId` = ? AND `identifier` = ? LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->get('master/slave db');

        $data = $db->query($query, array($clientId, $date, $rewardId, $identifier))->fetch();

        if(is_array($data) == true) {
            return self::getObjectInstanceStatic($data);
        }
        return null;
    }

    public function countActivity($date, $rewardId = 0, $clientId){
        if(Util::isNumeric($clientId) != true) return false;
        $clientInstance = ClientContainer::isItem($clientId);
        if($clientInstance->getStatus() != Client::Status_Active) return false;

        $condition = array($clientInstance->getId(), addslashes($date));
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

    /**
     * 활동추가(사이트 기준)
     * itemId, rewardId, clientId, date, isReward = 0, url, identifier, regIp, regDate
    */
    public function addActivity($itemId, $rewardId, $clientId, $date, $url, $identifier, $regIp, $type = 0){

        if(Util::isNumeric($clientId) != true) return false;
        $clientInstance = ClientContainer::isItem($clientId);
        if($clientInstance->getStatus() == Client::Status_Inactive || $clientInstance->getStatus() == Client::Status_Block) return false;
        if($clientInstance->getId() != $clientId) return false;

        $exist = $this->checkActivity($clientId, $date, $rewardId, $url);
        if($exist >= 1){
            // 이미 있음.
            return $exist->getId();
        }

        $size = $this->getReceiveRewardCount($clientInstance->getId(), $itemId, Util::convertTimezone(Util::getDbNow(), 'Asia/Seoul', 'Y-m-d'));

        $rewardInstance = RewardContainer::isItem($rewardId);
        if($rewardInstance == false || $rewardInstance == null) return false;
        if($rewardInstance->getStatus() != Reward::Status_Active) return false;
        if($size >= $rewardInstance->getLimit()) return false;

        $itemInstance = SiteItemContainer::isItem($itemId);
        if($itemInstance == false || $itemInstance == null) return false;

        $newActivityItem = array();
        $newActivityItem['itemId'] = $itemInstance->getId();
        $newActivityItem['rewardId'] = $rewardId;
        $newActivityItem['assetId'] = $rewardInstance->getAssetId();
        $newActivityItem['reward'] = $rewardInstance->getReward();
        $newActivityItem['clientId'] = $clientInstance->getId();
        $newActivityItem['date'] = $date;
        if($type == Activity::Type_Referral) {
            // 리퍼럴로 들어온 경우
            $newActivityItem['isReward'] = Activity::Reward_Receive;
        } else {
            $newActivityItem['isReward'] = Activity::Reward_Refuse;
        }
        $newActivityItem['url'] = $url;
        $newActivityItem['identifier'] = $identifier;
        $newActivityItem['regIp'] = $regIp;
        $newActivityItem['regDate'] = Util::getLocalTime();

        $ret = $this->addNew($newActivityItem);

        if($ret != false || $ret != null) {
            return self::getObjectInstanceStatic($ret);
        } else {
            return false;
        }
    }

    public function getLastOne(){
        $this->setOrder('`id` DESC, ');
        $itemInstance = $this->getOne();
        if($itemInstance instanceof Activity == true) return $itemInstance;
        return null;
    }


    public function getRewardsPBF(){
        $where = array();
        if(Util::isInteger($this->getRewardId()) == true) $where[] = '`rewardId` = "' . $this->getRewardId() . '"';
        return $where;
    }

    /**
     * 오늘 활동해서 얻은 point
     */
    public function getTodayPoint($clientId, $date) {
        if($clientId == '') return null;
        if($date == '') return null;

        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT X.* FROM 
            (
                SELECT A.clientId as clientId, SUM(B.reward) as total, A.date as date 
                FROM
                SiteItemRewardActivity A, SiteItemReward B
                WHERE A.rewardId = B.id
                AND A.isReward = ?
                AND A.clientId = ?
                AND A.date = ?
            ) AS X
EOD;
        $var = array(Activity::Reward_Receive, $clientId, $date);
        $data = $db->query($query, $var)->fetch();

        if(!empty($data)) {
            $todayPoint = (int)$data['total'];
        } else {
            $todayPoint = 0;
        }
        return $todayPoint;
    }

    /**
     * 오늘의 전/현 년,월,주,일 기사읽기/기사공유 횟수 반환
     */
    public function getTodayRewardStatistics($siteItemId) {
        if($siteItemId == '') return null;

        $curY = date('Y');
        $curM = date('m');
        $curD = date('d');
        $curW = date('w');

        //년간
        $startPreY = date('Y-01-01', mktime(0, 0, 0, 1, 1, $curY-1));
        $startCurY = $endPreY = date('Y-01-01');
        $endCurY = date('Y-01-01', mktime(0, 0, 0, 1, 1, $curY+1));
        //월간
        $startPreM = date('Y-m-01', mktime(0, 0, 0, $curM-1, 1, $curY));
        $startCurM = $endPreM = date('Y-m-01');
        $endCurM = date('Y-m-01', mktime(0, 0, 0, $curM+1, 1, $curY));
        //주간
        $startPreW = date('Y-m-d', mktime(0, 0, 0, $curM, $curD-$curW-7, $curY));
        $startCurW = $endPreW = date('Y-m-d', mktime(0, 0, 0, $curM, $curD-$curW, $curY));
        $endCurW = date('Y-m-d', mktime(0, 0, 0, $curM, $curD-$curW+7, $curY));
        //일간
        $startPreD = date('Y-m-d', mktime(0, 0, 0, $curM, $curD-1, $curY));
        $startCurD = $endPreD = date('Y-m-d');
        $endCurD = date('Y-m-d', mktime(0, 0, 0, $curM, $curD+1, $curY));
        
        $db     = DI::getDefault()->getShared('db');

        $query  = "
            SELECT 
                SUM( IF (type = 'preYReadCount',`cnt`, 0) ) AS preYReadCount,
                SUM( IF (type = 'preMReadCount',`cnt`, 0) ) AS preMReadCount,
                SUM( IF (type = 'preWReadCount',`cnt`, 0) ) AS preWReadCount,
                SUM( IF (type = 'preDReadCount',`cnt`, 0) ) AS preDReadCount,
                SUM( IF (type = 'curYReadCount',`cnt`, 0) ) AS curYReadCount,
                SUM( IF (type = 'curMReadCount',`cnt`, 0) ) AS curMReadCount,
                SUM( IF (type = 'curWReadCount',`cnt`, 0) ) AS curWReadCount,
                SUM( IF (type = 'curDReadCount',`cnt`, 0) ) AS curDReadCount,
                SUM( IF (type = 'preYReferralCount', `cnt`, 0) ) AS preYReferralCount,
                SUM( IF (type = 'preMReferralCount', `cnt`, 0) ) AS preMReferralCount,
                SUM( IF (type = 'preWReferralCount', `cnt`, 0) ) AS preWReferralCount,
                SUM( IF (type = 'preDReferralCount', `cnt`, 0) ) AS preDReferralCount,
                SUM( IF (type = 'curYReferralCount', `cnt`, 0) ) AS curYReferralCount,
                SUM( IF (type = 'curMReferralCount', `cnt`, 0) ) AS curMReferralCount,
                SUM( IF (type = 'curWReferralCount', `cnt`, 0) ) AS curWReferralCount,
                SUM( IF (type = 'curDReferralCount', `cnt`, 0) ) AS curDReferralCount
            FROM (
                SELECT 'preYReadCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$startPreY  ."' and A.date <  '" .$endPreY  ."'
                UNION
                SELECT 'preMReadCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$startPreM  ."' and A.date <  '" .$endPreM  ."'
                UNION
                SELECT 'preWReadCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$startPreW  ."' and A.date <  '" .$endPreW  ."'
                UNION
                SELECT 'preDReadCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$startPreD  ."' and A.date <  '" .$endPreD  ."'
                UNION
                SELECT 'curYReadCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$startCurY  ."' and A.date <  '" .$endCurY  ."'
                UNION
                SELECT 'curMReadCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$startCurM  ."' and A.date <  '" .$endCurM  ."'
                UNION
                SELECT 'curWReadCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$startCurW  ."' and A.date <  '" .$endCurW  ."'
                UNION
                SELECT 'curDReadCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$startCurD  ."' and A.date <  '" .$endCurD  ."'
                UNION
                SELECT 'preYReferralCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$startPreY  ."' and A.date <  '" .$endPreY  ."'
                UNION
                SELECT 'preMReferralCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$startPreM  ."' and A.date <  '" .$endPreM  ."'
                UNION
                SELECT 'preWReferralCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$startPreW  ."' and A.date <  '" .$endPreW  ."'
                UNION
                SELECT 'preDReferralCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$startPreD  ."' and A.date <  '" .$endPreD  ."'
                UNION
                SELECT 'curYReferralCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$startCurY  ."' and A.date <  '" .$endCurY  ."'
                UNION
                SELECT 'curMReferralCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$startCurM  ."' and A.date <  '" .$endCurM  ."'
                UNION
                SELECT 'curWReferralCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$startCurW  ."' and A.date <  '" .$endCurW  ."'
                UNION
                SELECT 'curDReferralCount' as type, COUNT(A.id) as cnt  FROM SiteItemRewardActivity A JOIN SiteItemReward B ON A.rewardId=B.id WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$startCurD  ."' and A.date <  '" .$endCurD  ."'
            ) A
        ";
        $data = $db->query($query)->fetch();

        if (!empty($data)) return $data;
        else return [
            'preYReadCount'=> 0,
            'preMReadCount'=> 0,
            'preWReadCount'=> 0,
            'preDReadCount'=> 0,
            'curYReadCount'=> 0,
            'curMReadCount'=> 0,
            'curWReadCount'=> 0,
            'curDReadCount'=> 0,
            'preYReferralCount'=>0,
            'preMReferralCount'=>0,
            'preWReferralCount'=>0,
            'preDReferralCount'=>0,
            'curYReferralCount'=>0,
            'curMReferralCount'=>0,
            'curWReferralCount'=>0,
            'curDReferralCount'=>0
        ];
    }

    /**
     * 입력된 기간의 일간 기사읽기,기사공유 리워드 조회
     */
    public function getDailyRewardStatistics($siteItemId, $from, $to) {
        if(empty($siteItemId)) return null;
        if(empty($from)) return null;
        if(empty($to)) return null;

        $db     = DI::getDefault()->getShared('db');
        $query  = "
        SELECT * FROM (
            SELECT 
                A.date,
                'read' as type,
                SUM(A.reward) as rewardAmount
            FROM 
                SiteItemRewardActivity A 
                JOIN SiteItemReward B ON A.rewardId=B.id 
            WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_ArticleView ."' AND A.date >= '" .$from  ."' and A.date <=  '" .$to  ."'
            GROUP BY A.date

            UNION ALL

            SELECT 
                A.date,
                'referral' as type,
                SUM(A.reward) as rewardAmount
            FROM 
                SiteItemRewardActivity A 
                JOIN SiteItemReward B ON A.rewardId=B.id 
            WHERE A.itemId='" .$siteItemId ."' AND A.isReward = 0 AND B.rewardId='" .SiteReward::Type_Referral ."' AND A.date >= '" .$from  ."' and A.date <=  '" .$to  ."'
            GROUP BY A.date
        ) A
        ORDER BY A.Date DESC
        ";
        $data = $db->query($query)->fetchAll();
        return $data;
    }

    public function getReceiveRewardCount($clientId, $itemId, $date) {
        if ($itemId == 0 || $itemId == '') return false;
        if ($clientId == 0 || $clientId == '') return false;
        if ($date == '') return false;

        $db     = DI::getDefault()->getShared('db');
        $query  = "SELECT COUNT(a.Id) as cReward FROM `SiteItemRewardActivity` a WHERE `clientId` = ? AND `itemId` = ? AND `isReward` = ? AND `date` = ? AND `rewardId` = ?";
        $var = array($clientId, $itemId, Activity::Reward_Receive, $date, $this->getRewardId());
        $data = $db->query($query, $var)->fetch();

        if(!empty($data)) {
            $cReward = (int)$data['cReward'];
        } else {
            $cReward = 0;
        }

        return $cReward;

    }

    public function getTop5RewardArticle($siteItemId, $code) {
        if ($code == '') return false;

        $db     = DI::getDefault()->getShared('db');
        $query  = "
            SELECT
                A.`identifier`, C.`title`, C.`host`, C.`path`, C.`author`, SUM(A.`reward`) as rewardSum
            FROM 
                `SiteItemRewardActivity` A
                JOIN `SiteItemReward` B on A.`rewardId` = B.`id` 
                JOIN `SiteItemPage` C on B.`itemId` = C.`itemId` AND A.`identifier`=C.`path`
            WHERE
                A.`itemId` = ?
                AND A.`isReward` = ?
                AND B.`code` = ?
            GROUP BY A.`identifier`
            ORDER BY `rewardSum` DESC
            LIMIT 0, 5
        ";

        $var = array($siteItemId, Activity::Reward_Receive, $code);
        $data = $db->query($query, $var)->fetchAll();
        return $data;
    }
}
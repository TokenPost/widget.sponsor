<?php
namespace PL\Models\Donation\Item\Pay;

use PL\Models\Donation\Item\Pay\Pay;
use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Donation\Item\Item;

class Container extends AbstractContainer {

    protected $_itemId;
    protected $_itemInstance;


    public function __construct(Item $itemInstance = null) {
        parent::__construct(Pay::tableName);
        $this->setTableName(Pay::tableName);

        if($itemInstance != null){
            $this->_itemInstance = $itemInstance;
            $this->_itemId = $itemInstance->getId();
        } else {
            $this->_itemInstance = null;
            $this->_itemId = 0;
        }
    }

    public static function getTableNameStatic(){
        return Pay::tableName;
    }

    public static function getObjectInstanceStatic($date) : Pay {
        return Pay::getInstance($date);
    }

    public function getObjectInstance($date) : Pay {
        return Pay::getInstance($date);
    }


    public function getItemInstance(){
        return $this->_itemInstance;
    }

    public function getItemId(){
        return $this->_itemId;
    }


    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getItemId()) == true) $where[] = '`itemId` = '  . $this->getItemId();

        return $where;
    }

    public function getDailyDonationStatistics($siteItemId, $from, $to) {
        if(empty($siteItemId)) return null;
        if(empty($from)) return null;
        if(empty($to)) return null;

        $db     = DI::getDefault()->getShared('db');
        $query  = "
            SELECT 
                date_format(A.regDate, '%Y-%m-%d') as date,
                SUM(A.quantity) as quantity
            FROM 
                DonationItemPay A 
                JOIN DonationItem B ON A.itemId=B.id 
            WHERE B.targetId='" .$siteItemId ."' AND A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$from  ." 00:00:00' and A.regDate <=  '" .$to  ." 23:59:59'
            GROUP BY date
            ORDER BY date DESC
        ";
        $data = $db->query($query)->fetchAll();
        return $data;
    }

    public function getDonationTotal($siteItemId) {
        if(empty($siteItemId)) return null;

        $db     = DI::getDefault()->getShared('db');
        $query  = "
            SELECT 
                SUM(A.quantity) as quantity
            FROM 
                DonationItemPay A 
                JOIN DonationItem B ON A.itemId=B.id 
            WHERE 
                B.targetId='" .$siteItemId ."'
                AND A.status='" .Pay::Status_Active ."'
        ";
        $data = $db->query($query)->fetch();
        if (!empty($data) && isset($data[0])) return $data[0];
        return null;
    }

    public function getSuperadminSummary() {
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
                SUM( IF (type = 'preYDonation',`cnt`, 0) ) AS preYDonation,
                SUM( IF (type = 'preMDonation',`cnt`, 0) ) AS preMDonation,
                SUM( IF (type = 'preWDonation',`cnt`, 0) ) AS preWDonation,
                SUM( IF (type = 'preDDonation',`cnt`, 0) ) AS preDDonation,
                SUM( IF (type = 'curYDonation',`cnt`, 0) ) AS curYDonation,
                SUM( IF (type = 'curMDonation',`cnt`, 0) ) AS curMDonation,
                SUM( IF (type = 'curWDonation',`cnt`, 0) ) AS curWDonation,
                SUM( IF (type = 'curDDonation',`cnt`, 0) ) AS curDDonation
            FROM (
                SELECT 'preYDonation' as type, SUM(A.quantity) as cnt  FROM DonationItemPay A WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startPreY  ."' and A.regDate <  '" .$endPreY  ."'
                UNION
                SELECT 'preMDonation' as type, SUM(A.quantity) as cnt  FROM DonationItemPay A WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startPreM  ."' and A.regDate <  '" .$endPreM  ."'
                UNION
                SELECT 'preWDonation' as type, SUM(A.quantity) as cnt  FROM DonationItemPay A WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startPreW  ."' and A.regDate <  '" .$endPreW  ."'
                UNION
                SELECT 'preDDonation' as type, SUM(A.quantity) as cnt  FROM DonationItemPay A WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startPreD  ."' and A.regDate <  '" .$endPreD  ."'
                UNION
                SELECT 'curYDonation' as type, SUM(A.quantity) as cnt  FROM DonationItemPay A WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startCurY  ."' and A.regDate <  '" .$endCurY  ."'
                UNION
                SELECT 'curMDonation' as type, SUM(A.quantity) as cnt  FROM DonationItemPay A WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startCurM  ."' and A.regDate <  '" .$endCurM  ."'
                UNION
                SELECT 'curWDonation' as type, SUM(A.quantity) as cnt  FROM DonationItemPay A WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startCurW  ."' and A.regDate <  '" .$endCurW  ."'
                UNION
                SELECT 'curDDonation' as type, SUM(A.quantity) as cnt  FROM DonationItemPay A WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startCurD  ."' and A.regDate <  '" .$endCurD  ."'
            ) A
        ";
        $data = $db->query($query)->fetch();

        $query  = "
            SELECT 
                Count(A.id) as Count
            FROM
                DonationItemPay A
            WHERE A.status='" .Pay::Status_Active ."' AND A.regDate >= '" .$startCurY  ."' and A.regDate <  '" .$endCurY  ."'
        ";
        $countData = $db->query($query)->fetch();
        
        if (!empty($data)) {
            $data['curYDonationCount'] = 0;
            if (!empty($countData)) $data['curYDonationCount'] = $countData['Count'];
            return $data;
        } else return [
            'preYDonation'=> 0,
            'preMDonation'=> 0,
            'preWDonation'=> 0,
            'preDDonation'=> 0,
            'curYDonation'=> 0,
            'curMDonation'=> 0,
            'curWDonation'=> 0,
            'curDDonation'=> 0,
            'curYDonationCount' => 0
        ];
    }
}

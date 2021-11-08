<?php
namespace PL\Models\Site\Item\Point;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Site\Item\Item as SiteItem;
use PL\Models\Site\Item\Container as SiteItemContainer;

use PL\Models\Site\Item\Point\Log\Log as PointLog;
use PL\Models\Site\Item\Point\Log\Container as PointLogContainer;

class Point extends AbstractSingleton {

    const tableName = 'SiteItemPoint';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    const Token_Ppoint = 1;
    const Token_TPC_Legacy   = 1; // decommissioned
    const Token_News         = 2; // decommissioned

    const Token_NewsSatoshi  = 3;
    const Token_NewsKrw      = 4;
    const Token_TPC          = 4;
    const Token_NewsUsd      = 5;
    const Token_EOS          = 6;

    // decimal = 6, 0의 갯수
    // V1이용
    const Decimal_TPCLegacy   = 1000000;
    const Decimal_NEWS        = 1000000;

    // V2 이용
    const Decimal_NewsSatoshi   = 10000;
    const Decimal_PPOINT        = 10; // 10원기준 0.5원의 리워드가 존재.
    const Decimal_TPC           = 10; // 10원기준 0.5원의 리워드가 존재.
    const Decimal_NewsKrw       = 10; // 10원기준 0.5원의 리워드가 존재.
    const Decimal_NewsUsd       = 1000; // 두자리일경우 $0.01 리워드 최소 10원이기때문에 부담스럽다. 3~4자리로 수정

    const ActiveTokenId   = self::Token_NewsSatoshi;
    const ActiveTokenDecimal = self::Decimal_NewsSatoshi;

    const ActiveRewardTokenId   = self::Token_TPC;
    const ActiveRewardTokenDecimal = self::Decimal_TPC;


    protected $_siteItemInstance;
    protected $_siteItemContainer;

    protected $_logContainer;

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (Util::isInteger($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    /* SiteItem instance */
    public function getSiteItemInstance() {
        if (isset($this->_siteItemInstance) == false) {
            $this->_siteItemInstance = SiteItem::getInstance($this->getItemId());
        }
        return $this->_siteItemInstance;
    }

    /* SiteItemPointLog Container */
    public function getLogContainer() {
        if (isset($this->_logContainer) == false) {
            $this->_logContainer = new PointLogContainer($this);
        }
        return $this->_logContainer;
    }



    public function getId()
    {
        return $this->_info['id'];
    }

    public function getSiteItemId()
    {
        return $this->_info['siteItemId'];
    }

    public function setSiteItemId($var)
    {
        $this->_info['siteItemId'] = $var;
        $this->_changes['siteItemId'] = $this->_info['siteItemId'];
    }

    public function getTokenId()
    {
        return $this->_info['tokenId'];
    }

    public function setTokenId($var)
    {
        $this->_info['tokenId'] = $var;
        $this->_changes['tokenId'] = $this->_info['tokenId'];
    }

    public function getLog()
    {
        return $this->_info['log'];
    }

    public function setLog($var)
    {
        $this->_info['log'] = $var;
        $this->_changes['log'] = $this->_info['log'];
    }

    public function getPlus()
    {
        return $this->_info['plus'];
    }

    public function setPlus($var)
    {
        $this->_info['plus'] = $var;
        $this->_changes['plus'] = $this->_info['plus'];
    }

    public function getSubtract()
    {
        return $this->_info['subtract'];
    }

    public function setSubtract($var)
    {
        $this->_info['subtract'] = $var;
        $this->_changes['subtract'] = $this->_info['subtract'];
    }

    public function getPoint()
    {
        return $this->_info['point'];
    }

    public function setPoint($var)
    {
        $this->_info['point'] = $var;
        $this->_changes['point'] = $this->_info['point'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var)
    {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getStatus()
    {
        return $this->_info['status'];
    }

    public function setStatus($var)
    {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function checkMaxDecimal($point){
        if(strpos($point, '.')){
            if(strlen(strchr($point, '.')) > self::Decimal_TPC) return false;
        }
        return true;
    }

    /* add point */
    public function addPoint($point, $log, $comment, $regIp, $requesterType, $requesterId = 0, $approverId = 0, $date = '') {

        if(!($requesterType == PointLog::RequesterType_Self || $requesterType == PointLog::RequesterType_Admin || $requesterType == PointLog::RequesterType_Bot)){
            // requester type error
            return false;
        }

        // bot일수도 있다. id제한 0
        if(is_numeric($requesterId) == false || $requesterId < 0) throw new Exception('Parameter error.');
        if(is_numeric($approverId) == false || $approverId < 0) throw new Exception('Parameter Instance error.');
        if($point == 0) throw new Exception('Api Instance error.');
        if(is_numeric($point) == false) throw new Exception('포인트가 숫자가 아닙니다.');
        if($date == '') {
            $date = Util::getLocalTime('Y-m-d');
        }

        // check decimal point
        if($this->checkMaxDecimal($point) !== true) throw new Exception('허용범위보다 큰 소수자리의 포인트 입니다.');

        if($point > 0){
            // add
            $position = PointLog::Position_Add;
            $point = abs($point);
        } else {
            // subtract
            $position = PointLog::Position_Subtract;

            //check limit
            if(abs($point) > abs($this->getPoint())){
                // 현재값보다 더 많이 삭제하려 한다.
                //return false;
                throw new Exception('삭제하려는 포인트가 현재 포인트보다 작습니다.');
            }
        }

        $before = $this->getPoint();

        if($this->getTokenId() >= self::Token_NewsSatoshi){
            // 소수 버리기 위함.
            $after = round($before + $point);
            $point = $after - $before;
        } else {
            $after = $before + $point;
        }


        $logResult = $this->getLogContainer()->recordLog(PointLog::Type_PPOINT, $position, $point, $before, $after, $log, $comment, $regIp, $requesterType, $requesterId, $approverId, $date);
        if($logResult == false || $logResult == null){
            // log기록 실패
            throw new Exception('Log record fail');
        }

        $this->setPoint($after);
        $this->addLog();
        if($position == PointLog::Position_Add){
            $this->addPlus();
        } else {
            $this->addSubtract();
        }
        $this->saveChanges();

        return true;

    }

    public function addLog() {
        $this->db->query('UPDATE ' . self::getTableNameStatic() . ' set `log` = `log`+1 WHERE id = '. $this->getId());
    }

    public function addPlus() {
        $this->db->query('UPDATE ' . self::getTableNameStatic() . ' set `plus` = `plus`+1 WHERE id = '. $this->getId());
    }

    public function subtractPlus() {
        $this->db->query('UPDATE ' . self::getTableNameStatic() . ' set `plus` = `plus`-1 WHERE id = '. $this->getId());
    }

    public function addSubtract() {
        $this->db->query('UPDATE ' . self::getTableNameStatic() . ' set `subtract` = `subtract`+1 WHERE id = '. $this->getId());
    }

    public function subtractSubtract() {
        $this->db->query('UPDATE ' . self::getTableNameStatic() . ' set `subtract` = `subtract`-1 WHERE id = '. $this->getId());
    }



}
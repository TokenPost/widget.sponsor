<?php
namespace PL\Models\Site\Reward;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Site;
use PL\Models\Client\Client;

class Reward extends AbstractSingleton {

    const tableName = 'SiteReward';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    // 리워드 타입
    const Type_Cert         = 1;    // 본인인증
    const Type_Referral     = 2;    // 공유하기
    const Type_Login        = 3;    // 로그인
    const Type_ArticleView  = 4;    // 기사 읽기
    const Type_Comment      = 5;    // 댓글 작성
    const Type_Share        = 6;    // X
    const Type_Like         = 7;    // 좋아요
    const Type_BoardPost    = 8;    // 게시판 글쓰기

    protected $_siteInstance;

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (is_numeric($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getSiteInstance() {
        if (isset($this->_siteInstance) == false) {
            $this->_siteInstance = Site::getInstance($this->getSiteId());
        }
        return $this->_siteInstance;
    }

    public function getId()
    {
        return $this->_info['id'];
    }

    public function getSiteId()
    {
        return $this->_info['siteId'];
    }

    public function setSiteId($var)
    {
        $this->_info['siteId'] = $var;
        $this->_changes['siteId'] = $this->_info['siteId'];
    }

    public function getTitle()
    {
        return $this->_info['title'];
    }

    public function setTitle($var)
    {
        $this->_info['title'] = $var;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getCode()
    {
        return $this->_info['code'];
    }

    public function setCode($var)
    {
        $this->_info['code'] = $var;
        $this->_changes['code'] = $this->_info['code'];
    }

    public function getTranslateId()
    {
        return $this->_info['translateId'];
    }

    public function setTranslateId($var)
    {
        $this->_info['translateId'] = $var;
        $this->_changes['translateId'] = $this->_info['translateId'];
    }

    public function getTodayActivity()
    {
        return $this->_info['todayActivity'];
    }

    public function setTodayActivity($var)
    {
        $this->_info['todayActivity'] = $var;
        $this->_changes['todayActivity'] = $this->_info['todayActivity'];
    }

    public function getTotalActivity()
    {
        return $this->_info['totalActivity'];
    }

    public function setTotalActivity($var)
    {
        $this->_info['totalActivity'] = $var;
        $this->_changes['totalActivity'] = $this->_info['totalActivity'];
    }

    public function getTodayClient()
    {
        return $this->_info['todayClient'];
    }

    public function setTodayClient($var)
    {
        $this->_info['todayClient'] = $var;
        $this->_changes['todayClient'] = $this->_info['todayClient'];
    }

    public function getTotalClient()
    {
        return $this->_info['totalClient'];
    }

    public function setTotalClient($var)
    {
        $this->_info['totalClient'] = $var;
        $this->_changes['totalClient'] = $this->_info['totalClient'];
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

    public function getLimit()
    {
        return $this->_info['limit'];
    }

    public function setLimit($var)
    {
        $this->_info['limit'] = $var;
        $this->_changes['limit'] = $this->_info['limit'];
    }

    public function getLimitPerDay()
    {
        return $this->_info['limitPerDay'];
    }

    public function setLimitPerDay($var)
    {
        $this->_info['limitPerDay'] = $var;
        $this->_changes['limitPerDay'] = $this->_info['limitPerDay'];
    }

    public function getTodayPoint()
    {
        return $this->_info['todayPoint'];
    }

    public function setTodayPoint($var)
    {
        $this->_info['todayPoint'] = $var;
        $this->_changes['todayPoint'] = $this->_info['todayPoint'];
    }

    public function getTotalPoint()
    {
        return $this->_info['totalPoint'];
    }

    public function setTotalPoint($var)
    {
        $this->_info['totalPoint'] = $var;
        $this->_changes['totalPoint'] = $this->_info['totalPoint'];
    }

    public function getModId()
    {
        return $this->_info['modId'];
    }

    public function setModId($var)
    {
        $this->_info['modId'] = $var;
        $this->_changes['modId'] = $this->_info['modId'];
    }

    public function getModIp()
    {
        return $this->_info['modIp'];
    }

    public function setModIp($var)
    {
        $this->_info['modIp'] = $var;
        $this->_changes['modIp'] = $this->_info['modIp'];
    }

    public function getModDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['modDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setModDate($var)
    {
        $this->_info['modDate'] = $var;
        $this->_changes['modDate'] = $this->_info['modDate'];
    }

    public function getRegIp()
    {
        return $this->_info['regIp'];
    }

    public function setRegIp($var)
    {
        $this->_info['regIp'] = $var;
        $this->_changes['regIp'] = $this->_info['regIp'];
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



}
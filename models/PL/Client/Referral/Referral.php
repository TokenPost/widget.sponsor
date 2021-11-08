<?php
namespace PL\Models\Client\Referral;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Client\Client;


class Referral extends AbstractSingleton {

    const tableName = 'ClientReferral';

    const Level_Bronze    = 1;

    protected $_refContainer;

    protected $_clientInstance;

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

    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getId()
    {
        return $this->_info['id'];
    }

    public function getClientId()
    {
        return $this->_info['clientId'];
    }

    public function setClientId($var)
    {
        $this->_info['clientId'] = $var;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getReferralCode()
    {
        return $this->_info['referralCode'];
    }

    public function setReferralCode($var)
    {
        $this->_info['referralCode'] = $var;
        $this->_changes['referralCode'] = $this->_info['referralCode'];
    }

    public function getCount()
    {
        return $this->_info['count'];
    }

    public function setCount($var)
    {
        $this->_info['count'] = $var;
        $this->_changes['count'] = $this->_info['count'];
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

}
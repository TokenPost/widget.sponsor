<?php
namespace PL\Models\Client\Temp;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Util\Util;


class Temp extends AbstractSingleton {

    const tableName = 'ClientTemp';

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (Util::isInteger($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . addslashes($keyIndex) . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getId()
    {
        return $this->_info['id'];
    }

    public function getCi()
    {
        return $this->_info['ci'];
    }

    public function setCi($var)
    {
        $this->_info['ci'] = $var;
        $this->_changes['ci'] = $this->_info['ci'];
    }

    public function getDi()
    {
        return $this->_info['di'];
    }

    public function setDi($var)
    {
        $this->_info['di'] = $var;
        $this->_changes['di'] = $this->_info['di'];
    }

    public function getSecret()
    {
        return $this->_info['secret'];
    }

    public function setSecret($var)
    {
        $this->_info['secret'] = $var;
        $this->_changes['secret'] = $this->_info['secret'];
    }

    public function getName()
    {
        return $this->_info['name'];
    }

    public function setName($var)
    {
        $this->_info['name'] = $var;
        $this->_changes['name'] = $this->_info['name'];
    }

    public function getPhone()
    {
        // 휴대전화 정규식
        $ptn = '/^(010|011|016|017|018|019)-[^0][0-9]{3,4}-[0-9]{4}/';
        $phone = preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/","\\1-\\2-\\3", $this->_info['phone']);
        return $phone;
    }

    public function setPhone($var)
    {
        $this->_info['phone'] = $var;
        $this->_changes['phone'] = $this->_info['phone'];
    }

    public function getBirth()
    {
        return $this->_info['birth'];
    }

    public function setBirth($var)
    {
        $this->_info['birth'] = $var;
        $this->_changes['birth'] = $this->_info['birth'];
    }

    public function getGender()
    {
        return $this->_info['gender'];
    }

    public function setGender($var)
    {
        $this->_info['gender'] = $var;
        $this->_changes['gender'] = $this->_info['gender'];
    }

    public function getEmail()
    {
        return $this->_info['email'];
    }

    public function setEmail($var)
    {
        $this->_info['email'] = $var;
        $this->_changes['email'] = $this->_info['email'];
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
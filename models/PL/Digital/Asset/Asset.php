<?php
namespace PL\Models\Digital\Asset;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Digital\Digital;
use PL\Models\Digital\Container as DigitalContainer;

use PL\Models\Util\Util;

class Asset extends AbstractSingleton {

    const tableName = 'DigitalAsset';

    const Asset_NKRW = 1; // decommissioned
    const Asset_PPOINT = 1;
    const Asset_NEWS   = 2;

    protected $_digitalInstance;
    protected $_pricePairInstance;

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

    public function getDigitalInstance() {
        if (isset($this->_digitalInstance) == false) {
            $this->_digitalInstance = DigitalContainer::isItem($this->getDigitalId());
        }
        return $this->_digitalInstance;
    }

    public function getItemInstance() {
        if (isset($this->_pricePairInstance) == false) {
            $this->_pricePairInstance = Container::isItem($this->getPricePairId());
        }
        return $this->_pricePairInstance;
    }


    public function getId()
    {
        return $this->_info['id'];
    }

    public function getDigitalId()
    {
        return $this->_info['digitalId'];
    }

    public function setDigitalId($var)
    {
        $this->_info['digitalId'] = $var;
        $this->_changes['digitalId'] = $this->_info['digitalId'];
    }

    public function getTypeId()
    {
        return $this->_info['typeId'];
    }

    public function setTypeId($var)
    {
        $this->_info['typeId'] = $var;
        $this->_changes['typeId'] = $this->_info['typeId'];
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

    public function getTitleEnglish()
    {
        return $this->_info['titleEnglish'];
    }

    public function setTitleEnglish($var)
    {
        $this->_info['titleEnglish'] = $var;
        $this->_changes['titleEnglish'] = $this->_info['titleEnglish'];
    }

    public function getUnit()
    {
        return $this->_info['unit'];
    }

    public function setUnit($var)
    {
        $this->_info['unit'] = $var;
        $this->_changes['unit'] = $this->_info['unit'];
    }

    public function getDecimals()
    {
        return $this->_info['decimals'];
    }

    public function setDecimals($var)
    {
        $this->_info['decimals'] = $var;
        $this->_changes['decimals'] = $this->_info['decimals'];
    }

    public function getHex()
    {
        return $this->_info['hex'];
    }

    public function setHex($var)
    {
        $this->_info['hex'] = $var;
        $this->_changes['hex'] = $this->_info['hex'];
    }

    public function getPriceKrw()
    {
        return $this->_info['priceKrw'];
    }

    public function setPriceKrw($var)
    {
        $this->_info['priceKrw'] = $var;
        $this->_changes['priceKrw'] = $this->_info['priceKrw'];
    }

    public function getPriceUsd()
    {
        return $this->_info['priceUsd'];
    }

    public function setPriceUsd($var)
    {
        $this->_info['priceUsd'] = $var;
        $this->_changes['priceUsd'] = $this->_info['priceUsd'];
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

    public function getTodayReward()
    {
        return $this->_info['todayReward'];
    }

    public function setTodayReward($var)
    {
        $this->_info['todayReward'] = $var;
        $this->_changes['todayReward'] = $this->_info['todayReward'];
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

    public function getTotalReward()
    {
        return $this->_info['totalReward'];
    }

    public function setTotalReward($var)
    {
        $this->_info['totalReward'] = $var;
        $this->_changes['totalReward'] = $this->_info['totalReward'];
    }

    public function getPricePairId()
    {
        return $this->_info['pricePairId'];
    }

    public function setPricePairId($var)
    {
        $this->_info['pricePairId'] = $var;
        $this->_changes['pricePairId'] = $this->_info['pricePairId'];
    }

    public function getUseWithdrawal()
    {
        return $this->_info['useWithdrawal'];
    }

    public function setUseWithdrawal($var)
    {
        $this->_info['useWithdrawal'] = $var;
        $this->_changes['useWithdrawal'] = $this->_info['useWithdrawal'];
    }

    public function getUseReceive()
    {
        return $this->_info['useReceive'];
    }

    public function setUseReceive($var)
    {
        $this->_info['useReceive'] = $var;
        $this->_changes['useReceive'] = $this->_info['useReceive'];
    }

    public function getUseInternal()
    {
        return $this->_info['useInternal'];
    }

    public function setUseInternal($var)
    {
        $this->_info['useInternal'] = $var;
        $this->_changes['useInternal'] = $this->_info['useInternal'];
    }

    public function getAvailable()
    {
        return $this->_info['available'];
    }

    public function setAvailable($var)
    {
        $this->_info['available'] = $var;
        $this->_changes['available'] = $this->_info['available'];
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
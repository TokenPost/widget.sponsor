<?php
namespace PL\Models\Site\Item\Point\Swap;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;

use PL\Models\Admin\Admin;
use PL\Models\Admin\Container as AdminContainer;

use PL\Models\Site\Item\Item as SiteItem;
use PL\Models\Site\Item\Container as SiteItemContainer;

use PL\Models\Site\Item\Point\Point as SiteItemPoint;
use PL\Models\Site\Item\Point\Container as SiteItemPointContainer;


class Swap extends AbstractSingleton {


    const tableName = 'SiteItemPointSwap';

    protected $_clientInstance;
    protected $_siteItemInstance;

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


    public function getSiteItemInstance() {
        if (isset($this->_siteItemInstance) == false) {
            $this->_siteItemInstance = SiteItemContainer::isItem($this->getItemId());
        }
        return $this->_siteItemInstance;
    }

    public function getFromTokenUnit(){
        switch ($this->getFromTokenId()){
            case SiteItemPoint::Token_TPC_Legacy:
            case SiteItemPoint::Token_TPC:
            case SiteItemPoint::Token_NewsKrw:
                return 'tpc';
                break;
            case SiteItemPoint::Token_News:
            case SiteItemPoint::Token_NewsSatoshi:
                return 'news';
                break;
            default:
                return 'Unit';
                break;
        }
    }

    public function getFromTokenTitle(){
        switch ($this->getFromTokenId()){
            case SiteItemPoint::Token_TPC_Legacy:
            case SiteItemPoint::Token_TPC:
            case SiteItemPoint::Token_NewsKrw:
                return 'TPC';
                break;
            case SiteItemPoint::Token_News:
            case SiteItemPoint::Token_NewsSatoshi:
                return 'NEWS';
                break;
            default:
                return 'Unknown Token';
                break;
        }
    }


    public function getToTokenUnit(){
        switch ($this->getToTokenId()){
            case SiteItemPoint::Token_TPC_Legacy:
            case SiteItemPoint::Token_TPC:
            case SiteItemPoint::Token_NewsKrw:
                return 'tpc';
                break;
            case SiteItemPoint::Token_News:
            case SiteItemPoint::Token_NewsSatoshi:
                return 'news';
                break;
            default:
                return 'Unit';
                break;
        }
    }

    public function getToTokenTitle(){
        switch ($this->getToTokenId()){
            case SiteItemPoint::Token_TPC_Legacy:
            case SiteItemPoint::Token_TPC:
            case SiteItemPoint::Token_NewsKrw:
                return 'TPC';
                break;
            case SiteItemPoint::Token_News:
            case SiteItemPoint::Token_NewsSatoshi:
                return 'NEWS';
                break;
            default:
                return 'Unknown Token';
                break;
        }
    }



    public function getId()
    {
        return $this->_info['id'];
    }

    public function getItemId()
    {
        return $this->_info['itemId'];
    }

    public function setItemId($var)
    {
        $this->_info['itemId'] = $var;
        $this->_changes['itemId'] = $this->_info['itemId'];
    }

    public function getFromTokenId()
    {
        return $this->_info['fromTokenId'];
    }

    public function setFromTokenId($var)
    {
        $this->_info['fromTokenId'] = $var;
        $this->_changes['fromTokenId'] = $this->_info['fromTokenId'];
    }

    public function getFromTokenQuantity()
    {
        return $this->_info['fromTokenQuantity'];
    }

    public function setFromTokenQuantity($var)
    {
        $this->_info['fromTokenQuantity'] = $var;
        $this->_changes['fromTokenQuantity'] = $this->_info['fromTokenQuantity'];
    }

    public function getToTokenId()
    {
        return $this->_info['toTokenId'];
    }

    public function setToTokenId($var)
    {
        $this->_info['toTokenId'] = $var;
        $this->_changes['toTokenId'] = $this->_info['toTokenId'];
    }

    public function getToTokenQuantity()
    {
        return $this->_info['toTokenQuantity'];
    }

    public function setToTokenQuantity($var)
    {
        $this->_info['toTokenQuantity'] = $var;
        $this->_changes['toTokenQuantity'] = $this->_info['toTokenQuantity'];
    }

    public function getPrice()
    {
        return $this->_info['price'];
    }

    public function setPrice($var)
    {
        $this->_info['price'] = $var;
        $this->_changes['price'] = $this->_info['price'];
    }

    public function getBasePrice()
    {
        return $this->_info['basePrice'];
    }

    public function setBasePrice($var)
    {
        $this->_info['basePrice'] = $var;
        $this->_changes['basePrice'] = $this->_info['basePrice'];
    }

    public function getSpread()
    {
        return $this->_info['spread'];
    }

    public function setSpread($var)
    {
        $this->_info['spread'] = $var;
        $this->_changes['spread'] = $this->_info['spread'];
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

    public function getRegTimestamp($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['regTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegTimestamp($var)
    {
        $this->_info['regTimestamp'] = $var;
        $this->_changes['regTimestamp'] = $this->_info['regTimestamp'];
    }

    public function getDisplayDate($format = 'Y-m-d') {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function getFromTokenQuantityWithNumberFormat($n) {
        return round(number_format($this->_info['fromTokenQuantity'], $n), $n);
    }

    public function getToTokenQuantityWithNumberFormat($n) {
        return round(number_format($this->_info['toTokenQuantity'], $n), $n);
    }
}
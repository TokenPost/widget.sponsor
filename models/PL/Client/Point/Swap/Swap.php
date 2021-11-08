<?php
namespace PL\Models\Client\Point\Swap;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Exchange\Token\Token as ExchangeToken;
use PL\Models\Exchange\Token\Container as ExchangeTokenContainer;

use PL\Models\Admin\Admin;
use PL\Models\Admin\Container as AdminContainer;
use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;
use PL\Models\Client\Point\Point as ClientPoint;
use PL\Models\Client\Point\Transaction\Log\Container as TransactionLogContainer;


class Swap extends AbstractSingleton {


    const tableName = 'ClientPointSwap';

    protected $_clientInstance;

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


    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = ClientContainer::isItem($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getFromTokenUnit(){
        switch ($this->getFromTokenId()){
            case ClientPoint::Token_TPC_Legacy:
            case ClientPoint::Token_TPC:
            case ClientPoint::Token_NewsKrw:
                return 'tpc';
                break;
            case ClientPoint::Token_News:
            case ClientPoint::Token_NewsSatoshi:
                return 'news';
                break;
            default:
                return 'Unit';
                break;
        }
    }

    public function getFromTokenTitle(){
        switch ($this->getFromTokenId()){
            case ClientPoint::Token_TPC_Legacy:
            case ClientPoint::Token_TPC:
            case ClientPoint::Token_NewsKrw:
                return 'TPC';
                break;
            case ClientPoint::Token_News:
            case ClientPoint::Token_NewsSatoshi:
                return 'NEWS';
                break;
            default:
                return 'Unknown Token';
                break;
        }
    }


    public function getToTokenUnit(){
        switch ($this->getToTokenId()){
            case ClientPoint::Token_TPC_Legacy:
            case ClientPoint::Token_TPC:
            case ClientPoint::Token_NewsKrw:
                return 'tpc';
                break;
            case ClientPoint::Token_News:
            case ClientPoint::Token_NewsSatoshi:
                return 'news';
                break;
            default:
                return 'Unit';
                break;
        }
    }

    public function getToTokenTitle(){
        switch ($this->getToTokenId()){
            case ClientPoint::Token_TPC_Legacy:
            case ClientPoint::Token_TPC:
            case ClientPoint::Token_NewsKrw:
                return 'TPC';
                break;
            case ClientPoint::Token_News:
            case ClientPoint::Token_NewsSatoshi:
                return 'NEWS';
                break;
            default:
                return 'Unknown Token';
                break;
        }
    }



    public function getId() {
        return $this->_info['id'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($var) {
        $this->_info['clientId'] = $var;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getFromTokenId() {
        return $this->_info['fromTokenId'];
    }

    public function setFromTokenId($var) {
        $this->_info['fromTokenId'] = $var;
        $this->_changes['fromTokenId'] = $this->_info['fromTokenId'];
    }

    public function getFromTokenQuantity() {
        return $this->_info['fromTokenQuantity'];
    }

    public function setFromTokenQuantity($var) {
        $this->_info['fromTokenQuantity'] = $var;
        $this->_changes['fromTokenQuantity'] = $this->_info['fromTokenQuantity'];
    }

    public function getToTokenId() {
        return $this->_info['toTokenId'];
    }

    public function setToTokenId($var) {
        $this->_info['toTokenId'] = $var;
        $this->_changes['toTokenId'] = $this->_info['toTokenId'];
    }

    public function getToTokenQuantity() {
        return $this->_info['toTokenQuantity'];
    }

    public function setToTokenQuantity($var) {
        $this->_info['toTokenQuantity'] = $var;
        $this->_changes['toTokenQuantity'] = $this->_info['toTokenQuantity'];
    }

    public function getPrice() {
        return $this->_info['price'];
    }

    public function setPrice($var) {
        $this->_info['price'] = $var;
        $this->_changes['price'] = $this->_info['price'];
    }

    public function getBasePrice() {
        return $this->_info['basePrice'];
    }

    public function setBasePrice($var) {
        $this->_info['basePrice'] = $var;
        $this->_changes['basePrice'] = $this->_info['basePrice'];
    }

    public function getSprad() {
        return $this->_info['sprad'];
    }

    public function setSprad($var) {
        $this->_info['sprad'] = $var;
        $this->_changes['sprad'] = $this->_info['sprad'];
    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function setRegIp($var) {
        $this->_info['regIp'] = $var;
        $this->_changes['regIp'] = $this->_info['regIp'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var) {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getRegTimestamp($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegTimestamp($var) {
        $this->_info['regTimestamp'] = $var;
        $this->_changes['regTimestamp'] = $this->_info['regTimestamp'];
    }

    public function getDisplayDate($format = 'Y-m-d') {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function getFromTokenQuantityWithNumberFormat($n) {
        return number_format($this->_info['fromTokenQuantity'], $n);
    }

    public function getToTokenQuantityWithNumberFormat($n) {
        return number_format($this->_info['toTokenQuantity'], $n);
    }
}
<?php
namespace PL\Models\Site\Item\Point\Address;

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

use PL\Models\Site\Item\Point\Transaction\Transaction;
use PL\Models\Site\Item\Point\Transaction\Container as PointTransactionContainer;

use PL\Models\Exchange\Token\Token;


class Address extends AbstractSingleton {

    const tableName = 'SiteItemPointAddress';

    const Status_Active   = 0;
    const Status_Inactive = 1;
    const Status_CreateInitialize = 2;
    const Status_CreateProgress   = 3;
    const Status_CreateFailure    = 4;

    const TokenType_Internal = 1;
    const TokenType_External = 2;

    const Ethereum_Deposit_Version = 1;

    const Luniverse_Deposit_Version = 1;

    // networId는 transantion


    protected $_clientInstance;
    protected $_tokenInstance;

    protected $_adminInstance;
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


    /**
     * 내/외부용을 따로하게되면 사람들이 헷갈려할수 있다.
     */
    public static function getDefaultTokenAddress($tokenTypeId, $tokenId, $platformId){
        if(APPLICATION_ENV == 'operation'){
            // 입출금용 주소
            $eosDefault = 'hz13svqnex3p';
        } else {
            //$eosDefault = 'tpdevtest123';
            $eosDefault = 'tpdevtestno1';
        }

        $data[self::TokenType_Internal][SiteItemPoint::Token_News][Transaction::Platform_Internal] = $eosDefault; // eos 실제 사용주소.
        $data[self::TokenType_Internal][SiteItemPoint::Token_TPC][Transaction::Platform_Internal] = $eosDefault;

        $data[self::TokenType_External][SiteItemPoint::Token_News][Transaction::Platform_Eos] = $eosDefault;
        $data[self::TokenType_External][SiteItemPoint::Token_NewsSatoshi][Transaction::Platform_Eos] = $eosDefault;

        if(isset($data[$tokenTypeId][$tokenId][$platformId]) == true) return $data[$tokenTypeId][$tokenId][$platformId];

        return '';
    }

    /* SiteItem Instance */
    public function getSiteItemInstance() {
        if (isset($this->_siteItemInstance) == false) {
            $this->_siteItemInstance = SiteItem::getInstance($this->getItemId());
        }
        return $this->_siteItemInstance;
    }


    public function getId()
    {
        return $this->_info['id'];
    }
    public function setId($var)
    {
        $this->_info['id'] = $var;
        $this->_changes['id'] = $this->_info['id'];
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

    public function getTokenTypeId()
    {
        return $this->_info['tokenTypeId'];
    }

    public function setTokenTypeId($var)
    {
        $this->_info['tokenTypeId'] = $var;
        $this->_changes['tokenTypeId'] = $this->_info['tokenTypeId'];
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

    public function getPlatformId()
    {
        return $this->_info['platformId'];
    }

    public function setPlatformId($var)
    {
        $this->_info['platformId'] = $var;
        $this->_changes['platformId'] = $this->_info['platformId'];
    }

    public function getVersion()
    {
        return $this->_info['version'];
    }

    public function setVersion($var)
    {
        $this->_info['version'] = $var;
        $this->_changes['version'] = $this->_info['version'];
    }

    public function getNonce()
    {
        return $this->_info['nonce'];
    }

    public function setNonce($var)
    {
        $this->_info['nonce'] = $var;
        $this->_changes['nonce'] = $this->_info['nonce'];
    }

    public function getAddress()
    {
        return $this->_info['address'];
    }

    public function setAddress($var)
    {
        $this->_info['address'] = $var;
        $this->_changes['address'] = $this->_info['address'];
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

    public function getPrivateKey()
    {
        return $this->_info['privateKey'];
    }

    public function setPrivateKey($var)
    {
        $this->_info['privateKey'] = $var;
        $this->_changes['privateKey'] = $this->_info['privateKey'];
    }

    public function getResult()
    {
        return $this->_info['result'];
    }

    public function setResult($var)
    {
        $this->_info['result'] = $var;
        $this->_changes['result'] = $this->_info['result'];
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
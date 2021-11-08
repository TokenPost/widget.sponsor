<?php
namespace PL\Models\Client\Point\Address;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Client\Client;
use PL\Models\Exchange\Token\Token;
use PL\Models\Client\Point\Point;
use PL\Models\Client\Point\Transaction\Transaction;

class Address extends AbstractSingleton {

    const tableName = 'ClientPointAddress';

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

        $data[self::TokenType_Internal][Point::Token_News][Transaction::Platform_Internal] = $eosDefault; // eos 실제 사용주소.
        $data[self::TokenType_Internal][Point::Token_TPC][Transaction::Platform_Internal] = $eosDefault;

        $data[self::TokenType_External][Point::Token_News][Transaction::Platform_Eos] = $eosDefault;
        $data[self::TokenType_External][Point::Token_NewsSatoshi][Transaction::Platform_Eos] = $eosDefault;

        if(isset($data[$tokenTypeId][$tokenId][$platformId]) == true) return $data[$tokenTypeId][$tokenId][$platformId];

        return '';
    }


    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
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

    public function getTokenId() {
        return $this->_info['tokenId'];
    }

    public function setTokenId($var) {
        $this->_info['tokenId'] = $var;
        $this->_changes['tokenId'] = $this->_info['tokenId'];
    }

    public function getPlatformIdId() {
        return $this->_info['platformId'];
    }

    public function setPlatformId($var) {
        $this->_info['platformId'] = $var;
        $this->_changes['platformId'] = $this->_info['platformId'];
    }

    public function getVersion() {
        return $this->_info['version'];
    }

    public function setVersion($var) {
        $this->_info['version'] = $var;
        $this->_changes['version'] = $this->_info['version'];
    }

    public function getNonce() {
        return $this->_info['nonce'];
    }

    public function setNonce($var) {
        $this->_info['nonce'] = $var;
        $this->_changes['nonce'] = $this->_info['nonce'];
    }

    public function getAddress() {
        return $this->_info['address'];
    }

    public function setAddress($var) {
        $this->_info['address'] = $var;
        $this->_changes['address'] = $this->_info['address'];
    }

    public function getDisplaySecret() {
        return Util::strInsertPattern($this->getSecret(), '-', 4);
    }

    public function getSecret() {
        return $this->_info['secret'];
    }

    public function setSecret($var) {
        $this->_info['secret'] = $var;
        $this->_changes['secret'] = $this->_info['secret'];
    }

    public function getPrivateKey() {
        return $this->_info['privateKey'];
    }

    public function setPrivateKey($var) {
        $this->_info['privateKey'] = $var;
        $this->_changes['privateKey'] = $this->_info['privateKey'];
    }

    public function getResult() {
        return $this->_info['result'];
    }

    public function setResult($var) {
        $this->_info['result'] = $var;
        $this->_changes['result'] = $this->_info['result'];
    }

    public function getRegId() {
        return $this->_info['regId'];
    }

    public function setRegId($var) {
        $this->_info['regId'] = $var;
        $this->_changes['regId'] = $this->_info['regId'];
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

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($var) {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function getStatusName() {
        switch ($this->_info['status']) {
            case self::Status_Complete:
                return 'complete';
                break;
            case self::Status_Pending:
                return 'pending';
                break;
            case self::Status_Approved:
                return 'approved';
                break;
            case self::Status_Error:
                return 'error';
                break;
            case self::Status_Cancel:
                return 'cancel';
                break;
            default:
                return '';
        }
    }

}
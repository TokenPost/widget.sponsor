<?php
namespace PL\Models\Client\Certification\Token;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;
use PL\Models\Client\Certification\Certification;
use PL\Models\Client\Client;

class Container extends AbstractContainer {


    protected $_client;

    public function __construct() {
        parent::__construct(Token::tableName);
        $this->setTableName(Token::tableName);
    }

    public static function getTableNameStatic(){
        return Token::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Token::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Token::getInstance($date);
    }



    public function setClient($client){
        $this->_client = $client;
    }

    public function getClient(){
        return $this->_client;
    }

    public function getClientId(){
        return $this->getClient()->getId();
    }


    public static function isTokenSecret($token, $secret){
        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `token` = ? AND `secret` = ? LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->get('master/slave db');
        $data = $db->query($query, array($token, $secret))->fetch();

        if(is_array($data) == true) {
            return self::getObjectInstanceStatic($data);
        }
        return false;
    }

    public static function isToken($token) {
        return self::isItem($token, 'token');
    }

    public static function issueToken($regIp, $clientId = 0) : Token {
        //$currentTime = Util::convertTimezone(Util::getDbNow(), SITE_TIMEZONE,'YmdHis');
        $currentTime = Util::getLocalTime('YmdHis');

        //$currentTime = date('YmdHis');
        $RandNumber = rand(100000, 999999);

        //요청 번호 생성
        $token = $currentTime.$RandNumber;
        // check exist
        $existInstance = self::isToken($token);
        if($existInstance == false || $existInstance == null){
            // 기존에 존재하지 않음.

            // insert
            $newItem['clientId'] = $clientId;
            $newItem['certificationId'] = 0;
            $newItem['token'] = $token;
            $newItem['secret'] = substr(md5(microtime()), -10) . substr(md5(microtime()), -10);
            $newItem['regIp'] = $regIp;
            $newItem['regDate'] = Util::getDbNow();
            $newItem['status'] = Certification::Status_Pending;

            $db = DI::getDefault()->getShared('db_master');
            $result = $db->insert(Token::tableName, array_values($newItem), array_keys($newItem));

            if ($result == true) {
                $tokenInstance = Token::getInstance($db->lastInsertId());
            } else {
                // 실패 재귀
                return self::issueToken($regIp, $clientId);
                //return false;
            }

            return $tokenInstance;
        } else {
            // 기존에 존재함. 재귀
            return self::issueToken($regIp, $clientId);
        }
    }
}

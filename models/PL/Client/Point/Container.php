<?php
namespace PL\Models\Client\Point;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use Phalcon\Http\Client\Provider\Curl as Curl;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Client\Client;
use PL\Models\Source\Source;
use PL\Models\Util\Util;

class Container extends AbstractContainer {

    protected $_tokenId;
    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(Point::tableName);
        $this->setTableName(Point::tableName);
    }

    public static function getTableNameStatic(){
        return Point::tableName;
    }

    public static function getObjectInstanceStatic($date) : Point {
        return Point::getInstance($date);
    }

    public function getObjectInstance($date) : Point {
        return Point::getInstance($date);
    }


    public function getClientId(){
        return $this->_clientId;
    }

    private function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance){
        $this->_clientInstance = $clientInstance;
        $this->setClientId($clientInstance->getId());
    }



    public function getTokenId(){
        return $this->_tokenId;
    }

    public function setTokenId($tokenId){
        $this->_tokenId = $tokenId;
    }


    public static function isClientToken($clientId, $tokenId) {
        $query = "SELECT * FROM `" . self::getTableNameStatic() . "` WHERE `clientId` = ? AND `tokenId` = ? LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->getShared('master/slave db');
        $data = $db->query($query, array($clientId, $tokenId))->fetch();

        if(is_array($data) == true) {
            return self::getObjectInstance($data);
        }
        return false;
    }


    /**
     * 외부용
     */
    public static function create($clientId, $tokenId) {

        if(is_numeric($clientId) == false || $clientId < 1) return false;
        if(is_numeric($clientId) == false || $clientId < 1) return false;

        // check exist
        $existInstance = self::isItem($clientId);
        if($existInstance) return $existInstance;

        // get api info
        $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
        if($sourceInstance->getApiInstanceByStream('up') == false){
            // upstream 미존재
            return false;
        }

        $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
        $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
        $postValues['function']     = 'initClientPoint';
        $postValues['branchId']     = BRANCH_ID;
        $postValues['tokenId']      = $tokenId;
        $postValues['ip']           = getIP();

        if($tokenId >= Point::Token_NewsSatoshi){
            $postValues['function'] .= 'V2';
        }

        $curl = new Curl();
        $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        $curl->setOption(CURLOPT_HEADER, false);
        $curl->setOption(CURLOPT_TIMEOUT, 5000);
        $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);
        $curlRetBody = json_decode($curlRetData->body, true);

        if(APPLICATION_ENV == 'dev'){
            $fp = fopen( "/var/log/publishlink/debug.log", "a");
            fwrite($fp, "\nnow : " . Util::getDbNow() . " \n");
            fwrite($fp, "body : " . $curlRetData->body . " \n");
        }

        $newItem = array();
        if(isset($curlRetBody['status']) == true && $curlRetBody['status'] == 200){
            // 성공
            if(isset($curlRetBody['code']) == true && $curlRetBody['code'] != ''){
                $newItem['code'] = $curlRetBody['code'];
            } else {
                return false;
            }
            if(isset($curlRetBody['secret']) == true && $curlRetBody['secret'] != ''){
                $newItem['secret'] = $curlRetBody['secret'];
            } else {
                return false;
            }
        } else {
            return false;
        }

        $newItem['clientId']     = $clientId;
        $newItem['tokenId']      = $tokenId;
        $newItem['regDate']      = Util::getLocalTime();
        $newItem['status']       = Point::Status_Active;

        $db        = DI::getDefault()->getShared('db');
        $db_master = DI::getDefault()->getShared('db_master');

        $result = $db_master->insert(self::getTableNameStatic(), array_values($newItem), array_keys($newItem));
        return $result;
    }



    public function getItemsPBF() {
        $where = array();

        if (Util::isInteger($this->getClientId()) == true) $where[] = 'clientId = ' . $this->getClientId();
        if (Util::isInteger($this->getTokenId()) == true) $where[] = 'tokenId = ' . $this->getTokenId();

        return $where;
    }


}

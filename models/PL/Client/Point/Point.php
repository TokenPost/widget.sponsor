<?php
namespace PL\Models\Client\Point;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use Phalcon\Http\Client\Provider\Curl as Curl;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Digital\Asset\Asset;
use PL\Models\Digital\Asset\Container as AssetContainer;
use PL\Models\Source\Source;
use PL\Models\Util\Util;
use PL\Models\Client\Client;
use PL\Models\Client\Point\Log\Log;
use PL\Models\Client\Point\Log\Container as LogContainer;

class Point extends AbstractSingleton {

    /**
     * 상수 설정
     */
    const tableName = 'ClientPoint';

    // decimal = 6, 0의 갯수
    // V1이용
    const Decimal_TPCLegacy   = 1000000;
    const Decimal_NEWS        = 1000000;

    // V2 이용
    const Decimal_NewsSatoshi   = 10000;
    const Decimal_PPOINT        = 10; // 10원기준 0.5원의 리워드가 존재.
    const Decimal_TPC           = 10; // 10원기준 0.5원의 리워드가 존재.
    const Decimal_NewsKrw       = 10; // 10원기준 0.5원의 리워드가 존재.
    const Decimal_NewsUsd       = 1000; // 두자리일경우 $0.01 리워드 최소 10원이기때문에 부담스럽다. 3~4자리로 수정

    const Token_TPC_Legacy   = 1; // decommissioned
    const Token_News         = 2; // decommissioned

    const Token_NewsSatoshi  = 3;
    const Token_NewsKrw      = 4;
    const Token_TPC          = 4;
    const Token_NewsUsd      = 5;
    const Token_EOS          = 6;

    /*
    const ActiveTokenId   = self::Token_News;
    const ActiveTokenDecimal = self::Decimal_NEWS;*/
    const ActiveTokenId   = self::Token_NewsSatoshi;
    const ActiveTokenDecimal = self::Decimal_NewsSatoshi;

    const ActiveRewardTokenId   = self::Token_TPC;
    const ActiveRewardTokenDecimal = self::Decimal_TPC;


    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_clientInstance;
    protected $_assetInstance;
    protected $_logContainer;

    protected $_tokenId;
    protected $_pointSync = 'N';
    protected $_pointArray = array(
        'point' => 0,
        'log' => 0,
        'add' => 0,
        'subtract' => 0
    );

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

    public static function getTableNameStatic(){
        return self::tableName;
    }

    public static function getObjectInstanceStatic($date) : self {
        return self::getInstance($date);
    }

    public function getObjectInstance($date) : self {
        return self::getInstance($date);
    }

    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getId());
        }
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance) {
        $this->_clientInstance = $clientInstance;
    }

    public function getAssetInstance() {
        $this->_assetInstance = AssetContainer::isItem($this->getTokenId());
        /*if (isset($this->_assetInstance) == false) {
            $this->_assetInstance = AssetContainer::isItem($this->getTokenId());
        }*/
        return $this->_assetInstance;
    }

    public function getLogContainer() {
        if (isset($this->_logContainer) == false) {
            $this->_logContainer = new LogContainer($this);
        }
        return $this->_logContainer;
    }

    public function getTokenId(){
        return $this->_tokenId;
    }

    public function setTokenId($tokenId){
        $this->_tokenId = $tokenId;

        $this->_pointSync = 'N';
        $this->_pointArray = array(
            'point' => 0,
            'log' => 0,
            'add' => 0,
            'subtract' => 0
        );
    }

    public static function isItem($itemId) {
        if(is_numeric($itemId) == true){
            $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `id` = ? LIMIT 1";
            $db     = DI::getDefault()->getShared('db');

            //$db     = DI::getDefault()->getShared('master/slave db');
            $data = $db->query($query, array($itemId))->fetch();

            if(is_array($data) == true) {
                return self::getObjectInstance($data);
            }
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
        $postValues['function']     = 'initClientPointV2';
        $postValues['tokenId']      = $tokenId;
        $postValues['branchId']     = BRANCH_ID;
        $postValues['ip']           = getIP();


        $curl = new Curl();
        $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        $curl->setOption(CURLOPT_HEADER, false);
        $curl->setOption(CURLOPT_TIMEOUT, 5000);
        $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);
        $curlRetBody = json_decode($curlRetData->body, true);

        $fp = fopen( "/var/log/linksecu/debug.log", "a");
        fwrite($fp, "\nnow : " . Util::getDbNow() . " \n");
        fwrite($fp, "body : " . $curlRetData->body . " \n");



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

        $newItem['id'] = $clientId;
        $newItem['regDate'] = Util::getLocalTime();
        $newItem['status']  = self::Status_Active;

        $db        = DI::getDefault()->getShared('db');
        $db_master = DI::getDefault()->getShared('db_master');

        $result = $db_master->insert(self::getTableNameStatic(), array_values($newItem), array_keys($newItem));
        return $result;
    }


    private function _pointSync() {
        if ($this->_pointSync == 'N') {

            // get api info
            $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
            if($sourceInstance->getApiInstanceByStream('up') == false){
                // upstream 미존재
                return false;
            }

            $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
            $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
            $postValues['function']     = 'getClientPointV2';
            $postValues['branchId']     = BRANCH_ID;
            $postValues['code']         = $this->getCode();
            $postValues['clientSecret'] = $this->getSecret();
            $postValues['tokenId']      = $this->getTokenId();
            $postValues['ip']           = getIP();


            $curl = new Curl();
            $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
            $curl->setOption(CURLOPT_HEADER, false);
            $curl->setOption(CURLOPT_TIMEOUT, 5000);
            $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);
            $curlRetBody = json_decode($curlRetData->body, true);


            /*
                                    echo "article upstream ret : ".  "\n";
                                    var_dump($curlRetBody);
                                    echo "\n\n";*/
            /*
                        var_dump($curlRetData);
                        var_dump($curlRetBody);
                        */
            //$this->_client = Client::getInstance($this->getClientId());
            if(isset($curlRetBody['status']) == true && $curlRetBody['status'] == 200){
                // 성공
                if(isset($curlRetBody['values']) == true && is_array($curlRetBody['values']) == true){
                    if(isset($curlRetBody['values']['point']) == true) $this->setPointValue('point', $curlRetBody['values']['point']);
                    if(isset($curlRetBody['values']['log']) == true) $this->setPointValue('log', $curlRetBody['values']['log']);
                    if(isset($curlRetBody['values']['add']) == true) $this->setPointValue('add', $curlRetBody['values']['add']);
                    if(isset($curlRetBody['values']['subtract']) == true) $this->setPointValue('subtract', $curlRetBody['values']['subtract']);
                }
            }
            $this->_pointSync = 'Y';
        }
    }

    public function getLogItemsTP($maxId = 0, $listSize = 0) {
        // get api info
        $sourceInstance = Source::getInstance(Source::Source_ElminSecu);
        if($sourceInstance->getApiInstanceByStream('up') == false){
            // upstream 미존재
            return false;
        }

        $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
        $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
        $postValues['function']     = 'getClientPointLogV2';
        //$postValues['ci']           = $this->getCi();
        $postValues['code']         = $this->getCode();
        $postValues['clientSecret'] = $this->getSecret();
        $postValues['tokenId']      = $this->getTokenId();
        $postValues['ip']           = getIP();
        $postValues['maxId']        = $maxId;
        $postValues['listSize']     = $listSize;


        $curl = new Curl();
        $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        $curl->setOption(CURLOPT_HEADER, false);
        $curl->setOption(CURLOPT_TIMEOUT, 5000);
        $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);
        $curlRetBody = json_decode($curlRetData->body, true);


        /*
            echo "article upstream ret : ".  "\n";
            var_dump($curlRetBody);
            echo "\n\n";*/
        /*
        var_dump($curlRetData);
        var_dump($curlRetBody);
        */
        //$this->_client = Client::getInstance($this->getClientId());
        if(isset($curlRetBody['status']) == true && $curlRetBody['status'] == 200){
            // 성공
            if(isset($curlRetBody['size']) == true && $curlRetBody['size'] >= 1){
                // item이 있다.

                // timezone을 바꿔준다.
                foreach ($curlRetBody['items'] as $key => $var){
                    $curlRetBody['items'][$key]['regDate'] = Util::convertTimezone($curlRetBody['items'][$key]['regDate'], SITE_TIMEZONE);

                    //  set할때 display단위로 변경해준다.
                    $curlRetBody['items'][$key]['point'] = Point::castDisplayPoint($curlRetBody['items'][$key]['point'], $this->getTokenId());
                    $curlRetBody['items'][$key]['after'] = Point::castDisplayPoint($curlRetBody['items'][$key]['after'], $this->getTokenId());
                    $curlRetBody['items'][$key]['tokenId'] = $this->getTokenId();
                }

                //return Instance format
                //return ;
                $logItems = array();
                foreach ($curlRetBody['items'] as $var){
                    $logItems[] = Log::getInstance($var);
                    if($listSize >= 1 && sizeof($logItems) >= $listSize) break;
                }


                return $logItems;
            }
            return array();
        }
        return false;
    }

    public function getPointValue($key) {
        $pointArray = $this->getPointArray();
        if(isset($pointArray[$key]) == true){
            return $pointArray[$key];
        } else {
            return '';
        }
    }

    public function setPointValue($key, $value) {
        if(trim($key) == '') return false;
        $value = trim($value);
        $pointArray = $this->getPointArray();
        $pointArray[$key] = $value;
        $this->setPointArray($pointArray);
    }

    public function getPointArray() : array {
        return $this->_pointArray;
    }

    public function setPointArray(array $pointArray){
        $this->_pointArray = $pointArray;
    }


    /**
     * secu로 보낸다.
     */
    //public function addPoint($point, $log, $comment, $regIp, $adminId) {
    public function addPoint($point, $log, $comment, $regIp, $requesterType, $requesterId = 0, $approverId = 0, $date = '') {
        try{
            if(!($requesterType == Log::RequesterType_Self || $requesterType == Log::RequesterType_Admin || $requesterType == Log::RequesterType_Bot)){
                // requester type error
                return false;
            }
            // bot일수도 있다. id제한 0
            if(is_numeric($requesterId) == false || $requesterId < 0) throw new Exception('Parameter error.');
            if(is_numeric($approverId) == false || $approverId < 0) throw new Exception('Parameter Instance error.');
            if($point == 0) throw new Exception('Api Instance error.');
            if(is_numeric($point) == false) throw new Exception('포인트가 숫자가 아닙니다.');
            if($date == '') {
                $date = Util::getLocalTime('Y-m-d');
            }


            // check decimal point
            if($this->checkMaxDecimal($point) !== true) throw new Exception('허용범위보다 큰 소수자리의 포인트 입니다.');




            // convert to basic unit
            //$point = $point * self::Decimal_TPC;
            // secu에서 알아서 처리한다.

            if($point > 0){
                // add
                $position = Log::Position_Add;
                $point = abs($point);
            } else {
                // subtract
                $position = Log::Position_Subtract;

                //check limit
                if(abs($point) > abs($this->getPoint())){
                    // 현재값보다 더 많이 삭제하려 한다.
                    //return false;
                    throw new Exception('삭제하려는 포인트가 현재 포인트보다 작습니다.');
                }
            }



            // 소수점 -> 사토시 변환
            if($this->getTokenId() <= self::Token_News){

            } else {

                // Casting display style to Satoshi
                switch ($this->getTokenId()){
                    case self::Token_NewsSatoshi:
                    case self::Token_NewsKrw:
                    case self::Token_NewsUsd:
                        $point = self::castSatoshi($point, $this->getTokenId());
                        break;
                }

            }



            // get api info
            $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
            if($sourceInstance->getApiInstanceByStream('up') == false){
                // upstream 미존재
                //return false;
                throw new Exception('Api Instance error.');
            }



            $valuesArray['point']           = $point;
            $valuesArray['log']             = $log;
            $valuesArray['comment']         = $comment;
            $valuesArray['regIp']           = getIP();
            $valuesArray['requesterType']   = $requesterType;
            $valuesArray['requesterId']     = $requesterId;
            $valuesArray['approverId']      = $approverId;
            $valuesArray['position']        = $position;
            $valuesArray['date']            = $date;

            $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
            $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
            $postValues['function']     = 'addClientPointV2';
            $postValues['code']         = $this->getCode();
            $postValues['branchId']     = BRANCH_ID;
            $postValues['clientSecret'] = $this->getSecret();
            $postValues['tokenId']      = $this->getTokenId();
            $postValues['ip']           = getIP();
            $postValues['values']       = $valuesArray;

            $curl = new Curl();
            $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
            $curl->setOption(CURLOPT_HEADER, false);
            $curl->setOption(CURLOPT_TIMEOUT, 5000);
            $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);
            $curlRetBody = json_decode($curlRetData->body, true);


            /*
                                    echo "article upstream ret : ".  "\n";
                                    var_dump($curlRetBody);
                                    echo "\n\n";*/
            /*
                        var_dump($curlRetData);
                        var_dump($curlRetBody);
                        */
            //$this->_client = Client::getInstance($this->getClientId());
            if(isset($curlRetBody['status']) == true && $curlRetBody['status'] == 200){
                // 성공


                //$this->_info['point'] = $this->getPoint() + $point;
                $this->_info['point'] = $curlRetBody['after'];
            } else {
                throw new Exception($curlRetBody['message']);
            }

            // addpoint -> $curlRetBody['logId']

            $result = array();
            $result['error'] = 0;
            $result['message'] = '';
            $result['logId'] = trim($curlRetBody['logId']);

        } catch (Exception $e){
            $result = array();
            $result['error'] = 1;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }






    public function getId() {
        return $this->_info['id'];
    }

//    public function getClientId() {
//        return $this->_info['clientId'];
//    }

    public function getCode() {
        return $this->_info['code'];
    }

    public function getSecret() {
        return $this->_info['secret'];
    }

    public function getDisplayPoint() {
        if($this->getPoint() == 0){
            return 0;
        }
        $point = self::castDisplayPoint($this->getPoint(), $this->getTokenId());
        return Util::decimalFormat($point, 4);
    }

    public static function castDisplayPoint($point, $tokenId) {
        if($point == 0){
            return 0;
        }

        // Casting Satoshi to display style
        switch ($tokenId){
            case self::Token_NewsSatoshi:
                $point = $point / self::Decimal_NewsSatoshi;
                break;
            case self::Token_NewsKrw:
                $point = $point / self::Decimal_NewsKrw;
                break;
            case self::Token_NewsUsd:
                $point = $point / self::Decimal_NewsUsd;
                break;
            default:
                //$point =  Util::numberFormat($point ,strlen(self::ActiveTokenDecimal) -1);
                // 기존 그대로 소수
                break;
        }
        //$point =  Util::numberFormat($point ,strlen(self::ActiveTokenDecimal) -1);
        return $point;


        //return Util::numberFormat(number_format($this->getPoint() ,strlen(self::Decimal_TPC) -1), strlen(self::Decimal_TPC) -1);
        //return Util::numberFormat(number_format(($this->getPoint() / self::Decimal_TPC),strlen(self::Decimal_TPC) -1), strlen(self::Decimal_TPC) -1);
    }

    public static function castSatoshi($point, $tokenId){
        if($point == 0){
            return 0;
        }

        // Casting Satoshi to display style
        switch ($tokenId){
            case self::Token_NewsSatoshi:
                $point = $point * self::Decimal_NewsSatoshi;
                break;
            case self::Token_NewsKrw:
                $point = $point * self::Decimal_NewsKrw;
                break;
            case self::Token_NewsUsd:
                $point = $point * self::Decimal_NewsUsd;
                break;
            default:
                //$point =  Util::numberFormat($point ,strlen(self::ActiveTokenDecimal) -1);
                // 기존 그대로 소수
                break;
        }
        //$point =  Util::numberFormat($point ,strlen(self::ActiveTokenDecimal) -1);


        // 입력실수일경우 소수 존재할수도 있다.
        return round($point);
    }

    public function checkMaxDecimal($point, $tokenId = 0){

        if($this->getTokenId() < 1) return false;
        if($this->getAssetInstance() instanceof Asset != true) return false;

        $decimals = $this->getAssetInstance()->getDecimals();
        $point = floatval($point);

        if(strpos($point, '.')){
            if(strlen(strchr($point, '.')) > ++$decimals) return false;
        }
        return true;
    }


    public function getDecimalLength($tokenId){

        switch ($tokenId){
            case self::Token_News:
                $length = strlen(self::Decimal_NEWS);
                break;
            case self::Token_NewsSatoshi:
                $length = strlen(self::Decimal_NewsSatoshi);
                break;
            case self::Token_NewsKrw:
                $length = strlen(self::Decimal_NewsKrw);
                break;
            case self::Token_NewsUsd:
                $length = strlen(self::Decimal_NewsUsd);
                break;
            default:
                return 0;
                break;
        }
        return $length - 1;
    }


    public function getPoint() {
        $this->_pointSync();
        return $this->getPointValue('point');
    }

    public function getLog() {
        $this->_pointSync();
        return $this->getPointValue('log');
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }

    /**
     * 계정의 상태 값을 텍스트로 반환합니다
     *
     * @return string
     */
    public function getStatusName() {
        switch ($this->_info['status']) {
            case self::Status_Active:
                return 'active';
                break;
            case self::Status_Inactive:
                return 'inactive';
                break;
            default:
                return '(???)';
                break;
        }
    }


    // 오늘 활동한 point 내역
    public function getTodayActivityPoint() {
        $today = date("Y-m-d"); // 오늘 날짜
        // get api info
        $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
        if($sourceInstance->getApiInstanceByStream('up') == false){
            // upstream 미존재
            return false;
        }
        $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
        $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
        $postValues['function']     = 'getTodayActivityPointV2';
        $postValues['branchId']     = BRANCH_ID;
        $postValues['code']         = $this->getCode();
        $postValues['clientSecret'] = $this->getSecret();
        $postValues['tokenId']      = $this->getTokenId();
        $postValues['requesterId']  = $this->getId();     // client id
        $postValues['today']        = $today;

        $curl = new Curl();
        $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        $curl->setOption(CURLOPT_HEADER, false);
        $curl->setOption(CURLOPT_TIMEOUT, 5000);

        $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);
        $curlRetBody = json_decode($curlRetData->body, true);

        if(isset($curlRetBody['status']) == true && $curlRetBody['status'] == 200){
            // 성공
            if(isset($curlRetBody['size']) == true && $curlRetBody['size'] >= 1){
                // items 존재
                $amount = 0;    // 하루 활동해서 모은 포인트
                foreach ($curlRetBody['items'] as $var){
                    $point = $var['point'];
                    if($var['position'] == Log::Position_Add) {
                        // 플러스
                        $condition = '+';
                    } else {
                        // 마이너스
                        $condition = '-';
                        $point = str_replace('-','',  $var['point']);
                    }
                    $amount = Util::decimalMath($amount, $condition, $point);
                }
                return $amount;
            }
        }
        return 0;
    }

    public function getLogItems($maxId = 0, $listSize = 0, $beforeDay = '') {
        // get api info
        $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
        if($sourceInstance->getApiInstanceByStream('up') == false){
            // upstream 미존재
            return false;
        }

        $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
        $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
        $postValues['function']     = 'getClientPointLogV2';
        $postValues['branchId']     = BRANCH_ID;
        $postValues['code']         = $this->getCode();
        $postValues['clientSecret'] = $this->getSecret();
        $postValues['tokenId']      = $this->getTokenId();
        $postValues['ip']           = getIP();
        $postValues['maxId']        = $maxId;
        $postValues['listSize']     = $listSize;
        $postValues['beforeDay']    = $beforeDay;


        $curl = new Curl();
        $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        $curl->setOption(CURLOPT_HEADER, false);
        $curl->setOption(CURLOPT_TIMEOUT, 5000);
        $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);

        $curlRetBody = json_decode($curlRetData->body, true);


        if(isset($curlRetBody['status']) == true && $curlRetBody['status'] == 200){
            // 성공
            if(isset($curlRetBody['size']) == true && $curlRetBody['size'] >= 1){
                // item이 있다.

                $logItems = array();
                foreach ($curlRetBody['items'] as $var){
                    $logItems[] = Log::getInstance($var);
                    if($listSize >= 1 && sizeof($logItems) >= $listSize) break;
                }


                return $logItems;
            }
            return array();
        }
        return false;
    }

    public function getMonthLog() {
        // get api info
        $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
        if($sourceInstance->getApiInstanceByStream('up') == false){
            // upstream 미존재
            return false;
        }

        $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
        $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
        $postValues['function']     = 'getClientPointLogMonthV3';
        $postValues['branchId']     = BRANCH_ID;
        $postValues['code']         = $this->getCode();
        $postValues['clientSecret'] = $this->getSecret();
        $postValues['tokenId']      = $this->getTokenId();
        $postValues['ip']           = getIP();
        $postValues['startDate']    = date("Y-m-d", strtotime('-1 month'));
        $postValues['endDate']      = date("Y-m-d");

        $curl = new Curl();
        $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        $curl->setOption(CURLOPT_HEADER, false);
        $curl->setOption(CURLOPT_TIMEOUT, 5000);
        $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);

        $curlRetBody = json_decode($curlRetData->body, true);

        if(isset($curlRetBody['status']) == true && $curlRetBody['status'] == 200){
            // 성공
            if(isset($curlRetBody['size']) == true && $curlRetBody['size'] >= 1){
                // items 존재


                $date = '';
                $amount = 0;    // 하루 활동해서 모은 포인트
                $tempArray = array();
                foreach ($curlRetBody['items'] as $idx => $var){
                    $point = $var['point'];
                    if($var['position'] == Log::Position_Add) {
                        // 플러스
                        $condition = '+';
                    } else {
                        // 마이너스
                        $condition = '-';
                        $point = str_replace('-','',  $var['point']);
                    }

                    if($date != $var['date']) {
                        $amount = 0;
                        $date = $var['date'];
                        $amount = Util::decimalMath($amount, $condition, $point);
                    } else {
                        $amount = Util::decimalMath($amount, $condition, $point);
                    }

                    $tempArray[$date] = $amount;
                }
                return $tempArray;
            }
        }
        return array();
    }

}
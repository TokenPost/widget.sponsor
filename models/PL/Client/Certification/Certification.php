<?php
namespace PL\Models\Client\Certification;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use Phalcon\Http\Client\Provider\Curl as Curl;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Source\Source;
use PL\Models\Admin\Admin;
use PL\Models\Client\Client;
use PL\Models\Country\Country;
use PL\Models\Util\Util;

class Certification extends AbstractSingleton {
    /**
     * log 저장
    */

    const tableName = 'ClientCertification';

    const Status_Complete  = 0;
    const Status_Pending   = 1;
    const Status_Expired   = 2;
    const Status_Discard   = 3;

    const Provider_KMC     = 1;
    const Provider_KCB     = 2;

    protected $_admin;
    protected $_clientInstance;
    protected $_informationInit = 'N';
    protected $_informationArray = array(
        'name' => '',
        'phone' => '',
        'birthDate' => '',
        'gender' => '',
        'nation' => ''
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

    public function isAvailableIp(){
        if($this->getIp() == '') return true;
        $items = explode(',', $this->getIp());
        $clientIp = getIp();
        foreach($items as $var){
            if(trim($var) == $clientIp) return true;
        }
        //return strpos($this->getIp(), getIp());
        return false;
    }


    public function getClientInstance() {
    if (isset($this->_clientInstance) == false) {
        $this->_clientInstance = Client::getInstance($this->getClientId());
    }
    return $this->_clientInstance;
}


    private function _informationInit() {
        if ($this->_informationInit == 'N') {

            // get api info
            $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
            if($sourceInstance->getApiInstanceByStream('up') == false){
                // upstream 미존재
                return false;
            }

            $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
            $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
            $postValues['function']     = 'getClientInfo';
            $postValues['ci']           = $this->getCi();
            $postValues['clientSecret'] = $this->getSecret();
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
                    if(isset($curlRetBody['values']['name']) == true && $curlRetBody['values']['name'] != '') $this->setInformationValue('name', $curlRetBody['values']['name']);
                    if(isset($curlRetBody['values']['phone']) == true && $curlRetBody['values']['phone'] != '') $this->setInformationValue('phone', $curlRetBody['values']['phone']);
                    if(isset($curlRetBody['values']['gender']) == true && $curlRetBody['values']['gender'] != '') $this->setInformationValue('gender', $curlRetBody['values']['gender']);
                    if(isset($curlRetBody['values']['birthDate']) == true && $curlRetBody['values']['birthDate'] != '') $this->setInformationValue('birthDate', $curlRetBody['values']['birthDate']);
                }
            }
            $this->_informationInit = 'Y';
        }
    }

    public function getInformationValue($key) {
        $informationArray = $this->getInformationArray();
        if(isset($informationArray[$key]) == true){
            return $informationArray[$key];
        } else {
            return '';
        }
    }

    public function setInformationValue($key, $value) {
        if(trim($key) == '') return false;
        $value = trim($value);
        $informationArray = $this->getInformationArray();
        $informationArray[$key] = $value;
        $this->setInformationArray($informationArray);
    }

    public function getInformationArray() : array {
        return $this->_informationArray;
    }

    public function setInformationArray(array $informationArray){
        $this->_informationArray = $informationArray;
    }




    public function getId() {
        return $this->_info['id'];
    }

//    public function getClientId() {
//        return $this->_info['clientId'];
//    }
//
//    public function setClientId($clientId) {
//        $this->_info['clientId']    = $clientId;
//        $this->_changes['clientId'] = $this->_info['clientId'];
//    }
//
//    public function getTokenId() {
//        return $this->_info['tokenId'];
//    }
//
//    public function setTokenId($tokenId) {
//        $this->_info['tokenId']    = $tokenId;
//        $this->_changes['tokenId'] = $this->_info['tokenId'];
//    }

    public function getProviderId() {
        return $this->_info['providerId'];
    }

    public function setProviderId($providerId) {
        $this->_info['providerId']    = $providerId;
        $this->_changes['providerId'] = $this->_info['providerId'];
    }

//    public function getResult() {
//        return $this->_info['result'];
//    }
//
//    public function setResult($result) {
//        $this->_info['result']    = $result;
//        $this->_changes['result'] = $this->_info['result'];
//    }

    public function getFailCode() {
        return $this->_info['failCode'];
    }

    public function setFailCode($failCode) {
        $this->_info['failCode']    = $failCode;
        $this->_changes['failCode'] = $this->_info['failCode'];
    }

    public function getMessage() {
        return $this->_info['message'];
    }

    public function setMessage($message) {
        $this->_info['message']    = $message;
        $this->_changes['message'] = $this->_info['message'];
    }

    public function getCi() {
        return $this->_info['ci'];
    }

    public function setCi($ci) {
        $this->_info['ci']    = $ci;
        $this->_changes['ci'] = $this->_info['ci'];
    }

    public function getDi() {
        return $this->_info['di'];
    }

    public function setDi($di) {
        $this->_info['di']    = $di;
        $this->_changes['di'] = $this->_info['di'];
    }

    public function getName() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['name'];
    }

    public function getGender() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['gender'];
        //return $this->_info['gender'];
    }

    public function setGender($gender) {
        $this->_info['gender']    = $gender;
        $this->_changes['gender'] = $this->_info['gender'];
    }

    public function getBirthDate($format = 'Y-m-d') {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        if($this->_informationArray['birthDate'] == '' || $this->_informationArray['birthDate'] == '0000-00-00'){
            return '0000-00-00';
        }
        return date($format, strtotime($this->_informationArray['birthDate']));
        //return date($format, strtotime($this->_info['birthDate']));
    }

    public function setBirthDate($birthDate) {
        $this->_info['birthDate']    = $birthDate;
        $this->_changes['birthDate'] = $this->_info['birthDate'];
    }

    public function getNation() {
        return $this->_info['nation'];
    }

    public function setNation($nation) {
        $this->_info['nation']    = $nation;
        $this->_changes['nation'] = $this->_info['nation'];
    }

    public function getPhone() {
        return $this->_informationArray['phone'];
        //return $this->_info['phone'];
    }

    public function setPhone($phone) {
        $this->_info['phone']    = $phone;
        $this->_changes['phone'] = $this->_info['phone'];
    }

    public function getTelecom() {
        return $this->_info['telecom'];
    }

    public function setTelecom($telecom) {
        $this->_info['telecom']    = $telecom;
        $this->_changes['telecom'] = $this->_info['telecom'];
    }

    public function getSecret() {
        return $this->_info['secret'];
    }

    public function getValues() {
        return $this->_info['values'];
    }

    public function setValues($values) {
        $this->_info['values']    = $values;
        $this->_changes['values'] = $this->_info['values'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = $regDate;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
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
            case self::Status_Expired:
                return 'expired';
                break;
            case self::Status_Discard:
                return 'discard';
                break;

            default:
                return '(???)';
        }
    }
}
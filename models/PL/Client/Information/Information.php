<?php
namespace PL\Models\Client\Information;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use Phalcon\Http\Client\Provider\Curl as Curl;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Crypt\Crypt;
use PL\Models\Source\Source;
use PL\Models\Admin\Admin;
use PL\Models\Client\Client;
use PL\Models\Country\Country;
use PL\Models\Util\Util;

class Information extends AbstractSingleton {

    const tableName = 'ClientInformation';

    protected $_clientInstance;
    protected $_informationInit = 'N';
    protected $_informationArray = array(
        'ci'            => '',
        'di'            => '',
        'name'          => '',
        'phone'         => '',
        'birthDate'     => '',
        'gender'        => '',
        'nation'        => '',
        'zipCode'       => '',
        'address'       => '',
        'addressDetail' => '',
        'jobId'         => '',
        'job'           => ''
    );

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

    public static function getTableNameStatic(){
        return self::tableName;
    }

    public static function getObjectInstanceStatic($date) : self {
        return self::getInstance($date);
    }

    public function getObjectInstance($date) : self {
        return self::getInstance($date);
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

    public static function isItem($itemId, $field = 'id') {
        if(Util::isInteger($itemId) == true){
            $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `" . $field . "` = ? LIMIT 1";
            $db     = DI::getDefault()->getShared('db');

            //$db     = DI::getDefault()->getShared('master/slave db');
            $data = $db->query($query, array($itemId))->fetch();

            if(is_array($data) == true) {
                return self::getObjectInstance($data);
            }
        }
        return false;
    }

    public static function create($clientId) {
        if(Util::isInteger($clientId) == false || $clientId < 1) return false;

        $code = '';
        while (true){
            $code  = Util::generateRandomString(32, '**');
            $exist = self::isItem($code, 'code');
            if($exist == false) break;
        };

        $item['id']   = $clientId;
        $item['code'] = $code;
        usleep(1);
        $item['secret'] = Util::generateRandomString(32, '**');

        $db        = DI::getDefault()->getShared('db');
        $db_master = DI::getDefault()->getShared('db_master');

        $result = $db_master->insert(self::tableName, array_values($item), array_keys($item));
        return $result;
    }

    public function initInformation($informationItem){
        // get api info
        $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
        if($sourceInstance->getApiInstanceByStream('up') == false){
            // upstream 미존재
            return false;
        }
        foreach ($informationItem as $key => $var){
            $informationItem[$key] = Crypt::encrypt($var, $sourceInstance->getApiInstanceByStream('up')->getFixedValue());
        }

        $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
        $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
        $postValues['function']     = 'addClientInfo';
        $postValues['branchId']     = BRANCH_ID;
        $postValues['clientCode']   = $this->getCode();
        $postValues['clientSecret'] = $this->getSecret();
        $postValues['ip']           = getIP();
        $postValues['values']       = $informationItem;

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
            if(isset($curlRetBody['values']) == true && is_array($curlRetBody['values']) == true){
                if(isset($curlRetBody['values']['name']) == true && $curlRetBody['values']['name'] != '')                   $this->setInformationValue('name', $curlRetBody['values']['name']);
                if(isset($curlRetBody['values']['phone']) == true && $curlRetBody['values']['phone'] != '')                 $this->setInformationValue('phone', $curlRetBody['values']['phone']);
                if(isset($curlRetBody['values']['gender']) == true && $curlRetBody['values']['gender'] != '')               $this->setInformationValue('gender', $curlRetBody['values']['gender']);
                if(isset($curlRetBody['values']['birthDate']) == true && $curlRetBody['values']['birthDate'] != '')         $this->setInformationValue('birthDate', $curlRetBody['values']['birthDate']);
                if(isset($curlRetBody['values']['zipCode']) == true && $curlRetBody['values']['zipCode'] != '')             $this->setInformationValue('zipCode', $curlRetBody['values']['zipCode']);
                if(isset($curlRetBody['values']['addressType']) == true && $curlRetBody['values']['addressType'] != '')     $this->setInformationValue('addressType', $curlRetBody['values']['addressType']);
                if(isset($curlRetBody['values']['address']) == true && $curlRetBody['values']['address'] != '')             $this->setInformationValue('address', $curlRetBody['values']['address']);
                if(isset($curlRetBody['values']['addressDetail']) == true && $curlRetBody['values']['addressDetail'] != '') $this->setInformationValue('addressDetail', $curlRetBody['values']['addressDetail']);

                if(isset($curlRetBody['values']['job']) == true && $curlRetBody['values']['job'] != '')                     $this->setInformationValue('job', $curlRetBody['values']['job']);
                if(isset($curlRetBody['values']['jobId']) == true && $curlRetBody['values']['jobId'] != '')                 $this->setInformationValue('jobId', $curlRetBody['values']['jobId']);
            }
        }

        $this->_informationInit = 'Y';
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
            $postValues['branchId']     = BRANCH_ID;
            $postValues['clientCode']   = $this->getCode();
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
                    if(isset($curlRetBody['values']['name']) == true && $curlRetBody['values']['name'] != '')                   $this->setInformationValue('name', $curlRetBody['values']['name']);
                    if(isset($curlRetBody['values']['phone']) == true && $curlRetBody['values']['phone'] != '')                 $this->setInformationValue('phone', $curlRetBody['values']['phone']);
                    if(isset($curlRetBody['values']['gender']) == true && $curlRetBody['values']['gender'] != '')               $this->setInformationValue('gender', $curlRetBody['values']['gender']);
                    if(isset($curlRetBody['values']['birthDate']) == true && $curlRetBody['values']['birthDate'] != '')         $this->setInformationValue('birthDate', $curlRetBody['values']['birthDate']);
                    if(isset($curlRetBody['values']['zipCode']) == true && $curlRetBody['values']['zipCode'] != '')             $this->setInformationValue('zipCode', $curlRetBody['values']['zipCode']);
                    //if(isset($curlRetBody['values']['addressType']) == true && $curlRetBody['values']['addressType'] != '')     $this->setInformationValue('addressType', $curlRetBody['values']['addressType']);
                    if(isset($curlRetBody['values']['address']) == true && $curlRetBody['values']['address'] != '')             $this->setInformationValue('address', $curlRetBody['values']['address']);
                    if(isset($curlRetBody['values']['addressDetail']) == true && $curlRetBody['values']['addressDetail'] != '') $this->setInformationValue('addressDetail', $curlRetBody['values']['addressDetail']);

                    if(isset($curlRetBody['values']['job']) == true && $curlRetBody['values']['job'] != '')                     $this->setInformationValue('job', $curlRetBody['values']['job']);
                    if(isset($curlRetBody['values']['jobId']) == true && $curlRetBody['values']['jobId'] != '')                 $this->setInformationValue('jobId', $curlRetBody['values']['jobId']);
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

    public function saveChanges() {
        if (sizeof($this->_changes) < 1) {
            return;
        }


        // get api info
        $sourceInstance = Source::getInstance(Source::Source_PublishSecu);
        if($sourceInstance->getApiInstanceByStream('up') == false){
            // upstream 미존재
            return false;
        }

        foreach ($this->_changes as $key => $var){
            if($key == 'ci' || $key == 'di') {
                $this->_changes[$key] = $var;
            } else {
                $this->_changes[$key] = Crypt::encrypt($var, $sourceInstance->getApiInstanceByStream('up')->getFixedValue());
            }
        }

        $postValues['key']          = $sourceInstance->getApiInstanceByStream('up')->getKey();
        $postValues['secret']       = $sourceInstance->getApiInstanceByStream('up')->getSecret();
        $postValues['function']     = 'modifyClientInfo';
        $postValues['branchId']     = BRANCH_ID;
        $postValues['clientCode']   = $this->getCode();
        $postValues['clientSecret'] = $this->getSecret();
        $postValues['ip']           = getIP();
        $postValues['values']       = $this->_changes;

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
                if(isset($curlRetBody['values']['ci']) == true && $curlRetBody['values']['ci'] != '') $this->setInformationValue('name', $curlRetBody['values']['ci']);
                if(isset($curlRetBody['values']['di']) == true && $curlRetBody['values']['di'] != '') $this->setInformationValue('phone', $curlRetBody['values']['di']);
                if(isset($curlRetBody['values']['name']) == true && $curlRetBody['values']['name'] != '') $this->setInformationValue('name', $curlRetBody['values']['name']);
                if(isset($curlRetBody['values']['phone']) == true && $curlRetBody['values']['phone'] != '') $this->setInformationValue('phone', $curlRetBody['values']['phone']);
                if(isset($curlRetBody['values']['gender']) == true && $curlRetBody['values']['gender'] != '') $this->setInformationValue('gender', $curlRetBody['values']['gender']);
                if(isset($curlRetBody['values']['birthDate']) == true && $curlRetBody['values']['birthDate'] != '') $this->setInformationValue('birthDate', $curlRetBody['values']['birthDate']);
            }
        }

        $this->_changes = array();
    }

    public static function isClientInfo($addedCode, $addedSecret) {
        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `code` = ?  AND `secret` = ? LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->get('master/slave db');
        $data = $db->query($query, array(addslashes($addedCode),addslashes($addedSecret),))->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }
        return false;
    }



    public function getId() {
        return $this->_info['id'];
    }

    public function getCode() {
        return $this->_info['code'];
    }

    public function getSecret() {
        return $this->_info['secret'];
    }

    //********
    //* ClientTemp 에 저장된 secret 이랑 동일해야하나..?
    //* 동일하게 저장할 경우는 필요
    //********
    public function setSecret($secret) {
        $this->_informationArray['secret'] = $secret;
        $this->_changes['secret'] = $this->_informationArray['secret'];
    }

    /**
     * 이하 secu 서버 저장
     */
    public function getCi() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['ci'];
    }

    public function setCi($ci) {
        $this->_informationArray['ci'] = $ci;
        $this->_changes['ci'] = $this->_informationArray['ci'];
    }

    public function getDi() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['di'];
    }

    public function setDi($di) {
        $this->_informationArray['di'] = $di;
        $this->_changes['di'] = $this->_informationArray['di'];
    }

    public function getName() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['name'];
    }

    public function getProfileName(){

        $name = $this->getName();

        if(preg_match("/[\xA1-\xFE][\xA1-\xFE]/", $name) == 1){
            return mb_substr($name, 0, 1, 'UTF8');
        } else {
            return substr($name, 0, 2);
        }

    }

    public function setName($name) {
        $this->_informationArray['name'] = $name;
        $this->_changes['name'] = $this->_informationArray['name'];
    }

    public function getGender() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['gender'];
    }

    public function getGenderStr(){
        if($this->getGender() == 'M'){
            return '남';
        } elseif ($this->getGender() == 'F') {
            return '여';
        }
        return '';
    }

    public function setGender($gender) {
        $this->_informationArray['gender'] = $gender;
        $this->_changes['gender'] = $this->_informationArray['gender'];
    }

    public function getBirthDate($format = 'Y-m-d') {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        if($this->_informationArray['birthDate'] == '' || $this->_informationArray['birthDate'] == '0000-00-00'){
            return '0000-00-00';
        }
        return date($format, strtotime($this->_informationArray['birthDate']));
    }

    public function getBirthDateFormat($format = 'Y년 m월 d일') {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        if($this->_informationArray['birthDate'] == '' || $this->_informationArray['birthDate'] == '0000-00-00'){
            return '0000-00-00';
        }
        return date($format, strtotime($this->_informationArray['birthDate']));
    }

    public function setBirthDate($birthDate) {
        $this->_informationArray['birthDate'] = $birthDate;
        $this->_changes['birthDate'] = $this->_informationArray['birthDate'];
    }

    public function getNation() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_info['nation'];
    }

    public function setNation($nation) {
        $this->_informationArray['nation'] = $nation;
        $this->_changes['nation'] = $this->_informationArray['nation'];
    }

    public function getPhone() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['phone'];
    }

    public function getFrontPhone() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }

        $phone = preg_replace("/[^0-9]/", "", $this->_informationArray['phone']);
        $length = strlen($phone);

        switch($length){
            case 11 :
                return preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
            case 10:
                return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
            default :
                return $phone;
        }
    }

    public function setPhone($phone) {
        $this->_informationArray['phone'] = $phone;
        $this->_changes['phone'] = $this->_informationArray['phone'];
    }

    public function getJobId() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['jobId'];
    }

    public function setJobId($jobId) {
        $this->_informationArray['jobId'] = $jobId;
        $this->_changes['jobId'] = $this->_informationArray['jobId'];
    }

    public function getJob() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['job'];
    }

    public function setJob($job) {
        $this->_informationArray['job'] = $job;
        $this->_changes['job'] = $this->_informationArray['job'];
    }

    public function getCompany() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['company'];
    }

    public function setCompany($company) {
        $this->_informationArray['company'] = $company;
        $this->_changes['company'] = $this->_informationArray['company'];
    }

    public function getDepartment() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['department'];
    }

    public function setDepartment($department) {
        $this->_informationArray['department'] = $department;
        $this->_changes['department'] = $this->_informationArray['department'];
    }

    public function getPosition() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['position'];
    }

    public function setPosition($position) {
        $this->_informationArray['position'] = $position;
        $this->_changes['position'] = $this->_informationArray['position'];
    }

    public function getZipCode() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['zipCode'];
    }

    public function setZipCode($zipCode) {
        $this->_informationArray['zipCode'] = $zipCode;
        $this->_changes['zipCode'] = $this->_informationArray['zipCode'];
    }

    public function getAddress() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['address'];
    }

    public function setAddress($address) {
        $this->_informationArray['address'] = $address;
        $this->_changes['address'] = $this->_informationArray['address'];
    }


    public function getAddressType() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['addressType'];
    }

    public function setAddressType($addressType) {
        $this->_informationArray['addressType'] = $addressType;
        $this->_changes['addressType'] = $this->_informationArray['addressType'];
    }

    public function getAddressDetail() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_informationArray['addressDetail'];
    }

    public function setAddressDetail($addressDetail) {
        $this->_informationArray['addressDetail'] = $addressDetail;
        $this->_changes['addressDetail'] = $this->_informationArray['addressDetail'];
    }

    public function getTelecom() {
        if($this->_informationInit == 'N'){
            $this->_informationInit();
        }
        return $this->_info['telecom'];
    }

    public function setTelecom($telecom) {
        $this->_informationArray['telecom'] = $telecom;
        $this->_changes['telecom'] = $this->_informationArray['telecom'];
    }
}
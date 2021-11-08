<?php
namespace PL\Models\Client\Point\Snapshot;

use PL\Models\Source\Source;
use Exception;
use Phalcon\Db;
use Phalcon\DI;
use Phalcon\Http\Client\Provider\Curl as Curl;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Admin\Admin;
use PL\Models\Client\Point\Snapshot\Item\Container as ItemContainer;

class Snapshot extends AbstractSingleton {

    const Status_Complete   = 0;
    const Status_Standby    = 1;
    const Status_Cancel     = 2;
    const Status_Error      = 4;
    const Status_Processing = 5;

    const tableName = 'ClientPointSnapshot';


    protected $_itemContainer;

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


    public function getItemContainer() {
        if (isset($this->_itemContainer) == false) {
            $this->_itemContainer = new ItemContainer($this);
        }
        return $this->_itemContainer;
    }

    public function processing(){
        try{

            // 혹 모르니 30분 뒤? status check?

            if($this->getStatus() != self::Status_Standby){
                // 상태 오류
                return false;
            }

            $checkStartTime = $this->checkStartTime();
            if($checkStartTime == 'E'){
                // 시작이후 1시간 경과했다.

            }elseif($checkStartTime == 'N'){
                // 시작 유휴시간이 지나지 않았다.

            } else {
                $this->setStatus(self::Status_Processing);
                $this->saveChanges();
            }


            // get api info
            $sourceInstance = Source::getInstance(Source::Source_ElminSecu);
            if($sourceInstance->getApiInstanceByStream('up') == false){
                // upstream 미존재
                //return false;
                throw new Exception('Api Instance error.');
            }


            //$valuesArray['classificationId']         = classificationId;
            $postValues['key']              = $sourceInstance->getApiInstanceByStream('up')->getKey();
            $postValues['secret']           = $sourceInstance->getApiInstanceByStream('up')->getSecret();
            $postValues['function']         = 'getSnapshotClassificationResult';
            $postValues['classificationId'] = $this->getClassificationId();
            $postValues['ip']               = getIP();
            //$postValues['values']       = $valuesArray;

            $curl = new Curl();
            $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
            $curl->setOption(CURLOPT_HEADER, false);
            $curl->setOption(CURLOPT_TIMEOUT, 5000);
            $curlRetData = $curl->post($sourceInstance->getApiInstanceByStream('up')->getUrl(), $postValues);
            $curlRetBody = json_decode($curlRetData->body, true);

            var_dump($curlRetBody);
            exit;


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
                $this->_info['point'] = $curlRetBody['status'];
            } else {
                throw new Exception($curlRetBody['message']);
            }


        } catch (Exception $e) {
            return $e->getMessage();
        }


    }


    public function getId() {
        return $this->_info['id'];
    }

    public function getTitle() {
        return $this->_info['title'];
    }

    public function setTitle($title) {
        $this->_info['title']    = $title;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getMemo() {
        return $this->_info['memo'];
    }

    public function setMemo($memo) {
        $this->_info['memo']    = $memo;
        $this->_changes['memo'] = $this->_info['memo'];
    }

    public function getSecuId() {
        return $this->_info['secuId'];
    }

    public function setSecuId($secuId) {
        $this->_info['secuId']    = $secuId;
        $this->_changes['secuId'] = $this->_info['secuId'];
    }

    public function getClassificationId() {
        return $this->_info['classificationId'];
    }

    public function setClassificationId($classificationId) {
        $this->_info['classificationId']    = $classificationId;
        $this->_changes['classificationId'] = $this->_info['classificationId'];
    }

    public function getCount() {
        return $this->_info['count'];
    }

    public function setCount($count) {
        $this->_info['count']    = $count;
        $this->_changes['count'] = $this->_info['count'];
    }


    public function getTargetDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['startDate']));
    }

    public function getStartDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['startDate']));
    }

    public function setStartDate($startDate) {
        $this->_info['startDate']    = $startDate;
        $this->_changes['startDate'] = $this->_info['startDate'];
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
}
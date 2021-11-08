<?php
namespace PL\Models\Client\Setting;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;
use PL\Models\Util\Util;
use PL\Models\Client\Client;

class Setting extends AbstractSingleton {

    /**
     * 상수 설정
     */

    const Status_Complete  = 0; // 완료
    const Status_Pending   = 1; // 대기 기본설정
    const Status_Error     = 2; // 에러
    const Status_Cancel    = 4; // 취소
    const Status_Approve   = 5; // 메일발송 승인됨 발송예정 또는 진행중.


    protected $_clientInstance;
    protected $_filterContainer;

    const tableName = 'ClientSetting';

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

    public function getClientInstance(){
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getId());
        }
        return $this->_clientInstance;
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getCategory() {
        return $this->_info['category'];
    }

    public function setCategory($category) {
        $this->_info['category']    = $category;
        $this->_changes['category'] = $this->_info['category'];
    }

    public function getFieldFilter() {
        return $this->_info['fieldFilter'];
    }

    public function setFieldFilter($fieldFilter) {
        $this->_info['fieldFilter']    = $fieldFilter;
        $this->_changes['fieldFilter'] = $this->_info['fieldFilter'];
    }

    public function getSoundId() {
        return $this->_info['soundId'];
    }

    public function setSoundId($soundId) {
        $this->_info['soundId']    = $soundId;
        $this->_changes['soundId'] = $this->_info['soundId'];
    }

    public function getDailyNews() {
        return $this->_info['dailyNews'];
    }

    public function setDailyNews($dailyNews) {
        $this->_info['dailyNews']    = $dailyNews;
        $this->_changes['dailyNews'] = $this->_info['dailyNews'];
    }


}
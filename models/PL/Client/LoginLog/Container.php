<?php
namespace PL\Models\Client\LoginLog;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Client\Client;
use PL\Models\News\News;
use PL\Models\Util\Util;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(LoginLog::tableName);
        $this->setTableName(LoginLog::tableName);
    }

    public static function getTableNameStatic(){
        return LoginLog::tableName;
    }

    public static function getObjectInstanceStatic($date) : LoginLog {
        return LoginLog::getInstance($date);
    }

    public function getObjectInstance($date) : LoginLog {
        return LoginLog::getInstance($date);
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

    public function addNewNow($ip, $type) {
        $item['clientId']       = $this->getClientId();
        $item['countryId']      = 0;
        $item['country']        = '';
        $item['location']       = '';
        $item['ip']             = $ip;
        $item['type']           = $type;
        $item['regDate']        = Util::getDbNow('Y-m-d H:i:s', 'kst');
        $item['regTimestamp']   = Util::getDbNow('Y-m-d H:i:s', 'kst');
        $item['status']         = 1;
        //new Db\RawValue('NOW()');

        return $this->addNew($item);
    }

    public function getCountIp() {
        $result = $this->db->query('SELECT count(*) FROM ClientLoginLog WHERE clientId = ? and status = 0', array($this->_client->getId()));

        $data = $result->fetch();
        return $data[0];
    }

    /* 마지막 로그인 정보 가져오기 */
    public function getLastLoginInfo() {
        $result = $this->db->query('SELECT *  FROM `ClientLoginLog` WHERE `clientId` = ? ORDER BY `id` DESC LIMIT 1', array($this->getClientId()));
        $data = $result->fetch();
        if(!empty($data)) {
            return $this->getObjectInstance($data);
        }
        return array();
    }

    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }

}

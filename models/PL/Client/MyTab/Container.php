<?php
namespace PL\Models\Client\MyTab;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;
use PL\Models\News\News;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;
use PL\Models\Client\Client;

class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(MyTab::tableName);
        $this->setTableName(MyTab::tableName);
    }

    public static function getTableNameStatic(){
        return MyTab::tableName;
    }

    public static function getObjectInstanceStatic($date) : MyTab {
        return MyTab::getInstance($date);
    }

    public function getObjectInstance($date) : MyTab {
        return MyTab::getInstance($date);
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

    public function _isMyTab($myTabId){
        if(is_numeric($myTabId) !== true) return false;
        $myTabInstance = $this->getObjectInstance($myTabId);
        if($myTabInstance->getClientId() == $this->getClientId()){
            return $myTabInstance;
        } else {
            return false;
        }
    }

    public function getCountMyTab() {
        $result = $this->db->query('SELECT count(*) FROM ClientMyTab WHERE clientId = ? and status = 0', array($this->getClientId()));

        $data = $result->fetch();
        return $data[0];
    }

    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }
    
}

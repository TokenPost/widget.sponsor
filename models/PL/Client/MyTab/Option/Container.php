<?php
namespace PL\Models\Client\MyTab\Option;

use PL\Models\Client\MyTab\MyTab;
use Exception;
use PL\Models\News\News;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

class Container extends AbstractContainer {

    protected $_myTabId;
    protected $_myTabInstance;

    public function __construct(MyTab $myTabInstance) {

        if(is_null($myTabInstance) != true){
            $this->setMyTabInstance($myTabInstance);
        }
        parent::__construct(Option::tableName);
        $this->setTableName(Option::tableName);
    }

    public static function getTableNameStatic(){
        return Option::tableName;
    }

    public static function getObjectInstanceStatic($date) : Option {
        return Option::getInstance($date);
    }

    public function getObjectInstance($date) : Option {
        return Option::getInstance($date);
    }


    public function setMyTabInstance(MyTab $myTabInstance){
        $this->_myTabInstance = $myTabInstance;
        $this->setMyTabId($myTabInstance->getId());
    }

    public function getMyTabInstance() : MyTab {
        return $this->_myTabInstance;
    }

    public function getMyTabId(){
        return $this->_myTabId;
    }

    private function setMyTabId($myTabId){
        $this->_myTabId = $myTabId;
    }


    public function getCountMyTabOption() {
        $result = $this->db->query('SELECT count(*) FROM ClientMyTabOption WHERE myTabId = ? and status = 0', array($this->getMyTabId()));

        $data = $result->fetch();
        return $data[0];
    }


    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getMyTabId()) == true) {
            $where[] = 'clientMyTabId = ' . $this->getMyTabId();
        }

        return $where;
    }

}

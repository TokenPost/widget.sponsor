<?php
namespace PL\Models\Client\Point\Snapshot\Item;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Client\Client;
use PL\Models\Client\Point\Point;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;


/**
 * @todo: 미사용?
 */
class Container extends AbstractContainer {

    protected $_pointId;
    protected $_pointInstance;

    public function __construct(Point $pointInstance = null) {

        if(is_null($pointInstance) != true){
            $this->setPointInstance($pointInstance);
        }
        parent::__construct(Item::tableName);
        $this->setTableName(Item::tableName);
    }

    public static function getTableNameStatic(){
        return Item::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Item::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Item::getInstance($date);
    }


    public function getPointId(){
        return $this->_pointId;
    }

    private function setPointId($pointId){
        $this->_pointId = $pointId;
    }

    public function getPointInstance(){
        return $this->_pointInstance;
    }

    public function setPointInstance(Point $pointInstance){
        $this->_pointInstance = $pointInstance;
        $this->setPointId($pointInstance->getId());
    }





    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }

}

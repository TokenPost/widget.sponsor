<?php
namespace PL\Models\Client\Company\CertificationLog;

use PL\Models\Recruit\Item\Count\Count;
use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Recruit\Recruit;

use PL\Models\Recruit\Item\Item;
use PL\Models\Recruit\Item\Container as ItemContainer;

use PL\Models\Client\Company\Company;

class Container extends AbstractContainer {

    protected $_itemId;
    protected $_itemInstance;

    protected $_clientId;


    public function __construct() {
        parent::__construct(CertificationLog::tableName);
        $this->setTableName(CertificationLog::tableName);
    }

    public static function getTableNameStatic(){
        return CertificationLog::tableName;
    }

    public static function getObjectInstanceStatic($date) : CertificationLog {
        return CertificationLog::getInstance($date);
    }

    public function getObjectInstance($date) : CertificationLog {
        return CertificationLog::getInstance($date);
    }


    public function getItemInstance() {
        return $this->_itemInstance;
    }

    public function setItemInstance($itemInstance) {
        $this->_itemInstance = $itemInstance;
    }

    public function getItemId(){
        return $this->_itemId;
    }

    public function setItemId($itemId){
        $this->_itemId = $itemId;
    }

    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function init($replace = '') {
        if($replace != ''){
            $search = $replace;
        } else {
            $search = ' * ';
        }
        $query = $this->getQuery();

        $query = str_replace($search, ' count(*) ', $query);

        //$data = $this->getDB()->query($query)->fetch();;
        $data = $this->getSearchDB()->query($query)->fetch();;
        $count = $data[0];

        $this->calcInit($count);
    }

    public function getItemsPBF(){
        $where = array();
        if($this->getItemId() >= 1) $where[] = '`itemId` = '  . $this->getItemId();
        if($this->getClientId() >= 1) $where[] = '`clientId` = '  . $this->getClientId();
        return $where;
    }
}

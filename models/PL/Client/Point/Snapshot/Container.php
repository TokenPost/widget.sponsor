<?php
namespace PL\Models\Client\Point\Snapshot;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;


class Container extends AbstractContainer {


    public function __construct() {
        parent::__construct(Snapshot::tableName);
        $this->setTableName(Snapshot::tableName);
    }

    public static function getTableNameStatic(){
        return Snapshot::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Snapshot::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Snapshot::getInstance($date);
    }


    public function getItemsPBF() {
        $where = array();
        return $where;
    }

}

<?php
namespace PL\Models\Donation;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

class Container extends AbstractContainer {

    public function __construct() {
        parent::__construct(Donation::tableName);
        $this->setTableName(Donation::tableName);
    }

    public static function getTableNameStatic(){
        return Donation::tableName;
    }

    public static function getObjectInstanceStatic($date) : Donation {
        return Donation::getInstance($date);
    }

    public function getObjectInstance($date) : Donation {
        return Donation::getInstance($date);
    }

}

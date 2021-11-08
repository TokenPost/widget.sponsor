<?php
namespace PL\Models\Client\Temp;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;


class Container extends AbstractContainer {

    protected $_mode;

    protected $_admin;

    public function __construct() {
        parent::__construct(Temp::tableName);
        $this->setTableName(Temp::tableName);
    }

    public static function getTableNameStatic(){
        return Temp::tableName;
    }

    public static function getObjectInstanceStatic($date) : Temp {
        return Temp::getInstance($date);
    }

    public function getObjectInstance($date) : Temp {
        return Temp::getInstance($date);
    }


}

<?php
namespace PL\Models\Site;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;


class Container extends AbstractContainer {

    public function __construct() {
        parent::__construct(Site::tableName);
        $this->setTableName(Site::tableName);
    }

    public static function getTableNameStatic(){
        return Site::tableName;
    }

    public static function getObjectInstanceStatic($date) : Site {
        return Site::getInstance($date);
    }

    public function getObjectInstance($date) : Site {
        return Site::getInstance($date);
    }
}
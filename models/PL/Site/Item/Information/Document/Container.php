<?php
namespace PL\Models\Site\Item\Information\Document;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;


class Container extends AbstractContainer {

    public function __construct() {
        parent::__construct(Document::tableName);
        $this->setTableName(Document::tableName);
    }

    public static function getTableNameStatic(){
        return Document::tableName;
    }

    public static function getObjectInstanceStatic($date) : Document {
        return Document::getInstance($date);
    }

    public function getObjectInstance($date) : Document {
        return Document::getInstance($date);
    }
}
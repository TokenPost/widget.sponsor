<?php
namespace PL\Models\Client\MyCalendar;

use Phalcon\Mvc\Model;

class Client extends Model {

    public function initialize(){

        $this->setSource("Client");

    }
} 
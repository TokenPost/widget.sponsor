<?php
namespace PL\Models\Client\MyCalendar;

use Phalcon\Mvc\Model;

class MyCalendar extends Model {

    public function initialize(){

    $this->setSource("ClientMyCalendar");

    $this->belongsTo("clientId",  __NAMESPACE__ . '\Client', "id");
    $this->belongsTo("calendarId", "CalendarItems", "id");

    }
} 
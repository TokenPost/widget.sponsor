<?php
namespace PL\Models\Client\Statistic\Info;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;

class Container extends AbstractContainer {

    protected $_year;

    public function __construct() {
        parent::__construct(Info::tableName);
        $this->setTableName(Info::tableName);
    }

    public static function getTableNameStatic(){
        return Info::tableName;
    }

    public static function getObjectInstanceStatic($date) : Info {
        return Info::getInstance($date);
    }

    public function getObjectInstance($date) : Info {
        return Info::getInstance($date);
    }

    public function setYear($year){
        $this->_year = $year;
    }

    public function addCount($i = 1, $ageCode) {
        $this->db->query('UPDATE ' . Info::tableName . ' set `count` = `count` + ? WHERE `id` = ?', array($i, $ageCode));
    }

    public function subtractCount($i = 1, $ageCode) {
        $this->db->query('UPDATE ' . Info::tableName . ' set `count` = `count` - ? WHERE `id` = ?', array($i, $ageCode));
    }

    public function addMaleCount($i = 1, $id) {
        $this->db->query('UPDATE ' . Info::tableName . ' set `male` = `male` + ? WHERE `id` = ?', array($i, $id));
    }

    public function subtractMaleCount($i = 1, $id) {
        $this->db->query('UPDATE ' . Info::tableName . ' set `male` = `male` - ? WHERE `id` = ?', array($i, $id));
    }

    public function addFemaleCount($i = 1, $id) {
        $this->db->query('UPDATE ' . Info::tableName . ' set `female` = `female` + ? WHERE `id` = ?', array($i, $id));
    }

    public function subtractFemaleCount($i = 1, $id) {
        $this->db->query('UPDATE ' . Info::tableName . ' set `female` = `female` - ? WHERE `id` = ?', array($i, $id));
    }

    public function findFirst($year='', $gender='') {
        if($year == '') return null;
        if($gender == '') return null;

        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `year` = ? LIMIT 1";
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query, array($year))->fetch();

        if(is_array($data) == true) {
            $ageInstance = static::getObjectInstanceStatic($data);
            $this->addCount(1, $ageInstance->getId());
            if($gender == Info::Gender_Male) {
                $this->addMaleCount(1, $ageInstance->getId());
            } else {
                $this->addFemaleCount(1, $ageInstance->getId());
            }
        } else {
            // create
            $createItem = $this->create($year);
            if(is_null($createItem) != true) {
                $this->addCount(1, $createItem->getId());
                if($gender == Info::Gender_Male) {
                    $this->addMaleCount(1, $createItem->getId());
                } else {
                    $this->addFemaleCount(1, $createItem->getId());
                }
            } else {
                return null;
            }
        }
    }

    public function create($year) {
        if($year == '') return null;

        $newItem = array();
        $newItem['year']    = $year;
        $newItem['code']    = $year;
        $newItem['count']   = 0;
        $newItem['male']    = 0;
        $newItem['female']  = 0;
        $newItem['regDate'] = Util::getDbNow();

        $ret = $this->addNew($newItem);
        if($ret >= 1) return self::isItem($ret);
        return null;
    }

    public function getGenderSummary() {
        $db     = DI::getDefault()->getShared('db');

        $query  = "
            SELECT
                SUM(`male`) as male_sum,
                SUM(`female`) as female_sum         
            FROM 
                ClientStatisticInfo
        ";
        $data = $db->query($query)->fetch();

        return $data;
    }
	
	public function getAgeSummary() {
        $db     = DI::getDefault()->getShared('db');

        $query  = "
            SELECT 
				CASE 
                    WHEN age >= 10 AND age < 20 THEN '10' 
                    WHEN age < 30 THEN '20' 
					WHEN age < 40 THEN '30' 
					WHEN age < 50 THEN '40' 
					WHEN age < 60 THEN '50' 
					WHEN age < 70 THEN '60'
					WHEN age < 80 THEN '70'
					WHEN age < 10 OR age >= 90 THEN 'ETC'
				END AS age_group, 
                SUM(`male`) as male_total,
                SUM(`female`) as female_total
			FROM 
				(
					SELECT 
						FLOOR(date_format(now(), '%Y')-substring(year,1,4)) as age, male, female 
					FROM ClientStatisticInfo
				) A 
			GROUP BY age_group	
        ";
        $data = $db->query($query)->fetchAll();
        return $data;
    }
}
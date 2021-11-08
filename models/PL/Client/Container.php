<?php
namespace PL\Models\Client;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\News\News;
use PL\Models\Util\Util;

class Container extends AbstractContainer {

    protected $_mode;

    protected $_admin;

    public function __construct() {
        parent::__construct(Client::tableName);
        $this->setTableName(Client::tableName);
    }

    public static function getTableNameStatic(){
        return Client::tableName;
    }

    public static function getObjectInstanceStatic($date) : Client {
        return Client::getInstance($date);
    }

    public function getObjectInstance($date) : Client {
        return Client::getInstance($date);
    }

    public function addNewCommon($item){
        if(isset($item['password']) == true && $item['password'] != ''){
            $data             = $this->getDb()->query('SELECT PASSWORD(?)', array($item['password']))->fetch();
            $item['password'] = $data[0];
        }
        return $item;
    }

    public static function findUserEmail($name, $birthDate, $phone) {
        $query = "SELECT a.* FROM `Client` as a INNER JOIN `ClientInformation` as b ON a.id = b.id ";
        $query .= "WHERE b.birthDate = ? AND b.phone = ? AND a.firstName = ? ORDER BY a.regDate DESC";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->get('master/slave db');
        $result = $db->query($query, array($birthDate, $phone, $name))->fetchAll();

        if ($result === false) return array();

        $return = array();
        foreach ($result as $var) {
            $return[] = self::getObjectInstanceStatic($var);
        }

        return $return;
    }

    public static function checkNickname($nickname, $clientId = 0) {

        //preg_match('/(^[ㄱ-ㅎ가-힣A-Za-z0-9])/', $nickname, $scMatches);

        //var_dump($scMatches);

        switch(SITE_LANGUAGE_CODE){
            case 'ko':
                // 한글 30글자로 받아져서 정규식은 30.
                preg_match('/^[ㄱ-ㅎ가-힣A-Za-z0-9\s_-]{2,30}$/', $nickname, $matches);
                break;
            default:
                // en이라 한글 제외
                //최대 20 글자
                preg_match('/^[A-Za-z0-9\s_-]{4,20}$/', $nickname, $matches);
                break;
        }
        //preg_match('/^([ㄱ-ㅎ가-힣A-Za-z0-9])$/', $nickname, $matches);
        //preg_match('/^[ㄱ-ㅎ가-힣A-Za-z0-9]{2,16}$/', $nickname, $matches);
/*
        if(sizeof($matches) == 0) return 'P';
        var_dump($matches);
        exit;*/
        $minByte = 4;
        $maxByte = 20;
        $byte = strlen(iconv('UTF-8', 'CP949', $nickname));

        //var_dump($matches);
        if ($nickname == '') {
            $result = 'E';
        } else if ($byte < $minByte) {
            $result = 'L';
        } else if ($byte > $maxByte) {
            $result = 'G';
        } else {
            if(sizeof($matches) == 0) return 'P';

            //$result = ClientContainer::checkNickname($nickname, $clientId);
            // check latest user nickname
            $clientInstance = null;
            if(is_numeric($clientId) == true && $clientId >= 1){
                $clientInstance = self::isItem($clientId);
            }

            if(is_null($clientInstance) !== true){
                if($nickname == $clientInstance->getNickname()) return 'M';
            }

            // check ban list
            $query = "SELECT * FROM `ClientNickBan` WHERE `text` = ?";
            $db     = DI::getDefault()->getShared('db');

            //$db     = DI::getDefault()->get('master/slave db');
            $queryResult = $db->query($query, array($nickname))->fetchAll();

            if(sizeof($queryResult) === 0){
                // 0 개다. 괜찮다.
            } else {
                return 'B';
            }


            $query = "SELECT * FROM `Client` WHERE `nickname` = ?";

            //$db     = DI::getDefault()->get('master/slave db');
            $queryResult = $db->query($query, array($nickname))->fetchAll();

            if(sizeof($queryResult) === 0){
                // 0 개다. 괜찮다.
                $result = 'Y';
            } else {
                $result = 'N';
            }

        }
        return $result;


    }


    // 이메일 찾기
    // 이름, 전화번호로 정보 찾기
    public function findClientInfo($type, $email, $name, $phone){
        if ($type == "") return false;
        if ($name == "") return false;
        if ($phone == "") return false;

        $db     = DI::getDefault()->getShared('db');
        $query = "SELECT * FROM `Client` WHERE `namePassword` = PASSWORD(?) AND `encPhone` = PASSWORD(?)";
        $arrayData = array($name, $phone);
        if($type === 'changePassword') {
            if ($email == "") return false;
            $query .= " AND `email` = ?";
            array_push($arrayData, $email);
        }
        $result = $db->query($query, $arrayData)->fetch();

        if(is_array($result) == true) {
            // 성공
            return self::getObjectInstanceStatic($result);
        }
        return false;

    }

    //가입,탈퇴 연/월/주/일 횟수 조회
    public function getUserJoinLeaveStatistics() {
        $curY = date('Y');
        $curM = date('m');
        $curD = date('d');
        $curW = date('w');

        //년간
        $startPreY = date('Y-01-01', mktime(0, 0, 0, 1, 1, $curY-1));
        $startCurY = $endPreY = date('Y-01-01');
        $endCurY = date('Y-01-01', mktime(0, 0, 0, 1, 1, $curY+1));
        //월간
        $startPreM = date('Y-m-01', mktime(0, 0, 0, $curM-1, 1, $curY));
        $startCurM = $endPreM = date('Y-m-01');
        $endCurM = date('Y-m-01', mktime(0, 0, 0, $curM+1, 1, $curY));
        //주간
        $startPreW = date('Y-m-d', mktime(0, 0, 0, $curM, $curD-$curW-7, $curY));
        $startCurW = $endPreW = date('Y-m-d', mktime(0, 0, 0, $curM, $curD-$curW, $curY));
        $endCurW = date('Y-m-d', mktime(0, 0, 0, $curM, $curD-$curW+7, $curY));
        //일간
        $startPreD = date('Y-m-d', mktime(0, 0, 0, $curM, $curD-1, $curY));
        $startCurD = $endPreD = date('Y-m-d');
        $endCurD = date('Y-m-d', mktime(0, 0, 0, $curM, $curD+1, $curY));
        
        $db     = DI::getDefault()->getShared('db');

        $query  = "
            SELECT 
                SUM( IF (type = 'preYJoinCount',`cnt`, 0) ) AS preYJoinCount,
                SUM( IF (type = 'preMJoinCount',`cnt`, 0) ) AS preMJoinCount,
                SUM( IF (type = 'preWJoinCount',`cnt`, 0) ) AS preWJoinCount,
                SUM( IF (type = 'preDJoinCount',`cnt`, 0) ) AS preDJoinCount,
                SUM( IF (type = 'curYJoinCount',`cnt`, 0) ) AS curYJoinCount,
                SUM( IF (type = 'curMJoinCount',`cnt`, 0) ) AS curMJoinCount,
                SUM( IF (type = 'curWJoinCount',`cnt`, 0) ) AS curWJoinCount,
                SUM( IF (type = 'curDJoinCount',`cnt`, 0) ) AS curDJoinCount,
                SUM( IF (type = 'preYLeaveCount', `cnt`, 0) ) AS preYLeaveCount,
                SUM( IF (type = 'preMLeaveCount', `cnt`, 0) ) AS preMLeaveCount,
                SUM( IF (type = 'preWLeaveCount', `cnt`, 0) ) AS preWLeaveCount,
                SUM( IF (type = 'preDLeaveCount', `cnt`, 0) ) AS preDLeaveCount,
                SUM( IF (type = 'curYLeaveCount', `cnt`, 0) ) AS curYLeaveCount,
                SUM( IF (type = 'curMLeaveCount', `cnt`, 0) ) AS curMLeaveCount,
                SUM( IF (type = 'curWLeaveCount', `cnt`, 0) ) AS curWLeaveCount,
                SUM( IF (type = 'curDLeaveCount', `cnt`, 0) ) AS curDLeaveCount
            FROM (
                SELECT 'preYJoinCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.regDate >= '" .$startPreY  ."' and A.regDate <  '" .$endPreY  ."'
                UNION
                SELECT 'preMJoinCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.regDate >= '" .$startPreM  ."' and A.regDate <  '" .$endPreM  ."'
                UNION
                SELECT 'preWJoinCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.regDate >= '" .$startPreW  ."' and A.regDate <  '" .$endPreW  ."'
                UNION
                SELECT 'preDJoinCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.regDate >= '" .$startPreD  ."' and A.regDate <  '" .$endPreD  ."'
                UNION
                SELECT 'curYJoinCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.regDate >= '" .$startCurY  ."' and A.regDate <  '" .$endCurY  ."'
                UNION
                SELECT 'curMJoinCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.regDate >= '" .$startCurM  ."' and A.regDate <  '" .$endCurM  ."'
                UNION
                SELECT 'curWJoinCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.regDate >= '" .$startCurW  ."' and A.regDate <  '" .$endCurW  ."'
                UNION
                SELECT 'curDJoinCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.regDate >= '" .$startCurD  ."' and A.regDate <  '" .$endCurD  ."'
                UNION
                SELECT 'preYLeaveCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.leaveDate >= '" .$startPreY  ."' and A.leaveDate <  '" .$endPreY  ."'
                UNION
                SELECT 'preMLeaveCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.leaveDate >= '" .$startPreM  ."' and A.leaveDate <  '" .$endPreM  ."'
                UNION
                SELECT 'preWLeaveCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.leaveDate >= '" .$startPreW  ."' and A.leaveDate <  '" .$endPreW  ."'
                UNION
                SELECT 'preDLeaveCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.leaveDate >= '" .$startPreD  ."' and A.leaveDate <  '" .$endPreD  ."'
                UNION
                SELECT 'curYLeaveCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.leaveDate >= '" .$startCurY  ."' and A.leaveDate <  '" .$endCurY  ."'
                UNION
                SELECT 'curMLeaveCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.leaveDate >= '" .$startCurM  ."' and A.leaveDate <  '" .$endCurM  ."'
                UNION
                SELECT 'curWLeaveCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.leaveDate >= '" .$startCurW  ."' and A.leaveDate <  '" .$endCurW  ."'
                UNION
                SELECT 'curDLeaveCount' as type, COUNT(A.id) as cnt  FROM Client A WHERE A.leaveDate >= '" .$startCurD  ."' and A.leaveDate <  '" .$endCurD  ."'
            ) A
        ";
        $data = $db->query($query)->fetch();

        if (!empty($data)) return $data;
        else return [
            'preYJoinCount'=> 0,
            'preMJoinCount'=> 0,
            'preWJoinCount'=> 0,
            'preDJoinCount'=> 0,
            'curYJoinCount'=> 0,
            'curMJoinCount'=> 0,
            'curWJoinCount'=> 0,
            'curDJoinCount'=> 0,
            'preYLeaveCount'=>0,
            'preMLeaveCount'=>0,
            'preWLeaveCount'=>0,
            'preDLeaveCount'=>0,
            'curYLeaveCount'=>0,
            'curMLeaveCount'=>0,
            'curWLeaveCount'=>0,
            'curDLeaveCount'=>0
        ];
    }

    public function getLoginTypeStatistics() {
        $curY = date('Y');
                
        $db     = DI::getDefault()->getShared('db');

        $query  = "
            SELECT 
                A.loginType, COUNT(A.id) as cnt  
            FROM 
                Client A 
            WHERE 
                (
                    A.status='" .Client::Status_Active ."' 
                    OR A.status='" .Client::Status_Mail ."'
                ) 
            Group BY A.loginType
            Order By cnt desc
        ";
        $data = $db->query($query)->fetchAll();

        return $data;
    }
}

<?php
namespace PL\Models\Site\Item;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Util\HTML;
use Phalcon\Http\Client\Provider\Curl as Curl;

use PL\Models\Site\Site;

use PL\Models\Site\Item\Reward\Activity\Activity as SiteItemRewardActivity;

class Container extends AbstractContainer {

    protected $_siteId;
    protected $_status;
    protected $_ownerId;
    protected $_domain;

    protected $_siteInstance;

    public function __construct(Site $siteInstance = null) {
        parent::__construct(Item::tableName);
        $this->setTableName(Item::tableName);

        if(is_null($siteInstance) == false){
            $this->setSiteInstance($siteInstance);
            $this->setSiteId($siteInstance->getId());
        }
    }

    public static function getTableNameStatic(){
        return Item::tableName;
    }

    public static function getObjectInstanceStatic($data) : Item {
        return Item::getInstance($data);
    }

    public function getObjectInstance($data) : Item {
        return Item::getInstance($data);
    }

    public function getSiteInstance(){
        return $this->_siteInstance;
    }

    public function setSiteInstance($siteInstance){
        $this->_siteInstance = $siteInstance;
    }

    public function getSiteId(){
        return $this->_siteId;
    }

    public function setSiteId($siteId){
        $this->_siteId = $siteId;
    }

    public function setStatus($status){
        $this->_status = $status;
    }


    public function getOwnerId(){
        return $this->_ownerId;
    }

    public function setOwnerId($ownerId){
        $this->_ownerId = $ownerId;
    }

    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getSiteId()) == true) $where[] = '`siteId` = '. $this->getSiteId();
//        if(Util::isInteger($this->getOwnerId()) == true) $where[] = '`ownerId` = '. $this->getOwnerId();
        return $where;
    }

    /**
     * Extract metatags from a webpage
     */
    public function getContentWithAgent($url) {
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36';

        try{
            $curl = new Curl();
            $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
            $curl->setOption(CURLOPT_HEADER, false);
            $curl->setOption(CURLOPT_TIMEOUT, 5000);
            $curl->setOption(CURLOPT_USERAGENT, $agent);
            $curlRetData = $curl->get($url);

            return $curlRetData->body;

        } catch (Exception $e){
            return '';
        }

    }

    /**
     * ????????? ????????????
     * echo getDomain("http://example.com"); // outputs 'example.com'
     * echo getDomain("http://www.example.com"); // outputs 'example.com'
     * echo getDomain("http://mail.example.co.uk"); // outputs 'example.co.uk'
     *
     * preg_match()
     * ????????? ?????? : ????????? ??????
     * ????????? ?????? : ?????? ?????? ?????????
     * ????????? ?????? : ?????? ?????? ??????, ????????? ?????? ????????? ??????
     * ????????? : ????????? ???????????? 1, ???????????? 0
     */
    public function getDomainInfo($url) {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return false;
    }

    // Domain Verification
    // meta ?????? ?????????????????? ??????
    public function parseDomain($url, $verification, $itemId) {

        if(APPLICATION_ENV == 'dev' || APPLICATION_ENV == 'stage') {
            $verification = "12B9218BDBA3BB65CFC04863ADD739B9"; // ????????????
        }

        if($url == '' || !(strpos($url, 'http://') == 0 || !strpos($url, 'https://') == 0)) {
            throw new Exception('http(s)??? ???????????? ?????? URL??? ??????????????????. code : 1');
        }

        $itemInstance = $this->getObjectInstance($itemId);
        if($itemInstance instanceof Item != true) throw new Exception('URL??? ???????????? ????????????.');

        $html = $this->getContentWithAgent($url);
        if($html == '') {
            // ????????? URL ?????? ??????????
            throw new Exception('URL??? ???????????? ????????????. code : 2');
        }


        $domain = $this->getDomainInfo($url);
        if($domain == false) throw new Exception('URL??? ???????????? ????????????. code : 3');
        if(APPLICATION_ENV != 'dev' && APPLICATION_ENV != 'stage') {
            if ($itemInstance->getDomain() != $domain) throw new Exception('URL??? ???????????? ????????????. code : 4');
        }

        // verification ??? ??????????????? ??????
        $html = substr($html, 0, strpos($html, '<body>'));
        $htmlInstance = new HTML($html);

        $result = false;
        foreach ($htmlInstance->getRows('/html/head/meta') as $key => $var) {
            if(APPLICATION_ENV != 'dev' && APPLICATION_ENV != 'stage') {
                if($var->getAttr('//meta', 'name') == 'publishapi') {
                    if($var->getAttr('//meta', 'content') == $verification){
                        $result = true;
                        break;
                    }
                }
            } else {
                $result = true;
            }
        }

        return $result;
    }

    // html upload verification
    // ?????? ???????????? ????????? ?????? explode ???????????????
    public function parseHtmlUpload($url, $verification, $itemId) {

        if(APPLICATION_ENV == 'dev' || APPLICATION_ENV == 'stage') {
            $verification = 'google094709ebaaca7d17.html'; // ????????????
        } else {
            $verification = 'widget' . $verification . '.html';
        }

        if($url == '' || !(strpos($url, 'http://') == 0 || !strpos($url, 'https://') == 0)) {
            throw new Exception('http(s)??? ???????????? ?????? URL??? ??????????????????. code : 1');
        }

        $itemInstance = $this->getObjectInstance($itemId);
        if($itemInstance instanceof Item != true) throw new Exception('URL??? ???????????? ????????????. code : 2');

        $domain = $this->getDomainInfo($url);
        if($domain == false) throw new Exception('URL??? ???????????? ????????????. code : 3');
        if(APPLICATION_ENV != 'dev' && APPLICATION_ENV != 'stage') {
            if ($itemInstance->getDomain() != $domain) throw new Exception('URL??? ???????????? ????????????. code : 4');
        }

        $setUrl = $url.$verification;
        $ret = $this->getContentWithAgent($setUrl);

        if($ret == '') {
            return false;
        } else {
            // ????????? ??????
            if(APPLICATION_ENV == 'dev' || APPLICATION_ENV == 'stage') {
                // ????????? ??? ==> google-site-verification: google81ac697b0ff5244e.html
                $explode = explode('google-site-verification:', $ret);
            } else {
                // ????????? ??? ==> widget-verification: widgetCbNLuNXcCZfQqJDBFuKI*nOjkkOgRIR*.html
                $explode = explode('widget-verification:', $ret);
            }

            $explodeRet = trim($explode[1]);

            if($verification != $explodeRet) {
                // ?????? ??????
                return false;
            }else {
                // ?????? ??????
                return true;
            }
        }
    }

    // DNS txt record ??????
    // google-site-verification=mGAO6NIeyLcPuoLdS3FF_UzyodVgxaBipgQskeRd3FY
    public function parseDnsRecord($url, $verification, $itemId) {

        if(APPLICATION_ENV == 'dev' || APPLICATION_ENV == 'stage') {
            $url = 'tokenpost.kr';  // ????????????
            $verification = 'google-site-verification=mGAO6NIeyLcPuoLdS3FF_UzyodVgxaBipgQskeRd3FY'; // ????????????
        } else {
            // wiget-site-verification=CbNLuNXcCZfQqJDBFuKI*nOjkkOgRIR*
            $verification = 'wiget-site-verification=' . $verification;
        }

        if($url == '' || !(strpos($url, 'http://') == 0 || !strpos($url, 'https://') == 0)) {
            throw new Exception('http(s)??? ???????????? ?????? URL??? ??????????????????.');
        }

        $itemInstance = $this->getObjectInstance($itemId);
        if($itemInstance instanceof Item != true) throw new Exception('URL??? ???????????? ????????????.');

        $domain = $this->getDomainInfo($url);
        if($domain == false) throw new Exception('URL??? ???????????? ????????????. code : 3');
        if(APPLICATION_ENV != 'dev' && APPLICATION_ENV != 'stage') {
            if ($itemInstance->getDomain() != $domain) throw new Exception('URL??? ???????????? ????????????. code : 4');
        }

        $res = dns_get_record($url, DNS_TXT);
        $result = false;
        foreach($res as $ar){
            foreach($ar as $key=>$val){
                if($key == 'txt') {
                    if($val == $verification) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }


    /**
     * ???????????? ?????? ??????, ?????? ?????? ?????? ??? ??????
     */
    public function getSiteLimits() {

        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
          SELECT 
            a.id, a.name, a.domain,
            b.`limit` as 'readlimit',
            c.`limit` as 'referrallimit'
            FROM 
              `SiteItem` a
              INNER JOIN `SiteItemReward` b ON a.id = b.itemId and b.code='articleView'
              INNER JOIN `SiteItemReward` c ON a.id = c.itemId and c.code='referral'
            WHERE a.status = 0
            ORDER BY a.id 
EOD;

        $data = $db->query($query)->fetchAll();

        return $data;
    }


    /**
     * ????????? ???????????? ?????? ??????, ?????? ??? ??????
     *
     * @param int $clientId
     * @param string $date
     * @return array
     */
    public function getUserSiteReadReferralCounts($clientId=0, $date='') {

        if($clientId == 0) return array();
        if($date == "") return array();

        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT 
              a.itemId, sum(readcount) as 'readcount', sum(referralcount) as 'referralcount'
            FROM
            (
                SELECT 
                  a.itemId, count(b.id) as 'readcount', 0 as 'referralcount'
                FROM 
                    `SiteItemRewardActivity` a 
                    JOIN `SiteItemReward` b ON a.rewardId = b.id and b.code='articleView'
                WHERE 
                   a.clientId = ? AND a.isReward = ? AND a.date = ?                   
                GROUP BY a.itemId
                
                UNION
                 
                SELECT 
                  a.itemId, 0 as 'readcount', count(b.id) as 'referralcount'
                FROM 
                    `SiteItemRewardActivity` a 
                    JOIN `SiteItemReward` b ON a.rewardId = b.id and b.code='referral'
                WHERE 
                    a.clientId = ? AND a.isReward = ? AND a.date = ?            
                GROUP BY a.itemId
            ) a 
            GROUP BY a.itemId
            ORDER BY readcount DESC, referralcount DESC
EOD;

        $condition = array($clientId, SiteItemRewardActivity::Reward_Receive, trim($date), $clientId, SiteItemRewardActivity::Reward_Receive, trim($date));
        $data = $db->query($query, $condition)->fetchAll();

        $result = array();
        if(!empty($data)) {
            foreach ($data as $idx => $val) {
                $temp = array();
                $temp['name'] = $val['name'];
                $temp['readcount'] = $val['readcount'];
                $temp['referralcount'] = $val['referralcount'];
                $result[$val['itemId']] = $temp;
            }
        }
        return $result;

    }

    /**
     * ?????? ??????
     */
    public function getActivityStatistics($clientId=0, $today='', $beforeMonthDay='') {
        if($clientId==0) return array();
        if($today == "") return array();
        if($beforeMonthDay == "") return array();

        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT 
                a.id, a.name, COUNT(c.id) as 'receivecount'
            FROM 
                `SiteItem` a
            INNER JOIN `SiteItemReward` b ON a.id = b.itemId and b.code='articleView'
            JOIN `SiteItemRewardActivity` c 
                ON a.id = c.itemId 
                AND c.clientId = ? 
                AND c.isReward = ?
                BETWEEN ? AND ?
            WHERE a.status = ?
            GROUP BY a.id
            ORDER BY receivecount DESC
            LIMIT 5
EOD;
        $condition = array($clientId, SiteItemRewardActivity::Reward_Receive, trim($beforeMonthDay), trim($today), Item::Status_Active);
        $data = $db->query($query, $condition)->fetchAll();

        $result = array();
        if(!empty($data)) {
            foreach ($data as $idx => $val) {
                $temp = array();
                $temp['id'] = $val['id'];
                $temp['name'] = $val['name'];
                $temp["receivecount"] = $val["receivecount"];
                $temp["ranking"] = $idx + 1;
                $result[] = $temp;
            }

        }
        return $result;
    }


    /*
     * ???????????? or ?????????
     * */
    public function checkExistSite($siteName='', $siteDomain='') {
        if($siteName == '') return null;
        if($siteDomain == '') return null;

        $db     = DI::getDefault()->getShared('db');
        $query  = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `name` = ? OR `domain` = ?";
        $condition = array($siteName, $siteDomain);
        $data = $db->query($query, $condition)->fetchAll();

        if(empty($data) == true) {
            // not exist
            return true;
        } else {
            // exist
            return false;
        }
    }







}
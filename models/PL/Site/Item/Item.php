<?php
namespace PL\Models\Site\Item;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Filter\Container as FilterContainer;
use PL\Models\Filter\Filter;
use PL\Models\Site\Item\Point\Point as SiteItemPoint;
use PL\Models\Site\Item\Point\Container as SiteItemPointContainer;

use PL\Models\Site\Item\Host\Host;
use PL\Models\Util\Util;
use PL\Models\Site\Site;
use PL\Models\Site\Item\Information\Container as InformationContainer;
use PL\Models\Site\Item\Widget\Container as WidgetContainer;
use PL\Models\Site\Item\Reward\Reward as Reward;
use PL\Models\Site\Item\Reward\Container as RewardContainer;

use PL\Models\Site\Item\Reward\Activity\Container as ActivityContainer;

use PL\Models\Site\Item\Host\Container as HostContainer;
use PL\Models\Site\Item\Page\Container as PageContainer;


use PL\Models\Site\Item\Point\Swap\Container as SiteItemPointSwapContainer;

use PL\Models\Site\Item\Point\Address\Container as SiteItemPointAddressContainer;

use PL\Models\Site\Item\Point\Transaction\Container as TransactionContainer;

use PL\Models\Site\Item\Point\Address\Address as SiteItemPointAddress;
use PL\Models\Site\Item\Point\Address\Container as SiteItemPointAdrdessContainer;


class Item extends AbstractSingleton {

    const tableName = 'SiteItem';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    const Type_Press = 1;       // 언론사
    const Type_General = 2;     // 일반

    const Bookmark_Active = 0;      // 북마크 활성화
    const Bookmark_Inactive = 1;    // 북마크 비활성화

    const Alarm_On      = 0;    // 알림 ON (자동 충전 알림, 한도 알림)
    const Alarm_Off     = 1;    // 알림 OFF (자동 충전 알림, 한도 알림)

    const Alarm_Type_Text       = 1;    // 문자
    const Alarm_Type_Email      = 2;    // 이메일
    const Alarm_Type_All        = 3;    // 문자 + 이메일

    const Verification_Complete = 0;      // 도메인 인증 완료
    const Verification_Incomplete = 1;    // 도메인 인증 미완료

    // 도메인 인증 방법
    const VType_Meta = 1;
    const VType_Html = 2;
    const VType_Domain = 3;

    protected $_siteInstance;
    protected $_informationInstance;
    protected $_widgetInstance;
    protected $_rewardInstance;
    protected $_hostInstance;

    protected $_pointInstance;

    protected $_rewardContainer;
    protected $_informationContainer;
    protected $_widgetContainer;
    protected $_activityContainer;
    protected $_hostContainer;
    protected $_pageContainer;
    protected $_siteItemPointContainer;
    protected $_swapContainer;

    protected $_pointAddressContainer;
    protected $_pointAddressInstance;

    protected $_transactionContainer;

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (Util::isInteger($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getSiteInstance() {
        if (isset($this->_siteInstance) == false) {
            $this->_siteInstance = Site::getInstance($this->getSiteId());
        }
        return $this->_siteInstance;
    }

    public function getInformationInstance() {
        if (isset($this->_informationInstance) == false) {
            $this->_informationInstance = $this->getInformationContainer()->getOne();
        }
        return $this->_informationInstance;
    }

    public function getWidgetInstance() {
        if (isset($this->_widgetInstance) == false) {
            $this->_widgetInstance = WidgetContainer::isItem($this->getId(), 'itemId');
        }
        return $this->_widgetInstance;
    }

    public function getHostInstance() {
        if (isset($this->_hostInstance) == false) {
            $this->_hostInstance = Host::getInstance($this->getId(), 'itemId');
        }
        return $this->_hostInstance;
    }

    public function getPointInstance($tokenId) {

        $filterContainer = new FilterContainer();
        $filterContainer->add(new Filter('siteItemId', '=', $this->getId()));
        $filterContainer->add(new Filter('tokenId', '=', $tokenId));

        $siteItemPointContainer = new SiteItemPointContainer();
        $siteItemPointContainer->setFilterContainer($filterContainer);

        return $siteItemPointContainer->getOne();
    }


    public function getInformationContainer() {
        if (isset($this->_informationContainer) == false) {
            $this->_informationContainer = new InformationContainer($this);
        }
        return $this->_informationContainer;
    }

    public function getWidgetContainer() {
        if (isset($this->_widgetContainer) == false) {
            $this->_widgetContainer = new WidgetContainer($this);
        }
        return $this->_widgetContainer;
    }

    public function getRewardInstance() {
        if (isset($this->_rewardInstance) == false) {
            $this->_rewardInstance = Reward::getInstance($this->getId(), 'itemId');
        }
        return $this->_rewardInstance;
    }

    public function getRewardContainer() {
        if (isset($this->_rewardContainer) == false) {
            $this->_rewardContainer = new RewardContainer($this);
        }
        return $this->_rewardContainer;
    }

    public function getActivityContainer() {
        if (isset($this->_activityContainer) == false) {
            $this->_activityContainer = new ActivityContainer($this);
        }
        return $this->_activityContainer;
    }

    public function getHostContainer() {
        if (isset($this->_hostContainer) == false) {
            $this->_hostContainer = new HostContainer($this);
        }
        return $this->_hostContainer;
    }

    public function getPageContainer() {
        if (isset($this->_pageContainer) == false) {
            $this->_pageContainer = new PageContainer($this);
        }
        return $this->_pageContainer;
    }

    /* SiteItemPoint Container */
    public function getSiteItemPointContainer() {
        if (isset($this->_siteItemPointContainer) == false) {
            $this->_siteItemPointContainer = new SiteItemPointContainer($this);
        }
        return $this->_siteItemPointContainer;
    }

//    public function getPointInstance($tokenId) {
//        if (isset($this->_pointInstance) == false) {
//            $pointInstance = Point::isItem($this->getId());
//            if($pointInstance == false || $pointInstance == null){
//                // 없다 새로 만든다.
//                $ret = Point::create($this->getId(), $tokenId);
//                if($ret != false){
//                    // 성공
//                    // idx가 같기때문에 getId로 한다.
//                    $pointInstance = Point::getInstance($this->getId());
//                } else {
//                    return false;
//                }
//            }
//            $pointInstance->setTokenId($tokenId);
//            $this->_pointInstance = $pointInstance;
//        } else {
//            $pointInstance = $this->_pointInstance;
//            if($pointInstance->getTokenId() != $tokenId){
//                $pointInstance->setTokenId($tokenId);
//                $this->_pointInstance = $pointInstance;
//            }
//        }
//
//        return $this->_pointInstance;
//    }

    public function getPointAddressContainer()
    {
        if (isset($this->_pointAddressContainer) == false) {
            $this->_pointAddressContainer = new SiteItemPointAddressContainer($this);
        }
        return $this->_pointAddressContainer;
    }

    public function getPointAddressInstance($tokenTypeId, $tokenId, $platformId){
        $pointAddressContainer = new SiteItemPointAddressContainer();
        $addressInstance = $pointAddressContainer->firstOrCreate($this->getId(), $tokenTypeId, $tokenId, $platformId);
        return $addressInstance;
    }

    public function existPointAddressInstance($tokenTypeId, $tokenId, $platformId){
        $pointAddressContainer = new SiteItemPointAdrdessContainer();
        $addressInstance = $pointAddressContainer->findFirst($this->getId(), $tokenTypeId, $tokenId, $platformId);
        return $addressInstance;
    }

    public function getTransactionContainer() {
        if (isset($this->_transactionContainer) == false) {
            $this->_transactionContainer = new TransactionContainer($this);
        }
        return $this->_transactionContainer;
    }



    public function getId()
    {
        return $this->_info['id'];
    }

    public function getSiteId()
    {
        return $this->_info['siteId'];
    }

    public function setSiteId($var)
    {
        $this->_info['siteId'] = $var;
        $this->_changes['siteId'] = $this->_info['siteId'];
    }

    public function getCategoryId()
    {
        return $this->_info['categoryId'];
    }

    public function setCategoryId($var)
    {
        $this->_info['categoryId'] = $var;
        $this->_changes['categoryId'] = $this->_info['categoryId'];
    }

    public function getOwnerId()
    {
        return $this->_info['ownerId'];
    }

    public function setOwnerId($var)
    {
        $this->_info['ownerId'] = $var;
        $this->_changes['ownerId'] = $this->_info['ownerId'];
    }

    public function getManagerName()
    {
        return $this->_info['managerName'];
    }

    public function setManagerName($var)
    {
        $this->_info['managerName'] = $var;
        $this->_changes['managerName'] = $this->_info['managerName'];
    }

    public function getManagerEmail()
    {
        return $this->_info['managerEmail'];
    }

    public function setManagerEmail($var)
    {
        $this->_info['managerEmail'] = $var;
        $this->_changes['managerEmail'] = $this->_info['managerEmail'];
    }

    public function getManagerPhone()
    {
        return $this->_info['managerPhone'];
    }

    public function setManagerPhone($var)
    {
        $this->_info['managerPhone'] = $var;
        $this->_changes['managerPhone'] = $this->_info['managerPhone'];
    }

    public function getImgId()
    {
        return $this->_info['imgId'];
    }

    public function setImgId($var)
    {
        $this->_info['imgId'] = $var;
        $this->_changes['imgId'] = $this->_info['imgId'];
    }

    public function getTypeId()
    {
        return $this->_info['typeId'];
    }

    public function setTypeId($var)
    {
        $this->_info['typeId'] = $var;
        $this->_changes['typeId'] = $this->_info['typeId'];
    }

    public function getName()
    {
        return $this->_info['name'];
    }

    public function setName($var)
    {
        $this->_info['name'] = $var;
        $this->_changes['name'] = $this->_info['name'];
    }

    public function getDomain()
    {
        return $this->_info['domain'];
    }

    public function setDomain($var)
    {
        $this->_info['domain'] = $var;
        $this->_changes['domain'] = $this->_info['domain'];
    }

    public function getFullUrl()
    {
        return $this->_info['fullUrl'];
    }

    public function setFullUrl($var)
    {
        $this->_info['fullUrl'] = $var;
        $this->_changes['fullUrl'] = $this->_info['fullUrl'];
    }

    public function getReporter()
    {
        return $this->_info['reporter'];
    }

    public function setReporter($var)
    {
        $this->_info['reporter'] = $var;
        $this->_changes['reporter'] = $this->_info['reporter'];
    }

    public function getApi()
    {
        return $this->_info['api'];
    }

    public function setApi($var)
    {
        $this->_info['api'] = $var;
        $this->_changes['api'] = $this->_info['api'];
    }

    public function getBookmark()
    {
        return $this->_info['bookmark'];
    }

    public function setBookmark($var)
    {
        $this->_info['bookmark'] = $var;
        $this->_changes['bookmark'] = $this->_info['bookmark'];
    }

    public function getRewardPool()
    {
        return $this->_info['rewardPool'];
    }

    public function getIntRewardPool()
    {
        $intRewardPool = (int)$this->_info['rewardPool'];
        return $intRewardPool;
    }

    public function getDisplayRewardPool() {
        if($this->getRewardPool() == '0.0000') {
            return 0;
        }
        $rp = $this->getRewardPool();
        $srp = (string)$rp;
        $erp = explode('.', $srp);

        if($erp[1] == '0000') {
            $rp = (int)$rp;
        }
        return $rp;
    }

    public function setRewardPool($var)
    {
        $this->_info['rewardPool'] = $var;
        $this->_changes['rewardPool'] = $this->_info['rewardPool'];
    }

    public function getLimitPerDay()
    {
        return $this->_info['limitPerDay'];
    }

    public function getIntLimitPerDay()
    {
        $intLimitPerDay = (int)$this->_info['limitPerDay'];
        return $intLimitPerDay;
    }

    public function setLimitPerDay($var)
    {
        $this->_info['limitPerDay'] = $var;
        $this->_changes['limitPerDay'] = $this->_info['limitPerDay'];
    }

    public function getChargeAlarm()
    {
        return $this->_info['chargeAlarm'];
    }

    public function setChargeAlarm($var)
    {
        $this->_info['chargeAlarm'] = $var;
        $this->_changes['chargeAlarm'] = $this->_info['chargeAlarm'];
    }

    public function getChargeUnit()
    {
        return $this->_info['chargeUnit'];
    }

    public function setChargeUnit($var)
    {
        $this->_info['chargeUnit'] = $var;
        $this->_changes['chargeUnit'] = $this->_info['chargeUnit'];
    }

    public function getLimitAlarm()
    {
        return $this->_info['limitAlarm'];
    }

    public function setLimitAlarm($var)
    {
        $this->_info['limitAlarm'] = $var;
        $this->_changes['limitAlarm'] = $this->_info['limitAlarm'];
    }

    public function getLimitAlarmUnit()
    {
        return $this->_info['limitAlarmUnit'];
    }

    public function setLimitAlarmUnit($var)
    {
        $this->_info['limitAlarmUnit'] = $var;
        $this->_changes['limitAlarmUnit'] = $this->_info['limitAlarmUnit'];
    }

    public function getLimitAlarmType()
    {
        return $this->_info['limitAlarmType'];
    }

    public function setLimitAlarmType($var)
    {
        $this->_info['limitAlarmType'] = $var;
        $this->_changes['limitAlarmType'] = $this->_info['limitAlarmType'];
    }

    public function getVerification()
    {
        return $this->_info['verification'];
    }

    public function setVerification($var)
    {
        $this->_info['verification'] = $var;
        $this->_changes['verification'] = $this->_info['verification'];
    }

    public function getVerificationType()
    {
        return $this->_info['verificationType'];
    }

    public function setVerificationType($var)
    {
        $this->_info['verificationType'] = $var;
        $this->_changes['verificationType'] = $this->_info['verificationType'];
    }

    public function getVerificationResult()
    {
        return $this->_info['verificationResult'];
    }

    public function setVerificationResult($var)
    {
        $this->_info['verificationResult'] = $var;
        $this->_changes['verificationResult'] = $this->_info['verificationResult'];
    }

    public function getModIp()
    {
        return $this->_info['modIp'];
    }

    public function setModIp($var)
    {
        $this->_info['modIp'] = $var;
        $this->_changes['modIp'] = $this->_info['modIp'];
    }

    public function getModDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['modDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setModDate($var)
    {
        $this->_info['modDate'] = $var;
        $this->_changes['modDate'] = $this->_info['modDate'];
    }

    public function getRegId()
    {
        return $this->_info['regId'];
    }

    public function setRegId($var)
    {
        $this->_info['regId'] = $var;
        $this->_changes['regId'] = $this->_info['regId'];
    }

    public function getRegIp()
    {
        return $this->_info['regIp'];
    }

    public function setRegIp($var)
    {
        $this->_info['regIp'] = $var;
        $this->_changes['regIp'] = $this->_info['regIp'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var)
    {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getStatus()
    {
        return $this->_info['status'];
    }

    public function setStatus($var)
    {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }


    public function getStatusName($mode = '') {

        $statusNameArray = array();
        $statusNameArray['en'][self::Status_Active]   = 'Active';
        $statusNameArray['ko'][self::Status_Active]   = '활성';
        $statusNameArray['en'][self::Status_Inactive] = 'Inactive';
        $statusNameArray['ko'][self::Status_Inactive] = '비활성';

        // 현재는 영어, 한국어만 지원
        if (USER_LANGUAGE_CODE){
            $languageCode = USER_LANGUAGE_CODE;
        } else {
            $languageCode = SITE_LANGUAGE_CODE;
        }

        switch ($languageCode) {
            case 'ko':
                break;
            case 'en':
            default:
                $languageCode = 'en';
                break;
        }
        $statusName = $statusNameArray[$languageCode][$this->getStatus()];

        if($mode == 'class'){
            return '<span class="status_' . strtolower($statusNameArray['en'][$this->getStatus()]) . '">' . $statusName . '</span>';
        } else {
            return $statusName;
        }
    }


    public function addRewardPool($addRewardPool) {
        $orgRewardPool = $this->getRewardPool();
        $total = $orgRewardPool + $addRewardPool;
        $this->setRewardPool($total);
    }


    /**
     * NKRW 지급내역
    */
    public function paymentDetails() {
        if($this->getId() == '') return null;

        $result = array();
        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT date(a.date) `date`, COUNT(a.id) `count`, SUM(b.reward) `sum`
            FROM SiteItemRewardActivity a
            LEFT JOIN SiteItemReward b ON a.rewardId = b.id
            WHERE a.isReward = ?
            AND a.itemId = ?
            GROUP BY `date`
EOD;
        $var = array(0, $this->getId());
        $data = $db->query($query, $var)->fetchAll();
        if(!empty($data)) {
            for ($i = 0; $i < sizeof($data); $i++) {
                $result[$i]['date']     = date('Y-m-d', strtotime($data[$i]['date']));
                $result[$i]['count']    = $data[$i]['count'];
                $result[$i]['sum']      = $data[$i]['sum'];
            }
        }
        return $result;
    }

    /**
     * 방문 및 보상현황
    */
    public function currentState() {
        if($this->getId() == '') return null;

        $result = array();
        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT date(a.date) `date`, 
            COUNT(a.id) `visit`, 
            COUNT(CASE WHEN a.isReward = 0 THEN 1 END) `reward`
            FROM SiteItemRewardActivity a
            LEFT JOIN SiteItemReward b ON a.rewardId = b.id
            WHERE a.itemId = ?
            GROUP BY `date`
EOD;
        $var = array($this->getId());
        $data = $db->query($query, $var)->fetchAll();

        if(!empty($data)) {
            for ($i = 0; $i < sizeof($data); $i++) {
                $result[$i]['date']     = date('Y-m-d', strtotime($data[$i]['date']));
                $result[$i]['visit']    = $data[$i]['visit'];
                $result[$i]['count']    = $data[$i]['reward'];
            }
        }
        return $result;
    }






}
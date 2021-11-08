<?php
namespace PL\Models\Client;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Referral\Referral;
use PL\Models\Site\Site;
use PL\Models\Util\Util;
use PL\Models\File\File;
use PL\Models\Timezone\Timezone;

use PL\Models\Client\Point\Point;
use PL\Models\Client\Point\Container as PointContainer;
use PL\Models\Client\Point\Swap\Container as SwapContainer;
use PL\Models\Client\Information\Information;
use PL\Models\Client\Payment\Container as PaymentContainer;
use PL\Models\Client\LoginLog\Container as LoginLogContainer;
use PL\Models\Client\SubscribeLog\Container as ClientSubscribeContainer;
use PL\Models\Client\Ref\Container as RefContainer;
use PL\Models\Client\Certification\Certification;
use PL\Models\Client\Activity\Container as ActivityContainer;

use PL\Models\Client\Tweet\Tweet;

use PL\Models\Board\Item\Container as BoardItemContainer;
use PL\Models\Comment\Item\Container as CommentItemContainer;

use PL\Models\Site\Item\Container as SiteItemContainer;

use PL\Models\Client\Point\Address\Container as PointAddressContainer;
use PL\Models\Client\Point\Transaction\Container as TransactionContainer;

use PL\Models\Client\Statistic\Gender\Container as StatisticGenderContainer;
use PL\Models\Client\Statistic\Age\Container as StatisticAgeContainer;


class Client extends AbstractSingleton {

    /**
     * 상수 설정
     * Active 메일인증을 거친 일반 유저
     * Inactive 탈퇴 처리된 계정.
     *
     * pwchange 암호 변경기간인 유저.
     * mail 메일인증을 진행중인 유저
     * secuMail 보안상(평소와 다른국가 등의) 메일인증을 진행하는 유저
     * block 악성댓글, 해킹등의 문제로 블럭

     */

    const tableName = 'Client';

    // @fixme:
    const Level_Bronze    = 1;
    const Level_Silver    = 2;
    const Level_Gold      = 3;
    const Level_Platinum  = 4;
    const Level_Diamond   = 5;

    const Type_User         = 0;        // 일반회원
    const Type_Press        = 1;        // 언론사
    const Type_Admin        = 2;        // 수퍼 어드민

    const Type_Expert       = 2;        // 전문가
//    const Type_Press     = 3;        // 기자

    // 이중인증
    const TwoFactor_Inactive = 0;
    const TwoFactor_Email    = 1;
    const TwoFactor_Otp      = 2;
    const TwoFactor_SMS      = 3;

    const Status_Active     = 0;    // Active 활동
    const Status_Inactive   = 1;    // Inactive (비활동)
    const Status_Defer      = 6;    // Defer 보류(회원가입 30일동안 못함)

    const Status_Pwchange = 2;
    const Status_Mail     = 3;
    const Status_Secumail = 4;
    const Status_Block    = 5;

    const C_Status_Success  = "Y";
    const C_Status_Fail     = "N";


    /* 소셜 로그인 타입 */
    const Login_Email       = 0;
    const Login_Google      = 1;
    const Login_Facebook    = 2;
    const Login_Twitter     = 3;
    const Login_KaKao       = 4;
    const Login_Naver       = 5;


    //@fixme: 회원 프로필 사용할경우 디폴트를 추가해야한다.
    const Default_Profile = '/assets/images/common/forum/img/imgDefaultProfile40.svg';

    protected $_refContainer;
    protected $_activityContainer;
    protected $_loginLogContainer;
    protected $_transactionContainer;

    protected $_ipContainer;
    protected $_xmlContainer;
    protected $_postContainer;
    protected $_alarmContainer;
    protected $_pointContainer;
    protected $_articleContainer;
    protected $_apiContainer;
    protected $_siteItemContainer;

    protected $_resumeContainer;
    protected $_boardItemContainer;
    protected $_commentItemContainer;
    protected $_factItemContainer;
    protected $_factItemAnswerContainer;
    protected $_eventItemContainer;
    protected $_eventAttendContainer;
    protected $_eventAttendGroupContainer;
    protected $_answerVotingItemContainer;

    protected $_profileInstance;
    protected $_paymentInstance;
    protected $_subscribeLogInstance = '';

    protected $_pointInstance;
    protected $_informationInstance;
    protected $_certificationInstance;
    protected $_referralInstance;

    protected $_answerEvaluationInstance;
    protected $_answerEvaluationContainer;
    protected  $_pointAddressContainer;

    protected $_companyInstance;

    protected $_pressMemberInstance;

    protected  $_swapContainer;


    protected   $_statisticAgeInstance;
    protected   $_statisticGenderInstance;
    protected   $_statisticAgeContainer;
    protected   $_statisticGenderContainer;

    /**
     * Tweet기능 사용하는 특정 Branch용
     * CG
     */
    protected $_tweetInstance;


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

    /**
     * ※ 변경 금지
     */
    public function getCryptMode($type){
        $info = array();
        $info[1]['get'] = 'N';
        $info[1]['set'] = 'N';

        $info[2]['get'] = 'N';
        $info[2]['set'] = 'N';

        $info[3]['get'] = 'Y';
        $info[3]['set'] = 'Y';

        $info[4]['get'] = 'Y';
        $info[4]['set'] = 'Y';

        if(is_numeric(BRANCH_ID) == false || BRANCH_ID < 1){
            return 'N';
        }
        if(isset($info[BRANCH_ID]) == false) return 'N';
        if(isset($info[BRANCH_ID][$type]) == false) return 'N';

        return $info[BRANCH_ID][$type];
    }

    public function getPointAddressContainer()
    {
        if (isset($this->_pointAddressContainer) == false) {
            $this->_pointAddressContainer = new PointAddressContainer($this);
        }
        return $this->_pointAddressContainer;
    }

    public function getPointAddressInstance($tokenTypeId, $tokenId, $platformId){
        $pointAddressContainer = new PointAddressContainer();
        $addressInstance = $pointAddressContainer->firstOrCreate($this->getId(), $tokenTypeId, $tokenId, $platformId);
        return $addressInstance;
    }

    public function existPointAddressInstance($tokenTypeId, $tokenId, $platformId){
        $pointAddressContainer = new PointAddressContainer();
        $addressInstance = $pointAddressContainer->findFirst($this->getId(), $tokenTypeId, $tokenId, $platformId);
        return $addressInstance;
    }

    public function getArticleContainer(){
        if (isset($this->_articleContainer) == false) {
            $this->_articleContainer = new ArticleContainer(News::getInstance(1));
        }
        return $this->_articleContainer;
    }

    public function getPointContainer(){
        if (isset($this->_pointContainer) == false) {
            $this->_pointContainer = new PointContainer($this);
        }
        return $this->_pointContainer;
    }

    public function getPaymentInstance(){
        if (isset($this->_paymentInstance) == false) {
            $this->_paymentInstance = new PaymentContainer($this);
        }
        return $this->_paymentInstance;
    }

    public function getLoginLogContainer() {
        if (isset($this->_loginLogContainer) == false) {
            $this->_loginLogContainer = new LoginLogContainer($this);
        }
        return $this->_loginLogContainer;
    }


    public function getRefContainer() {
        if (isset($this->_refContainer) == false) {
            $this->_refContainer = new RefContainer($this);
        }
        return $this->_refContainer;
    }


    public function getActivityContainer() {
        if (isset($this->_activityContainer) == false) {
            $this->_activityContainer = new ActivityContainer($this);
        }
        return $this->_activityContainer;
    }


    public function getTransactionContainer() {
        if (isset($this->_transactionContainer) == false) {
            $this->_transactionContainer = new TransactionContainer($this);
        }
        return $this->_transactionContainer;
    }



    public function getEventItemContainer() {
        if (isset($this->_eventItemContainer) == false) {
            $this->_eventItemContainer = new EventItemContainer();
            $this->_eventItemContainer->setClientId($this->getId());

        }
        return $this->_eventItemContainer;
    }

    public function getEventAttendContainer() {
        if (isset($this->_eventAttendContainer) == false) {
            $this->_eventAttendContainer = new EventAttendContainer($this);
        }
        return $this->_eventAttendContainer;
    }

    public function getCertificationInstance() {
        if (isset($this->_certificationInstance) == false) {
            $this->_certificationInstance = Certification::getInstance($this->getCertificationId());
        }
        return $this->_certificationInstance;
    }

    public function getReferralInstance() {
        if (isset($this->_referralInstance) == false) {
            $this->_referralInstance = Referral::getInstance($this->getId(), 'clientId');
        }
        return $this->_referralInstance;
    }

    public function getStatisticGenderInstance() {
        if (isset($this->_statisticGenderInstance) == false) {
            $this->_statisticGenderInstance = new StatisticGenderContainer($this);
        }
        return $this->_statisticGenderInstance;
    }

    public function getStatisticAgeInstance() {
        if (isset($this->_statisticAgeInstance) == false) {
            $this->_statisticAgeInstance = new StatisticAgeContainer($this);
        }
        return $this->_statisticAgeInstance;
    }

    public function getStatisticGenderContainer() {
        if (isset($this->_statisticGenderContainer) == false) {
            $this->_statisticGenderContainer = new StatisticGenderContainer($this);
        }
        return $this->_statisticGenderContainer;
    }

    public function getStatisticAgeContainer() {
        if (isset($this->_statisticAgeContainer) == false) {
            $this->_statisticAgeContainer = new StatisticAgeContainer($this);
        }
        return $this->_statisticAgeContainer;
    }



    public function getProfile(){
        if($this->getProfileId() < 1){
            $url = '';

            if(APPLICATION_ENV == 'dev'){
                if(defined('SERVICE_URL') == true){
                    $url .= PROTOCOL . '://' . SERVICE_URL . '';
                } elseif(defined('FULL_URL') == true){
                    $url .= PROTOCOL . '://' . FULL_URL . '';
                } else {
                    $url .= '';
                }
            } else {
                if(defined('STATIC_URL') == true){
                    $url .= STATIC_URL . '';
                }else if(defined('SERVICE_URL') == true){
                    $url .= PROTOCOL . '://' . SERVICE_URL . '';
                } else {
                    $url .= '';
                }
            }
            if(APPLICATION_SITE == 'cg') {
                return $url . '/assets/coinghost/images/web/common/img/profileDefault.svg';
            } else {
                return $url . self::Default_Profile;
            }
        } else {
            return $this->getProfileInstance()->getFullLinkUrl();
        }
    }

    public function getProfileInstance() {
        if (isset($this->_profileInstance) == false) {
            $this->_profileInstance = File::getInstance($this->getProfileId());
        }
        return $this->_profileInstance;
    }

    public function getSubscribeLogInstance() {
        if($this->_subscribeLogInstance == '') {
            $ret = ClientSubscribeContainer::isItemByClientId($this->getId(), 'obj');
            if($ret == false){
                $this->_subscribeLogInstance = 'N';
            } else {
                $this->_subscribeLogInstance = $ret;
            }
        }
        return $this->_subscribeLogInstance;
    }


    // 변경후

    public function getPointInstance($tokenId) {
        if (isset($this->_pointInstance) == false) {
            $pointInstance = Point::isItem($this->getId());
            if($pointInstance == false || $pointInstance == null){
                // 없다 새로 만든다.
                $ret = Point::create($this->getId(), $tokenId);
                if($ret != false){
                    // 성공
                    // idx가 같기때문에 getId로 한다.
                    $pointInstance = Point::getInstance($this->getId());
                } else {
                    return false;
                }
            }
            $pointInstance->setTokenId($tokenId);
            $this->_pointInstance = $pointInstance;
        } else {
            $pointInstance = $this->_pointInstance;
            if($pointInstance->getTokenId() != $tokenId){
                $pointInstance->setTokenId($tokenId);
                $this->_pointInstance = $pointInstance;
            }
        }

        return $this->_pointInstance;
    }

    public function getSwapContainer()
    {
        if (isset($this->_swapContainer) == false) {
            $this->_swapContainer = new SwapContainer($this);
        }
        return $this->_swapContainer;
    }

    public function getInformationInstance() {
        if (isset($this->_informationInstance) == false) {
            $informationInstance = Information::isItem($this->getId());
            if($informationInstance == false){
                // 없다 새로 만든다.
                $ret = Information::create($this->getId());
                if($ret != false){
                    // 성공
                    // idx가 같기때문에 getId로 한다.
                    $informationInstance = Information::getInstance($this->getId());
                    $informationInstance->initInformation(array());
                } else {
                    return false;
                }
            } else {
                // 존재할 경우

            }
            $this->_informationInstance = $informationInstance;
        }

        return $this->_informationInstance;
    }




    /**
     * @return bool|mixed|tweet
     * CG등 특정 Branch에서만 사용
     */
    public function getTweetInstance() {
        if (isset($this->_tweetInstance) == false) {
            $tweetInstance = Tweet::isItem($this->getId());
            if($tweetInstance instanceof Tweet != true){
                // 없다 새로 만든다.
                $ret = Tweet::create($this->getId());
                if($ret != false){
                    // 성공
                    // idx가 같기때문에 getId로 한다.
                    $tweetInstance = Tweet::isItem($this->getId());
                } else {
                    return false;
                }
            }
            $this->_tweetInstance = $tweetInstance;
        }

        return $this->_tweetInstance;
    }

    public function getPressMemberInstance() {
        if(isset($this->_pressMemberInstance) == false) {
            $pressMemberInstance = PressMemberContainer::isItem($this->getId(), 'clientId');
            if($pressMemberInstance instanceof PressMember == true) {
                $this->_pressMemberInstance = $pressMemberInstance;
            }
        }

        return $this->_pressMemberInstance;
    }

    // SiteItem
    public function getSiteItemContainer(){
        if (isset($this->_siteItemContainer) == false) {
            $this->_siteItemContainer = new SiteItemContainer(Site::getInstance(1));
            $this->_siteItemContainer->setOwnerId($this->getId());
        }
        return $this->_siteItemContainer;
    }

    public function getBoardItemContainer() {
        if (isset($this->_boardItemContainer) == false) {
            $this->_boardItemContainer = new BoardItemContainer();
            $this->_boardItemContainer->setClientInstance($this);
        }
        return $this->_boardItemContainer;
    }


    public function getCommentItemContainer() {
        if (isset($this->_commentItemContainer) == false) {
            $this->_commentItemContainer = new CommentItemContainer();
            $this->_commentItemContainer->setClientInstance($this);
        }
        return $this->_commentItemContainer;
    }

    // 팩트체크 > 질문
    public function getFactItemContainer() {
        if (isset($this->_factItemContainer) == false) {
            $this->_factItemContainer = new FactItemContainer();
            $this->_factItemContainer->setClientInstance($this);
        }
        return $this->_factItemContainer;
    }

    // 팩트체크 > 참여 한 답변
    public function getFactItemAnswerContainer() {
        if (isset($this->_factItemAnswerContainer) == false) {
            $this->_factItemAnswerContainer = new FactItemAnswerContainer();
            $this->_factItemAnswerContainer->setClientInstance($this);
        }
        return $this->_factItemAnswerContainer;
    }

    public function getFactItemAnswerEvaluationContainer() {
        if (isset($this->_answerEvaluationContainer) == false) {
            $this->_answerEvaluationContainer = new FactItemAnswerEvaluationContainer();
            $this->_answerEvaluationContainer->setClientInstance($this);
        }
        return $this->_answerEvaluationContainer;
    }


    public function isMatchPassword($password) {
        $data = $this->db->query('SELECT PASSWORD(?)', array($password))->fetch();
        return ($data[0] == $this->_info['password']);
    }

    public function isBlocked(){
        switch($this->getStatus()){

            case self::Status_Block:
                return true;
                break;

            case self::Status_Inactive:
                return true;
                break;

            default:
                return false;
            //return '(알 수 없음)';


        }

    }

    public function addLoginLog($ip, $type=0){
        $this->setLastLogin(Util::getDbNow('Y-m-d H:i:s', 'kst'));
//        $this->setLastLoginTimestamp(Util::getLocalTime());
        return $this->getLoginLogContainer()->addNewNow($ip, $type);
    }


    public function getItemByKey($key) {
        switch(trim($key)){
            case 'name':
                return $this->getName();
                break;
            case 'profile':
            case 'image':
                if(trim($this->getProfileId()) >= 1 && $this->getProfileInstance()){
                    return $this->getProfileInstance()->getFullLinkUrl();
                } else {
                    return '';
                }
                break;
            case 'certificationStatus':
                return $this->getCertificationStatus();
                break;
            case 'certificationStatusClass':
                return $this->getCertificationStatus('class');
                break;
            default:
                return $this->_info[$key];
                break;
            case 'status':
                return $this->getStatus();
                break;
            case 'statusName':
                return $this->getStatusName();
                break;
            case 'statusNameClass':
                return $this->getStatusName('class');
                break;
        }
    }


    public function getId() {
        return $this->_info['id'];
    }

    public function getEmail() {
        return $this->_info['email'];
    }

    public function setEmail($email) {
        $this->_info['email']    = $email;
        $this->_changes['email'] = $this->_info['email'];
    }

    public function getPassword() {
        return $this->_info['password'];
    }

    public function setPassword($password) {
        $password = trim($password);

        $this->_info['password']    = new Db\RawValue('PASSWORD("' . $password . '")');
        $this->_changes['password'] = $this->_info['password'];
    }

    public function getToken() {
        return $this->_info['token'];
    }

    public function setToken($token) {
        $this->_info['token']    = $token;
        $this->_changes['token'] = $this->_info['token'];
    }

    public function newToken() {
        return $this->issueToken();
    }

    public function issueToken() {
        $this->setToken(substr(md5(microtime()), -10));
        return $this->getToken();
    }

    public function isToken($token){
        return ($this->getToken() === $token) ? true : false;
    }

    public function getName() {
        return $this->getInformationInstance()->getName();
    }

    public function getNickname() {
        return $this->_info['nickname'];
    }

    public function setNickname($nickname) {
        $this->_info['nickname']    = $nickname;
        $this->_changes['nickname'] = $this->_info['nickname'];
    }

    public function getIntroduction() {
        return $this->_info['introduction'];
    }

    public function setIntroduction($introduction) {
        $this->_info['introduction']    = $introduction;
        $this->_changes['introduction'] = $this->_info['introduction'];
    }

    public function getNamePassword() {
        return $this->_info['namePassword'];
    }

    public function setNamePassword($name) {
        $name = trim($name);
        $this->_info['namePassword']    = new Db\RawValue('PASSWORD("' . addslashes($name) . '")');
        $this->_changes['namePassword'] = $this->_info['namePassword'];
    }

    public function changeNamePassword($name) {
        $name = trim($name);
        $namePassword = new Db\RawValue('PASSWORD("' . addslashes($name) . '")');
        return $namePassword;
    }

    public function getEncPhone() {
        return $this->_info['encPhone'];
    }

    public function setEncPhone($phone) {
        $name = trim($phone);
        $this->_info['encPhone']    = new Db\RawValue('PASSWORD("' . addslashes($phone) . '")');
        $this->_changes['encPhone'] = $this->_info['encPhone'];
    }

    public function changeEncPhone($phone) {
        $name = trim($phone);
        $namePassword = new Db\RawValue('PASSWORD("' . addslashes($phone) . '")');
        return $namePassword;
    }

    public function getPhone() {
        return $this->_info['phone'];
    }

    public function setPhone($phone) {
        $this->_info['phone']    = trim($phone);
        $this->_changes['phone'] = $this->_info['phone'];
    }

    public function getGender() {
        return $this->_info['gender'];
    }

    public function setGender($gender) {
        $this->_info['gender']    = trim($gender);
        $this->_changes['gender'] = $this->_info['gender'];
    }

    public function getBirthDate($format = 'Y-m-d') {
        if($this->_info['birthDate'] == '' || $this->_info['birthDate'] == '0000-00-00'){
            return '0000-00-00';
        }
        return date($format, strtotime($this->_info['birthDate']));
    }

    public function setBirthDate($birthDate) {
        $this->_info['birthDate'] = $birthDate;
        $this->_changes['birthDate'] = $this->_info['birthDate'];
    }

    public function getTypeId() {
        return $this->_info['typeId'];
    }

    public function setTypeId($typeId) {
        $this->_info['typeId']    = trim($typeId);
        $this->_changes['typeId'] = $this->_info['typeId'];
    }

//    public function getCertificationStatus($mode = ''){
//
//        $statusNameArray = array();
//        $statusNameArray['en']['Y'] = 'Complete';
//        $statusNameArray['ko']['Y'] = '완료';
//        $statusNameArray['en']['N'] = 'Incomplete';
//        $statusNameArray['ko']['N'] = '미완료';
//
//        // 현재는 영어, 한국어만 지원
//        if (USER_LANGUAGE_CODE){
//            $languageCode = USER_LANGUAGE_CODE;
//        } else {
//            $languageCode = SITE_LANGUAGE_CODE;
//        }
//
//        switch ($languageCode) {
//            case 'ko':
//                break;
//            case 'en':
//            default:
//                $languageCode = 'en';
//                break;
//        }
//        $statusName = $statusNameArray[$languageCode][$this->isCertified()];
//
//        if($mode == 'class'){
//            return '<span class="certification_' . strtolower($statusNameArray['en'][$this->isCertified()]) . '">' . $statusName . '</span>';
//        } else {
//            return $statusName;
//        }
//    }

    public function getCertificationStatus() {
        return $this->_info['certificationStatus'];
    }

    public function setCertificationStatus($certificationStatus) {
        $this->_info['certificationStatus']    = trim($certificationStatus);
        $this->_changes['certificationStatus'] = $this->_info['certificationStatus'];
    }

    public function isCertified() {
        if($this->getCertificationId() >= 1) return 'Y';
        return 'N';
    }

    public function getCertificationId() {
        return $this->_info['certificationId'];
    }

    public function setCertificationId($certificationId) {
        $this->_info['certificationId']    = $certificationId;
        $this->_changes['certificationId'] = $this->_info['certificationId'];
    }

    public function getCountryId() {
        return $this->_info['countryId'];
    }

    public function setCountryId($countryId) {
        $this->_info['countryId']    = $countryId;
        $this->_changes['countryId'] = $this->_info['countryId'];
    }

    public function getCompanyId() {
        return $this->_info['companyId'];
    }

    public function setCompanyId($companyId) {
        $this->_info['companyId']    = $companyId;
        $this->_changes['companyId'] = $this->_info['companyId'];
    }

    public function getSiteHits() {
        return $this->_info['siteHits'];
    }

    public function setSiteHits($siteHits) {
        $this->_info['siteHits']    = $siteHits;
        $this->_changes['siteHits'] = $this->_info['siteHits'];
    }

    public function addSiteHit() {
        $this->setSiteHits($this->getSiteHits()+1);
    }

    public function getLoginHits() {
        return $this->_info['loginHits'];
    }

    public function setLoginHits($loginHits) {
        $this->_info['loginHits']    = $loginHits;
        $this->_changes['loginHits'] = $this->_info['loginHits'];
    }

    public function addLoginHits() {
        $this->setLoginHits($this->getLoginHits()+1);
    }

    public function getLoginType() {
        return $this->_info['loginType'];
    }

    public function setLoginType($type) {
        $this->_info['loginType']    = $type;
        $this->_changes['loginType'] = $this->_info['loginType'];
    }

    // 보안인증 필드 START
    // 이중 로그인 [N(default) / Y]
    public function getLogin2Factor() {
        return $this->_info['login2Factor'];
    }

    public function setLogin2Factor($var) {
        $this->_info['login2Factor']    = $var;
        $this->_changes['login2Factor'] = $this->_info['login2Factor'];
    }

    // 이중 로그인 타입 [이메일 / OTP / SMS(나중에 추가)]
    public function get2FactorTypeId() {
        return $this->_info['2FactorTypeId'];
    }

    public function set2FactorTypeId($var) {
        $this->_info['2FactorTypeId']    = $var;
        $this->_changes['2FactorTypeId'] = $this->_info['2FactorTypeId'];
    }

    //
    public function get2FactorId() {
        return $this->_info['2FactorId'];
    }

    public function set2FactorId($var) {
        $this->_info['2FactorId']    = $var;
        $this->_changes['2FactorId'] = $this->_info['2FactorId'];
    }

    public function issue2FactorSecret() {
        $this->set2FactorSecret(Util::generateRandomNumber(6));
        $this->set2FactorIssueDate(Util::getDbNow());
        return $this->get2FactorSecret();
    }

    public function is2FactorSecret($token){
        return ($this->get2FactorSecret() === $token) ? true : false;
    }

    public function get2FactorSecret() {
        return $this->_info['2FactorSecret'];
    }

    public function set2FactorSecret($var) {
        $this->_info['2FactorSecret']    = $var;
        $this->_changes['2FactorSecret'] = $this->_info['2FactorSecret'];
    }

    public function is2FactorIssueDateExpired() {
        // 만료 시간 설정
        $expiredSeconds = 60 * 10; // 10분

        if(Util::getDateDiff($this->get2FactorIssueDate(),'Y', 's') < $expiredSeconds){
            return false;
        }
        return true;
    }

    public function get2FactorIssueDate() {
        return $this->_info['2FactorIssueDate'];
    }

    public function set2FactorIssueDate($var) {
        $this->_info['2FactorIssueDate']    = $var;
        $this->_changes['2FactorIssueDate'] = $this->_info['2FactorIssueDate'];
    }
    // 보안인증 필드 END

    public function getLevel() {
        return $this->_info['level'];
    }

    public function setLevel($level) {
        $this->_info['level']    = $level;
        $this->_changes['level'] = $this->_info['level'];
    }

    public function getLevelName() {
        switch ($this->getLevel()) {
            case self::Level_Bronze:
                return 'Bronze';
            case self::Level_Silver:
                return 'Silver';
            case self::Level_Gold:
                return 'Gold';
            case self::Level_Platinum:
                return 'Platinum';
            case self::Level_Diamond:
                return 'Diamond';
                break;
            default:
                return '(???)';
        }
    }

    public function getArticle() {
        return $this->_info['article'];
    }

    public function setArticle($article) {
        $this->_info['article']    = $article;
        $this->_changes['article'] = $this->_info['article'];
    }

    public function addArticle($i = 1) {
        $this->getMasterDB()->query('UPDATE ' . self::tableName . ' set `article` = `article` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractArticle($i = 1) {
        $this->getMasterDB()->query('UPDATE ' . self::tableName . ' set `article` = `article` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getBoard() {
        return $this->_info['board'];
    }

    public function setBoard($board) {
        $this->_info['board']    = $board;
        $this->_changes['board'] = $this->_info['board'];
    }

    public function addBoard($i = 1) {
        $this->getMasterDB()->query('UPDATE ' . self::tableName . ' set `board` = `board` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractBoard($i = 1) {
        $this->getMasterDB()->query('UPDATE ' . self::tableName . ' set `board` = `board` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getComment() {
        return $this->_info['comment'];
    }

    public function setComment($comment) {
        $this->_info['comment']    = $comment;
        $this->_changes['comment'] = $this->_info['comment'];
    }

    public function addComment($i = 1) {
        $this->getMasterDB()->query('UPDATE ' . self::tableName . ' set `comment` = `comment` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractComment($i = 1) {
        $this->getMasterDB()->query('UPDATE ' . self::tableName . ' set `comment` = `comment` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getSubscribe() {
        return $this->_info['subscribe'];
    }

    public function setSubscribe($subscribe) {
        $this->_info['subscribe']    = $subscribe;
        $this->_changes['subscribe'] = $this->_info['subscribe'];
    }

    public function getDueDateDiff($abs = 'Y', $format = 'd') {
        return Util::getDateDiff($this->_info['dueDate'], $abs, $format);
    }

    public function getDueDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['dueDate']));
    }

    public function setDueDate($dueDate) {
        $this->_info['dueDate']    = $dueDate;
        $this->_changes['dueDate'] = $this->_info['dueDate'];
    }

    public function getDailyNews() {
        return $this->_info['dailyNews'];
    }

    public function setDailyNews($dailyNews) {
        $this->_info['dailyNews']    = $dailyNews;
        $this->_changes['dailyNews'] = $this->_info['dailyNews'];
    }

    public function getEmailAgree() {
        return $this->_info['emailAgree'];
    }

    public function setEmailAgree($emailAgree) {
        $this->_info['emailAgree']    = $emailAgree;
        $this->_changes['emailAgree'] = $this->_info['emailAgree'];
    }

    public function getSmsAgree() {
        return $this->_info['smsAgree'];
    }

    public function setSmsAgree($smsAgree) {
        $this->_info['smsAgree']    = $smsAgree;
        $this->_changes['smsAgree'] = $this->_info['smsAgree'];
    }

    public function getTimezoneId() {
        return $this->_info['timezoneId'];
    }

    public function setTimezoneId($timezoneId) {
        $this->_info['timezoneId']    = $timezoneId;
        $this->_changes['timezoneId'] = $this->_info['timezoneId'];
    }

    public function getTimezone() {
        return $this->_info['timezone'];
    }

    public function setTimezone($timezone) {
        $this->_info['timezone']    = $timezone;
        $this->_changes['timezone'] = $this->_info['timezone'];
    }

    public function getTimezoneTitle() {
        if($this->getTimezoneId() >= 1 && $this->getTimezoneId() != 1000){
            $timezoneInstance = Timezone::getInstance($this->getTimezoneId());
            $timezoneTitle = $timezoneInstance->getCountry() . '/' . $timezoneInstance->getName();
        } else {
            $timezoneTitle = 'Global/UTC';
        }
        return $timezoneTitle;

    }


    public function getProfileId() {
        return $this->_info['profileId'];
    }

    public function setProfileId($profileId) {
        $this->_info['profileId']    = $profileId;
        $this->_changes['profileId'] = $this->_info['profileId'];
    }

    public function getSoundId() {
        return $this->_info['soundId'];
    }

    public function setSoundId($soundId) {
        $this->_info['soundId']    = $soundId;
        $this->_changes['soundId'] = $this->_info['soundId'];
    }

    public function getLastPwChange($format = 'Y-m-d H:i:s') {
        if(date($format, strtotime($this->_info['lastPwChange'])) == '1970-01-01 00:00:00') return '';
        return date($format, strtotime($this->_info['lastPwChange']));
    }

    public function setLastPwChange($lastPwChange) {
        $this->_info['lastPwChange']    = $lastPwChange;
        $this->_changes['lastPwChange'] = $this->_info['lastPwChange'];
    }

    public function getLastNnChange($format = 'Y-m-d H:i:s') {
        if(date($format, strtotime($this->_info['lastNnChange'])) == '1970-01-01 00:00:00') return '';
        return date($format, strtotime($this->_info['lastNnChange']));
    }

    public function setLastNnChange($lastNnChange) {
        $this->_info['lastNnChange']    = $lastNnChange;
        $this->_changes['lastNnChange'] = $this->_info['lastNnChange'];
    }

    public function getLastLogin($format = 'Y-m-d H:i:s') {
        if(trim($this->_info['lastLogin']) == '') return '';
        if($this->_info['lastLogin'] == '0000-00-00 00:00:00') return '';
        if($this->_info['lastLogin'] == '1970-01-01 00:00:00') return '';
        if(date($format, strtotime($this->_info['lastLogin'])) == '1970-01-01 00:00:00') return '';
        return date($format, strtotime($this->_info['lastLogin']));
    }

    public function setLastLogin($lastLogin) {
        $this->_info['lastLogin']    = $lastLogin;
        $this->_changes['lastLogin'] = $this->_info['lastLogin'];
    }

    public function getLastLoginInstance() {

        $lastLoginInstance = $this->getLoginLogContainer()->getOne();

        return $lastLoginInstance;
    }

    public function getLeaveDate($format = 'Y-m-d H:i:s') {
        if(date($format, strtotime($this->_info['leaveDate'])) == '1970-01-01 00:00:00') return '';
        return date($format, strtotime($this->_info['leaveDate']));
    }

    public function setLeaveDate($leaveDate) {
        $this->_info['leaveDate']    = $leaveDate;
        $this->_changes['leaveDate'] = $this->_info['leaveDate'];
    }

    public function getModIp() {
        return $this->_info['modIp'];
    }

    public function setModIp($modIp) {
        $this->_info['modIp']    = $modIp;
        $this->_changes['modIp'] = $this->_info['modIp'];
    }

    public function getModDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['modDate']));
    }

    public function setModDate($modDate) {
        $this->_info['modDate']    = $modDate;
        $this->_changes['modDate'] = $this->_info['modDate'];
    }

//    public function getLastLoginTimestamp($format = 'Y-m-d H:i:s') {
//        if(trim($this->_info['lastLoginTimestamp']) == '') return '';
//        if($this->_info['lastLoginTimestamp'] == '0000-00-00 00:00:00') return '';
//        if($this->_info['lastLoginTimestamp'] == '1970-01-01 00:00:00') return '';
//        if(date($format, strtotime($this->_info['lastLoginTimestamp'])) == '1970-01-01 00:00:00') return '';
//        return date($format, strtotime($this->_info['lastLoginTimestamp']));
//    }
//
//    public function setLastLoginTimestamp($lastLogin) {
//        $this->_info['lastLoginTimestamp']    = $lastLogin;
//        $this->_changes['lastLoginTimestamp'] = $this->_info['lastLoginTimestamp'];
//    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function setRegIp($regIp) {
        $this->_info['regIp']    = $regIp;
        $this->_changes['regIp'] = $this->_info['regIp'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = $regDate;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getDate($format = 'd-M') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function getTime($format = 'H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function getRegTimestamp($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regTimestamp']));
    }/*

    public function setRegTimestamp($regTimestamp) {
        $this->_info['regTimestamp']    = $regTimestamp;
        $this->_changes['regTimestamp'] = $this->_info['regTimestamp'];
    }*/

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }

    /**
     * 계정의 상태 값을 텍스트로 반환합니다
     *
     * @return string
     */
    public function getStatusName($mode = '') {

        $statusNameArray = array();
        $statusNameArray['en'][self::Status_Active]   = 'Active';
        $statusNameArray['ko'][self::Status_Active]   = '활성';
        $statusNameArray['en'][self::Status_Inactive] = 'Inactive';
        $statusNameArray['ko'][self::Status_Inactive] = '비활성';
        $statusNameArray['en'][self::Status_Pwchange] = 'PWChange';
        $statusNameArray['ko'][self::Status_Pwchange] = '비밀번호 변경';
        $statusNameArray['en'][self::Status_Mail]     = 'MailAuth';
        $statusNameArray['ko'][self::Status_Mail]     = '메일 미인증';
        $statusNameArray['en'][self::Status_Secumail] = 'SecuMail';
        $statusNameArray['ko'][self::Status_Secumail] = '시큐메일';
        $statusNameArray['en'][self::Status_Block]    = 'Block';
        $statusNameArray['ko'][self::Status_Block]    = '차단';

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

    /**
     * 회원 적립내역
    */
//    public function getAccumulationDetails() {
//        if($this->getId() == '') return null;
//
//        $result = array();
//        $db     = DI::getDefault()->getShared('db');
//        $query  = <<<EOD
//            SELECT date(A.date) `day`, COUNT(B.id) `count`, SUM(C.reward) `total`
//            FROM ClientActivity A
//            LEFT JOIN SiteItemRewardActivity B ON A.clientId = B.clientId AND A.date = B.date
//            LEFT JOIN SiteItemReward C ON C.id = B.rewardId
//            WHERE A.clientId = ?
//            AND B.isReward = ?
//            GROUP BY A.date
//EOD;
//        $var = array($this->getId(), 0);
//        $data = $db->query($query, $var)->fetchAll();
//        if(!empty($data)) {
//            for ($i = 0; $i < sizeof($data); $i++) {
//                $result[$i]['date']     = date('m-d', strtotime($data[$i]['day']));
//                $result[$i]['total']    = $data[$i]['total'];
//            }
//        }
//
//        return $result;
//    }

    public function getAccumulationDetails() {
        if($this->getId() == '') return null;

        $result = array();
        $db     = DI::getDefault()->getShared('db');
        $query  = <<<EOD
            SELECT A.clientId as `client`, date(A.date) as `day`, COUNT(*) as `count`, SUM(B.reward) `total`
            FROM SiteItemRewardActivity A, SiteItemReward B
            WHERE A.rewardId = B.id 
            AND A.clientId = ?
            AND A.isReward = ?
            GROUP BY date(A.date)
EOD;
        $var = array($this->getId(), 0);
        $data = $db->query($query, $var)->fetchAll();
        if(!empty($data)) {
            for ($i = 0; $i < sizeof($data); $i++) {
                $result[$i]['date']     = date('m-d', strtotime($data[$i]['day']));
                $result[$i]['total']    = $data[$i]['total'];
            }
        }

        return $result;
    }

    public static function getLoginTypeName($loginType) {
        $return = '';

        switch($loginType) {
            case self::Login_Email:
                $return = '이메일';
                break;
            case self::Login_Google:
                $return = '구글';
                break;
            case self::Login_Facebook:
                $return = '페이스북';
                break;
            case self::Login_Twitter:
                $return = '트위터';
                break;
            case self::Login_KaKao:
                $return = '카카오';
                break;
            case self::Login_Naver:
                $return = '네이버';
                break;
        }
        return $return;
    }
}
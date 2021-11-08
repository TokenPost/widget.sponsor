<?php
namespace PL\Widget\Controllers;

use Exception;
use Phalcon\DI;
use Phalcon\Mvc\Controller;

use Abraham\TwitterOAuth\TwitterOAuth;

use PL\Models\Util\Util;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;
use PL\Models\Client\LoginLog\LoginLog;
use PL\Models\Client\Activity\Activity as ClientActivity;

use PL\Models\Client\Point\Point;
use PL\Models\Client\Point\Log\Log as PointLog;
use PL\Models\Digital\Asset\Asset as DigitalAsset;

use PL\Models\Site\Item\Item as SiteItem;
use PL\Models\Site\Item\Container as SiteItemContainer;

use PL\Models\Site\Item\Point\Log\Log as SiteItemPointLog;

use PL\Models\Site\Item\Page\Page as SiteItemPage;

use PL\Models\Site\Item\Reward\Reward as SiteItemReward;
use PL\Models\Site\Item\Reward\Container as SiteItemRewardContainer;

use PL\Models\Site\Item\Reward\Activity\Activity as ItemRewardActivity;

use PL\Models\Site\Reward\Reward as SiteReward;

use PL\Models\Site\Item\Widget\Widget;
use PL\Models\Site\Item\Widget\Container as WidgetContainer;

use PL\Models\Referral\Referral;
use PL\Models\Referral\Container as ReferralContainer;

use PL\Models\Referral\Item\Item as ReferralItem;
use PL\Models\Referral\Item\Log\Log as ReferralItemLog;
use PL\Models\Referral\Item\Log\Ip\Ip as ReferralItemLogIp;

use PL\Models\Donation\Donation;
use PL\Models\Donation\Container as DonationContainer;
use PL\Models\Donation\Item\Pay\Pay as DonationItemPay;
use PL\Models\Donation\Item\Item as DonationItem;


class IndexController extends Controller {

    public function initialize() {
        $widgetCode = trim($this->request->get('widgetCode'));
        $widgetInstance = WidgetContainer::isItem($widgetCode, 'code');
        $widgetType = trim($this->request->get('widgetType'));

        if($widgetInstance instanceof Widget != false) {
            $this->view->widget         = $widgetInstance;
            $this->view->widgetCode     = $widgetCode;
            $this->view->widgetType     = $widgetType;
        }

    }

    public function indexAction() {

        $client = $this->view->client;
        $clientId = $this->view->clientId;
        if($client >= 1 && $client->getId() == $clientId) {
            $widgetItemInstance = $this->view->widget->getItemInstance();
            if($widgetItemInstance instanceof SiteItem != true) throw new Exception('존재하지 않은 위젯 정보입니다. code : 1');
            
            $clientInstance = ClientContainer::isItem($clientId);
            if($clientInstance instanceof Client != true) throw new Exception('존재하지 않은 회원 정보입니다. code : 2');
            $jbexplode = explode( '@', $clientInstance->getEmail());
            $name = $jbexplode[0];

            // P.POINT
            $ppointInstance = $clientInstance->getPointInstance(DigitalAsset::Asset_PPOINT);
            if($ppointInstance instanceof Point != true) throw new Exception('회원 정보를 가져오는데 실패했습니다. code : 3');
            $ppoint = Util::decimalFormat($ppointInstance->getPoint());
            $pDifference = Util::decimalFormat($ppointInstance->getTodayActivityPoint());

            // NEWS
            $newsInstance = $clientInstance->getPointInstance(DigitalAsset::Asset_NEWS);
            if($newsInstance instanceof Point != true) throw new Exception('회원 정보를 가져오는데 실패했습니다. code : 4');
            $news = Util::decimalFormat($newsInstance->getPoint(), 4);
            $nDifference = Util::decimalFormat($newsInstance->getTodayActivityPoint(), 4);

            $this->view->setVars(array(
                'clientSession'     => 'Y',
                'clientInstance'    => $clientInstance,
                'clientName'        => $name,
                'ppoint'            => $ppoint,
                'news'              => $news,
                'pDifference'       => $pDifference,
                'nDifference'       => $nDifference,
            ));
        }

        $widgetType = $this->view->widgetType;
        if($widgetType == 1) {
            // default
            $templateName = 'v3/horizontal2';
        } elseif ($widgetType == 2) {
            $templateName = 'v3/horizontal';
        } else {
            // default
            $templateName = 'v3/horizontal2';
        }

        $this->view->pick($templateName);
        $this->view->setVars(array(
            'widgetCode'    => $this->view->widgetCode,
            'widget'        => $this->view->widget
        ));
    }

    private function _checkDomain($purl) {
        $pieces = parse_url($purl);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';

        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return false;
    }

    /**
     * ajax Action 분기처리.
     * @param $mode
     */
    public function ajaxAction($mode) {
        if ($this->request->isAjax() === false) {
            $this->response->setStatusCode(406, 'Not Acceptable')->sendHeaders();
        } else {
            switch ($mode){
                case 'widgetCheck':
                    $this->_ajaxWidgetCheckAction();
                    break;
                case 'login':
                    $this->_ajaxLoginAction();
                    break;
                case 'memberInfo':
                    $this->_ajaxMemberInfoAction();
                    break;
                case 'addDonate':
                    $this->_ajaxAddDonateAction();
                    break;
                case 'rewardReceive':
                    $this->_ajaxRewardReceiveAction();
                    break;
                case 'logout':
                    $this->_ajaxLogoutAction();
                    break;

                default:
                    $this->_ajaxErrorAction();
                    break;
            }
        }
    }

    /**
     * WidgetCheck Action
     * 위젯 존재 여부 체크
     * 도메인으로 확인(Siteitem)
     */
    private function _ajaxWidgetCheckAction() {
        $response               = array();
        try {
            $purl = trim($this->request->getPost('purl'));
            $code = trim($this->request->getPost('code'));
            if($purl == '') throw new Exception('존재하지 않은 위젯 정보입니다. code : 1');
            if($code == '') throw new Exception('존재하지 않은 위젯 정보입니다. code : 2');

            $widgetInstance = WidgetContainer::isItem($code, 'code');
            if($widgetInstance instanceof Widget != true) throw new Exception('존재하지 않은 위젯 정보입니다. code : 3');

            $searchNewsSourceRet = $this->_SearchNewsSourceAction($purl, $widgetInstance->getItemId());
            if(empty($searchNewsSourceRet) == true) {
                $response['searchNewsSourceRet'] = 'fail';
            }

            $activityDate = Util::convertTimezone(Util::getLocalTime(), 'Asia/Seoul', 'Y-m-d');
            $urlData = parse_url($purl);
            $path = trim($urlData['path']);
            $query = trim($urlData['query']);
            $referralCode = $query;

            if($referralCode != '') {
                $referralCode = explode('wg_ref=', $referralCode)[1];
                if(strpos($referralCode, '&') != false) {
                    $code = substr($referralCode, 0, 6);
                } else {
                    $code = $referralCode;
                }
                $filterContainer = new FilterContainer();
                $rewardContainer = new SiteItemRewardContainer();
                $filterContainer->add(new Filter('`rewardId`', '=', '"' . SiteReward::Type_Referral .'"'));
                $filterContainer->add(new Filter('`status`', '=', '"' . SiteItemReward::Status_Active .'"'));
                $rewardContainer->setFilterContainer($filterContainer);
                $referralItem = $rewardContainer->getOne();

                $siteItemPointInstance = $widgetInstance->getItemInstance()->getPointInstance(DigitalAsset::Asset_PPOINT);
                if($referralItem instanceof SiteItemReward == true && $siteItemPointInstance->getPoint() >= $referralItem->getReward()) {
                    $referralInstance = ReferralContainer::isItem(Referral::Referral_Article);
                    $referralTargetInstance = $referralInstance->getItemContainer()->_isTargetCode($code, $path);

                    if($referralTargetInstance instanceof ReferralItem == true) {
                        $sharedClientInstance = ClientContainer::isItem($referralTargetInstance->getClientId());
                        $sharedClientLastLoginInfo = $sharedClientInstance->getLoginLogContainer()->getLastLoginInfo();
                        if($sharedClientLastLoginInfo instanceof LoginLog != true) throw new Exception('존재하지 않는 공유 코드입니다. code : 4');

                        if($sharedClientLastLoginInfo->getIp() != Util::getClientIp()) {
                            $referralTargetLogInstance = $referralTargetInstance->recordLog(0, Util::getClientIp(), 0, 0, $activityDate);
                            if($referralTargetLogInstance instanceof ReferralItemLog == true){
                                $logIpInstance = $referralTargetInstance->getLogIpContainer()->findFirst($activityDate, Util::getClientIp());
                                if($logIpInstance instanceof ReferralItemLogIp != true && IS_BOT === false){
                                    if($referralTargetInstance->getClientInstance()->getCertificationStatus() == Client::C_Status_Success) {
                                        $activityContainer = $referralItem->getActivityContainer();
                                        $referralRewardCount = $activityContainer->getReceiveRewardCount($referralTargetInstance->getClientInstance()->getId(), $referralItem->getItemId(), $activityDate);

                                        if($referralRewardCount < $referralItem->getLimit()) {
                                            $siteActivityRet = $referralItem->getActivityContainer()->addActivity($referralItem->getItemId(), $referralItem->getId(), $referralTargetInstance->getClientId(), $this->view->getVar('localDate'), $purl, $path, CLIENT_IP,  ItemRewardActivity::Type_Referral);
                                            if($siteActivityRet instanceof ItemRewardActivity != true) throw new Exception('리워드가 존재하지 않습니다. code : 5');

                                            $addPointResult = $siteItemPointInstance->addPoint('-' . $referralItem->getReward(), '리워드 지급', 'point receive : ' . $referralTargetInstance->getClientId(), getIP(), SiteItemPointLog::RequesterType_Self, $referralTargetInstance->getClientId(), 0);

                                            // ClientActivity 추가
                                            // 날짜, 사이트 Id, 리워드 Id, siteItemActivity Id
                                            $sharedClientInstance = ClientContainer::isItem($referralTargetInstance->getClientId());
                                            if($sharedClientInstance instanceof Client != true) throw new Exception('존재하지 않는 공유 코드입니다. code : 6');
                                            $sharedClientActivityRet = $sharedClientInstance->getActivityContainer()->addActivity($this->view->getVar('localDate'), $referralItem->getItemId(), $referralItem->getId(), $siteActivityRet->getId(), CLIENT_IP);
                                            if($sharedClientActivityRet == false) throw new Exception('존재하지 않는 공유 코드입니다. code : 7');
                                            if($sharedClientActivityRet instanceof ClientActivity != true) throw new Exception('존재하지 않는 공유 코드입니다. code : 8');

                                            // ClientPoint
                                            $assetId = $referralItem->getAssetId();   // 리워드 타입(p.point / news)
                                            $pointInstance = $sharedClientInstance->getPointInstance($assetId);
                                            $addPointResult = $pointInstance->addPoint('+' . $referralItem->getReward(), '리워드', 'Site item reward id:' . $referralItem->getId(), getIP(), PointLog::RequesterType_Self, $sharedClientInstance->getId(), 0);
                                            if($addPointResult['error'] == 1){
                                                // 생성 실패
                                                throw new Exception($addPointResult['message']);
                                            }

                                            // add ip log
                                            $ipLogRet = $referralTargetInstance->getLogIpContainer()->recordLog($activityDate, CLIENT_IP, $referralTargetLogInstance->getId());
                                            if($ipLogRet instanceof ReferralItemLogIp != true) throw new Exception('referral error code : 6');

                                        } else {
                                            // 사이트가 설정한 리미티드 채운 경우
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $response['error']      = 0;
            $response['status']     = 200;
            $response['display']   = 'Y';

        } catch (Exception $e) {
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }

        die(json_encode($response));
    }

    /**
     * Logout Action
     * 로그아웃 처리
     */
    private function _ajaxLogoutAction() {
        try {
            $purl = trim($this->request->getPost('purl'));
            $code = trim($this->request->getPost('widgetCode'));
            $clientId = trim($this->request->getPost('clientId'));

            if($purl == '') throw new Exception('존재하지 않은 위젯 정보입니다. code : 1');
            if($code == '') throw new Exception('존재하지 않은 위젯 정보입니다. code : 2');

            $widgetInstance = WidgetContainer::isItem($code, 'code');
            if($widgetInstance instanceof Widget != true) throw new Exception('존재하지 않은 위젯 정보입니다. code : 3');

            if($clientId != $this->view->clientId) throw new Exception('존재하지 않는 회원 정보입니다. code : 4');

            $this->session->remove('clientId');
            $this->session->destroy();
            $this->view->disable();

            $response               = array();
            $response['error']      = 0;
            $response['status']     = 200;

        } catch (Exception $e) {
            $response            = array();
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }

        die(json_encode($response));
    }


    /**
     * Login Action
     * 로그인 처리
     */
    private function _ajaxLoginAction() {
        try {
            $widget = $this->view->widget;
            $code = trim($this->request->getPost('widgetCode'));

            $widgetInstance = WidgetContainer::isItem($code, 'code');
            if($code == '') throw new Exception('존재하지 않은 위젯 정보입니다. code : 1');
            if($widget->getCode() != $code) throw new Exception('존재하지 않은 위젯 정보입니다. code : 2');
            if($widgetInstance instanceof Widget != true) throw new Exception('존재하지 않은 위젯 정보입니다. code : 3');

            $purl = trim($this->request->getPost('parentUrl'));
            if($purl == '') throw new Exception('존재하지 않은 위젯 정보입니다. code : 4');

            $rdUrl = trim($this->request->getPost('rdUrl'));
            if(substr($rdUrl, 0, 4) == 'http') {

            } else {
                if(substr($rdUrl, 0, 1) != '/') $rdUrl = '/';
                if(strpos($rdUrl, '/member') !== false) {
                    $rdUrl = '/';
                }
            }

            $id = trim($this->request->getPost('id'));
            $password = trim($this->request->getPost('pw'));

            // email format check
            $ret = Util::validEmailFormat($id);
            if($ret == false) throw new Exception('이메일을 다시 입력해 주세요. code:1');

            $client = ClientContainer::isItem($id, 'email');
            if ($client instanceof Client != true) throw new Exception('로그인할 수 없는 계정입니다. code:1');
            if ($client->isMatchPassword($password) === false) throw new Exception('로그인할 수 없는 계정입니다. code:1');

            if($client && ($client->getStatus() == Client::Status_Inactive || $client->getStatus() == Client::Status_Block)) {
                // 로그인 불가능한 계정
                throw new Exception('로그인할 수 없는 계정입니다.');
            }

            if($client->getLevel() != 1 && is_null($client->getDueDate()) != true && strtotime($client->getDueDate()) < strtotime(Util::getDbNow())){
                $client->setLevel(1);
                $client->saveChanges();
            }

            $this->session->set('clientId', $client->getId());
            $this->session->set('clientToken', $client->newToken());
            $_SESSION["loginId"] = $client->getId();

            $client->addLoginHits();
            $ret = $client->addLoginLog(Util::getClientIp());

            $response               = array();
            $response['error']      = 0;
            $response['status']     = 200;
            $response['rdUrl']      = $rdUrl;
            $response['clientId']   = $client->getId();

        } catch (Exception $e) {
            $response            = array();
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }

        die(json_encode($response));
    }

    /**
     * MemberInfo Action
     */
    private function _ajaxMemberInfoAction() {
        try {
            $result = array();

            $purl = trim($this->request->getPost('purl'));
            $widgetCode = trim($this->request->getPost('widgetCode'));

            if($purl == '') throw new Exception('존재하지 않은 위젯 정보입니다. code : 1');
            if($widgetCode == '') throw new Exception('존재하지 않은 위젯 정보입니다. code : 2');

            $widget = $this->view->widget;
            if($widget instanceof Widget != true) throw new Exception('존재하지 않은 위젯 정보입니다. code : 3');
            if($widget->getCode() != $widgetCode) throw new Exception('존재하지 않은 위젯 정보입니다. code : 4');
            if($this->view->widgetCode != $widgetCode) throw new Exception('존재하지 않은 위젯 정보입니다. code : 5');

            $urlData = parse_url($purl);
            $path = trim($urlData['path']);
            $activityDate = Util::convertTimezone(Util::getLocalTime(), 'Asia/Seoul', 'Y-m-d');

            // get site Item
            $itemInstance = $widget->getItemInstance();
            if($itemInstance instanceof SiteItem != true) throw new Exception('존재하지 않은 위젯 정보입니다. code : 6');
            $siteItemPointInstance = $itemInstance->getPointInstance(DigitalAsset::Asset_PPOINT);

            $clientId = $this->session->get('clientId');
            $clientInstance = ClientContainer::isItem($clientId);
            if($clientInstance instanceof Client != true) throw new Exception('존재하지 않은 회원 정보입니다. code : 7');

            $filterContainer = new FilterContainer();
            $rewardContainer = new SiteItemRewardContainer();
            $filterContainer->add(new Filter('`itemId`', '=', '"' . $itemInstance->getId() .'"'));
            $filterContainer->add(new Filter('`rewardId`', '=', '"' . SiteReward::Type_ArticleView .'"'));
            $filterContainer->add(new Filter('`status`', '=', '"' . SiteItemReward::Status_Active .'"'));
            $rewardContainer->setFilterContainer($filterContainer);
            $readRewardItem = $rewardContainer->getOne();

            $toastDisplay = 'N';
            $activityId = 0;
            $rewardId = 0;
            if($readRewardItem instanceof SiteItemReward == true && (float)$siteItemPointInstance->getPoint() >= (float)$readRewardItem->getReward()) {
                $rewardId = $readRewardItem->getId();
                if($readRewardItem instanceof SiteItemReward != true) throw new Exception('리워드가 존재하지 않습니다. code : 8');

                if($clientInstance->getCertificationStatus() == Client::C_Status_Success) {
                    $activityContainer = $readRewardItem->getActivityContainer();

                    $receiveRewardCount = $activityContainer->getReceiveRewardCount($clientInstance->getId(), $readRewardItem->getItemId(), $activityDate);
                    if($receiveRewardCount < (int)$readRewardItem->getLimit()) {
                        $activityContainer = $readRewardItem->getActivityContainer();
                        $checkActivity = $activityContainer->_checkActivity($clientId, $this->view->getVar('localDate'), $path);

                        if($checkActivity == null) {
                            if($clientId == 0 && $clientInstance->getId() == 0) throw new Exception('존재하지 않은 회원 정보입니다. code : 8');
                            $activityRet = $activityContainer->addActivity($readRewardItem->getItemId(), $readRewardItem->getId(), $clientId, $this->view->getVar('localDate'), $purl, $path, CLIENT_IP);

                            if($activityRet instanceof ItemRewardActivity != true) throw new Exception('리워드가 존재하지 않습니다. code : 9');
                            if($activityRet != false) $toastDisplay = 'Y';
                            $activityId = $activityRet->getId();
                        } else {
                            if($checkActivity instanceof ItemRewardActivity != true) throw new Exception('리워드가 존재하지 않습니다. code : 10');
                            if($checkActivity->getIsReward() == ItemRewardActivity::Reward_Refuse) $toastDisplay = 'Y';
                            $activityId = $checkActivity->getId();
                        }
                    } else {
                        $toastDisplay = 'N';
                        $activityId = 0;
                    }
                }
            } else {
                $toastDisplay = 'N';
                $activityId = 0;
            }

            $clientReferralCode = '';
            $filterContainer->clear();
            $rewardContainer = new SiteItemRewardContainer();
            $filterContainer->add(new Filter('`itemId`', '=', '"' . $itemInstance->getId() .'"'));
            $filterContainer->add(new Filter('`rewardId`', '=', '"' . SiteReward::Type_Referral .'"'));
            $rewardContainer->setFilterContainer($filterContainer);
            $referralItem = $rewardContainer->getOne();

            // create referral code
            if($referralItem instanceof SiteItemReward == true) {
                $referralInstance = ReferralContainer::isItem(Referral::Referral_Article);
                if($referralInstance instanceof Referral != true) throw new Exception('리워드가 존재하지 않습니다. code : 11');
                if ($this->view->clientId >= 1 && $this->view->client) {
                    $referralItemInstance = $referralInstance->getItemContainer()->firstOrCreate($path, $this->view->clientId, $referralItem->getId(), $referralItem->getReward());
                    if ($referralItemInstance instanceof ReferralItem == true) {
                        $clientReferralCode = $referralItemInstance->getCode();
                    }
                }
            }

            // get client information
            if($clientInstance->getInformationInstance()->getName() == '') {
                $jbexplode = explode( '@', $clientInstance->getEmail());
                $name = $jbexplode[0];
            } else {
                $name = $clientInstance->getInformationInstance()->getName();
            }
            $result['name'] = $name;
            $result['email']    = $clientInstance->getEmail();
            $result['profile'] = $clientInstance->getProfile();
            $result['profileId'] = $clientInstance->getProfileId();
            $result['profilename'] = $clientInstance->getInformationInstance()->getProfileName();

            $ppointInstance = $clientInstance->getPointInstance(DigitalAsset::Asset_PPOINT);
            if($ppointInstance instanceof Point != true) throw new Exception('회원 정보를 불러오는데 실패했습니다. code : 12');
            $result['pPoint'] = Util::decimalFormat($ppointInstance->getPoint());
            $pDifference = $ppointInstance->getTodayActivityPoint();
            $result['pDifference'] = Util::decimalFormat($pDifference);

            $newsInstance = $clientInstance->getPointInstance(DigitalAsset::Asset_NEWS);
            if($newsInstance instanceof Point != true) throw new Exception('회원 정보를 불러오는데 실패했습니다. code : 13');
            $result['news'] = Util::decimalFormat($newsInstance->getPoint(), 4);
            $nDifference = $newsInstance->getTodayActivityPoint();
            $result['nDifference'] = Util::decimalFormat($nDifference, 4);

            $result['toastDisplay'] = $toastDisplay;
            $result['activityId'] = $activityId;
            $result['rewardId'] = $rewardId;
            $result['referralCode'] = $clientReferralCode;

            $response           = array();
            $response['error']  = 0;
            $response['status'] = 200;
            $response['item']   = $result;

        } catch (Exception $e) {
            $response            = array();
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }
        die(json_encode($response));
    }


    /**
     * 후원하기
     */
    private function _ajaxAddDonateAction() {
        try {
            $client = $this->view->client;
            $clientId  = trim($this->request->getPost('clientId'));
            $widgetCode  = trim($this->request->getPost('widgetCode'));
            $quantity  = trim($this->request->getPost('donate'));
            $unit = trim($this->request->getPost('unit'));

            if(Util::isInteger($clientId) != true || $clientId != $this->view->clientId) throw new Exception('로그인 후 이용할 수 있습니다.');
            if($client instanceof Client != true) throw new Exception('로그인 후 이용할 수 있습니다.');
            if($client->getStatus() == Client::Status_Inactive || $client->getStatus() == Client::Status_Block) throw new Exception('사용할 수 없는 계정입니다.');

            if($widgetCode == "") throw new Exception('언론사가 존재하지 않습니다. code : 1');
            $widgetInstance = WidgetContainer::isItem($widgetCode, 'code');
            if($widgetInstance instanceof Widget != true) throw new Exception('언론사가 존재하지 않습니다. code : 2');

            $itemInstance = $widgetInstance->getItemInstance();
            if($itemInstance instanceof SiteItem != true) throw new Exception('언론사가 존재하지 않습니다. code : 3');
            if($itemInstance->getStatus() != SiteItem::Status_Active) throw new Exception('언론사가 존재하지 않습니다. code : 4');

            $siteItemPointInstance = $itemInstance->getPointInstance(DigitalAsset::Asset_PPOINT);
            if($siteItemPointInstance == false || $siteItemPointInstance == null) throw new Exception('언론사 포인트가 존재하지 않습니다.');

            if($quantity <= 0) throw new Exception('수량이 올바르지 않습니다.');

            // Donation, DonationItem check
            $donationInstance = DonationContainer::isItem('press', 'code');
            if($donationInstance instanceof Donation != true) throw new Exception('후원 기능이 존재하지 않습니다.');
            $donationItemInstance = $donationInstance->getItemContainer()->firstOrCreate($itemInstance->getId());
            if($donationItemInstance instanceof DonationItem != true) throw new Exception('후원 기능이 존재하지 않습니다.');

            $typeId = DonationItemPay::Type_Token;
            $periodId = DonationItemPay::Period_Onetime;

            $tokenId = 0;
            $remaining = 0;
            if($unit == 'news'){
                $tokenId = DigitalAsset::Asset_NEWS;
                $clientPointInstance = $client->getPointInstance($tokenId);
                if($clientPointInstance){
                    $remaining = $clientPointInstance->getPoint();
                }
                if($quantity > $remaining) throw new Exception('보유 수량이 부족합니다.');
                if($clientPointInstance->checkMaxDecimal($quantity, $tokenId) !== true) throw new Exception('허용범위보다 큰 소수자리의 포인트 입니다.');

            } elseif($unit == 'ppoint'){
                $tokenId = DigitalAsset::Asset_PPOINT;
                $clientPointInstance = $client->getPointInstance($tokenId);
                if($clientPointInstance){
                    $remaining = $clientPointInstance->getPoint();
                }
                if($quantity > $remaining) throw new Exception('보유 수량이 부족합니다.');
                if($clientPointInstance->checkMaxDecimal($quantity, $tokenId) !== true) throw new Exception('허용범위보다 큰 소수자리의 포인트 입니다.');
            } else {
                throw new Exception('타입이 올바르지 않습니다.');
            }

            $paid = $quantity;
            $fee = $paid * $donationInstance->getFeeRate() / 100;
            $decimals = 0;
            if($typeId == DonationItemPay::Type_Token){
                switch ($tokenId){
                    case DigitalAsset::Asset_PPOINT:
                        // P.Point
                        $decimalPoint = Point::Decimal_PPOINT;
                        $decimals = strlen(Point::Decimal_PPOINT) - 1;
                        break;
                    case DigitalAsset::Asset_NEWS;
                        // NEWS
                        $decimalPoint = Point::Decimal_NewsSatoshi;
                        $decimals = strlen(Point::Decimal_NewsSatoshi) - 1;
                        break;
                    default:
                        break;
                }
            } else if($typeId == DonationItemPay::Type_Fiat){
                $decimals = 2;
                $decimalPoint = 100;
            }
            // 수수료는 내림으로 조정
            $fee = floor($fee * $decimalPoint) / $decimalPoint;
            $fee = str_replace(',', '', number_format($fee, $decimals));
            $quantity = str_replace(',', '', number_format( ( $quantity - $fee ) , $decimals));
            $krwConverted = $paid;

            // add donation item pay
            $newItem = array();
            $newItem['donationId']        = $donationItemInstance->getDonationId();
            $newItem['itemId']            = $donationItemInstance->getId();
            $newItem['clientId']          = $client->getId();
            $newItem['typeId']            = $typeId;
            $newItem['periodId']          = $periodId;
            $newItem['paymentPayId']      = 0;
            $newItem['payCurrencyTypeId'] = $typeId;
            $newItem['payCurrencyId']     = $tokenId;
            $newItem['paid']              = $paid;
            $newItem['fee']               = $fee;
            $newItem['quantity']          = $quantity;
            $newItem['krwConverted']      = $krwConverted;
            $newItem['distribute']        = 0;
            $newItem['regDate']           = Util::getLocalTime();
            $newItem['status']            = DonationItemPay::Status_Active;

            // pay 추가
            $newItem = $donationItemInstance->getPayContainer()->addNew($newItem);
            if($newItem < 1){
                throw new Exception('후원 처리가 실패하였습니다.');
            }

            $itemPayInstance = $donationItemInstance->getPayContainer()->_isItem($newItem);
            if($itemPayInstance instanceof DonationItemPay != true){
                throw new Exception('후원 처리가 실패하였습니다.');
            }

            // 후원금 차감.
            $addPointResult = $clientPointInstance->addPoint('-' . $paid, '후원', 'Donation pay id:' . $itemPayInstance->getId(), getIP(), PointLog::RequesterType_Self, $client->getId(), 0);
            if($addPointResult['error'] == 1){
                // 생성 실패 donation pay 삭제
                $itemPayInstance->delete();
                throw new Exception($addPointResult['message']);
            }

            // add site point
            $addPointResult = $siteItemPointInstance->addPoint('+' . $paid, '후원', 'Donation pay id : ' . $clientId, getIP(), SiteItemPointLog::RequesterType_Self, $clientId, 0);

            $itemPayInstance->getItemInstance()->addPay();
            $itemPayInstance->getItemInstance()->setLastPayDate(Util::getLocalTime());

            $itemPayInstance->getDonationInstance()->addPay();
            $itemPayInstance->getDonationInstance()->setLastPayDate(Util::getDbNow());
            $itemPayInstance->getDonationInstance()->setLastPayTimestamp(Util::getLocalTime());

            if($itemPayInstance->getTypeId() == DonationItemPay::Type_Token){
                switch ($itemPayInstance->getPayCurrencyId()){
                    case DigitalAsset::Asset_PPOINT:
                        $itemPayInstance->getItemInstance()->addPointCount();
                        $itemPayInstance->getItemInstance()->addPointAmount($itemPayInstance->getPaid());
                        $itemPayInstance->getItemInstance()->addPointFee($itemPayInstance->getFee());

                        $itemPayInstance->getDonationInstance()->addPointCount();
                        $itemPayInstance->getDonationInstance()->addPointAmount($itemPayInstance->getPaid());
                        $itemPayInstance->getDonationInstance()->addPointFee($itemPayInstance->getFee());
                        break;
                    case DigitalAsset::Asset_NEWS:
                        $itemPayInstance->getItemInstance()->addNewsCount();
                        $itemPayInstance->getItemInstance()->addNewsAmount($itemPayInstance->getPaid());
                        $itemPayInstance->getItemInstance()->addNewsFee($itemPayInstance->getFee());

                        $itemPayInstance->getDonationInstance()->addNewsCount();
                        $itemPayInstance->getDonationInstance()->addNewsAmount($itemPayInstance->getPaid());
                        $itemPayInstance->getDonationInstance()->addNewsFee($itemPayInstance->getFee());
                        break;
                }
            }

            $clientActivityRet = $client->getActivityContainer()->addActivity($this->view->getVar('localDate'), $itemInstance->getId(), 0, $itemPayInstance->getId(), CLIENT_IP);
            if($clientActivityRet == false) throw new Exception('회원 정보를 불러오는데 실패했습니다. code : 1');
            if($clientActivityRet instanceof ClientActivity != true) throw new Exception('회원 정보를 불러오는데 실패했습니다. code : 2');

            $result = array();
            $result['siteName']     = $itemInstance->getName();
            $result['amount']       = $paid;
            $result['tokenType']    = $tokenId;


            $response               = array();
            $response['error']      = 0;
            $response['status']     = 200;
            $response['item']       = $result;

        } catch (Exception $e) {
            $response            = array();
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }
        die(json_encode($response));
    }


    /**
     * receive reward
     */
    private function _ajaxRewardReceiveAction() {
        try {
            $client = $this->view->client;
            $clientId = $this->request->getPost('clientId');
            $widgetCode = $this->request->getPost('widgetCode');
            $activityId = $this->request->getPost('activityId');
            $rewardId = $this->request->getPost('rewardId');

            if(Util::isInteger($clientId) != true || $clientId != $this->view->clientId) throw new Exception('로그인 후 이용할 수 있습니다.');
            if($client instanceof Client != true) throw new Exception('로그인 후 이용할 수 있습니다.');
            if($client->getStatus() == Client::Status_Inactive || $client->getStatus() == Client::Status_Block) throw new Exception('사용할 수 없는 계정입니다.');

            if($widgetCode == "") throw new Exception('언론사가 존재하지 않습니다. code : 1');
            $widgetInstance = WidgetContainer::isItem($widgetCode, 'code');
            if($widgetInstance instanceof Widget != true) throw new Exception('언론사가 존재하지 않습니다. code : 2');

            $itemInstance = $widgetInstance->getItemInstance();

            if($itemInstance instanceof SiteItem != true) throw new Exception('언론사가 존재하지 않습니다. code : 3');
            if($itemInstance->getStatus() != SiteItem::Status_Active) throw new Exception('언론사가 존재하지 않습니다. code : 4');

            $rewardInstance = $itemInstance->getRewardContainer()->_isItem($rewardId);
            if($rewardInstance instanceof SiteItemReward != true) throw new Exception('리워드가 존재하지 않습니다. code : 1');
            if($rewardInstance->getId() != $rewardId) throw new Exception('리워드가 존재하지 않습니다. code : 2');

            $activityInstance = $rewardInstance->getActivityContainer()->_isItem($activityId);
            if($activityInstance instanceof ItemRewardActivity != true) throw new Exception('리워드가 존재하지 않습니다. code : 3');
            if($activityInstance->getId() != $activityId) throw new Exception('리워드가 존재하지 않습니다. code : 4');
            if($activityInstance->getItemId() != $itemInstance->getId()) throw new Exception('리워드가 존재하지 않습니다. code : 5');
            if($activityInstance->getClientId() != $clientId) throw new Exception('리워드가 존재하지 않습니다. code : 7');
            if($activityInstance->getIsReward() == ItemRewardActivity::Reward_Receive) throw new Exception('리워드가 존재하지 않습니다. code : 6');

            $siteItemPointInstance = $itemInstance->getPointInstance(DigitalAsset::Asset_PPOINT);
            if($siteItemPointInstance == false || $siteItemPointInstance == null) throw new Exception('언론사 포인트가 존재하지 않습니다.');
            if($siteItemPointInstance->getPoint() < $rewardInstance->getReward()) {
                throw new Exception('언론사 포인트가 존재하지 않습니다.');
            }
            $addPointResult = $siteItemPointInstance->addPoint('-' . $rewardInstance->getReward(), '리워드 지급', 'point receive : ' . $clientId, getIP(), SiteItemPointLog::RequesterType_Self, $clientId, 0);


            $assetId = $rewardInstance->getAssetId();   // 리워드 타입(p.point / news)
            $pointInstance = $client->getPointInstance($assetId);

            // add reward
            $addPointResult = $pointInstance->addPoint('+' . $rewardInstance->getReward(), '리워드', 'Site item reward id:' . $rewardInstance->getId(), getIP(), PointLog::RequesterType_Self, $client->getId(), 0, $this->view->getVar('localDate'));
            if($addPointResult['error'] == 1){
                throw new Exception($addPointResult['message']);
            }

            // site item reward activity
            $activityInstance->setIsReward(ItemRewardActivity::Reward_Receive);
            $activityInstance->saveChanges();

            $clientActivityRet = $client->getActivityContainer()->addActivity($this->view->getVar('localDate'), $itemInstance->getId(), $rewardInstance->getId(), $activityInstance->getId(), CLIENT_IP);
            if($clientActivityRet == false) throw new Exception('error code : 2');
            if($clientActivityRet instanceof ClientActivity != true) throw new Exception('error code : 1');

            $rewardInstance->addTodayReward($rewardInstance->getReward());
            $rewardInstance->addTotalReward($rewardInstance->getReward());
            $rewardInstance->addTodayActivity();
            $rewardInstance->addTotalActivity();
            $rewardInstance->addTodayClient();
            $rewardInstance->addTotalClient();
            $rewardInstance->setRegDate(Util::getLocalTime());

            $result = array();
            $result['assetId'] = $assetId;
            $result['point'] = Util::decimalFormat($client->getPointInstance($assetId)->getPoint());
            $result['difference'] = $client->getPointInstance($assetId)->getTodayActivityPoint();

            $response            = array();
            $response['error']      = 0;
            $response['status']     = 200;
            $response['item']       = $result;

        } catch (Exception $e) {
            $response            = array();
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }
        die(json_encode($response));
    }



    /**
     * Error action
     */
    private function _ajaxErrorAction(){
        $response            = array();
        $response['error']   = 1;
        $response['message'] = $this->view->pageContextContainer->getContext($this->view->siteLanguageCode , 1459);

        die(json_encode($response));
    }

    private function _SearchNewsSourceAction($url, $siteItemId = 0) {

        if($url == '')  return array();
        if($siteItemId == 0)  return array();

        $getDomain = $this->_checkDomain($url);

        $siteItemContainer = new SiteItemContainer();
        $siteItemInstance = $siteItemContainer->_isItem($siteItemId);

        if($siteItemInstance->getDomain() != $getDomain) return array();

        $urlData = parse_url($url);
        $path = trim($urlData['path']);

        $siteItemPageContainer = $siteItemInstance->getPageContainer();
        $siteItemPageInstance = $siteItemPageContainer->_isItem($path, 'path');

        if(empty($siteItemPageInstance) == true) {
            $ret = $siteItemPageContainer->parseUrl($url);
            if(empty($ret) != true) {
                $newPageItem = array();
                $newPageItem['itemId']          = $siteItemId;
                $newPageItem['host']            = $getDomain;
                $newPageItem['path']            = $path;
                $newPageItem['author']          = $ret['author'];
                $newPageItem['title']           = $ret['title'];
                $newPageItem['image']           = $ret['image'];
                $newPageItem['description']     = $ret['description'];
                $newPageItem['regDate']         = Util::getLocalTime();

                $ret = $siteItemPageContainer->addNew($newPageItem);
                if($ret == false){
                    // 실패
                    throw new Exception('페이지 정보 저장에 실패하였습니다.');
                }

                $retSiteItemPageInstance = SiteItemPage::getInstance($ret);
                if($retSiteItemPageInstance instanceof SiteItemPage != true) {
                    return false;
                } else {
                    return $retSiteItemPageInstance;
                }
            } else {
                $newPageItem = array();
                $newPageItem['itemId']          = $siteItemId;
                $newPageItem['host']            = $getDomain;
                $newPageItem['path']            = $path;
                $newPageItem['author']          = '';
                $newPageItem['title']           = '';
                $newPageItem['image']           = '';
                $newPageItem['description']     = '';
                $newPageItem['regDate']         = Util::getLocalTime();

                $ret = $siteItemPageContainer->addNew($newPageItem);
                if($ret == false){
                    throw new Exception('페이지 정보 저장에 실패하였습니다.');
                }
                $retSiteItemPageInstance = SiteItemPage::getInstance($ret);
                if($retSiteItemPageInstance instanceof SiteItemPage != true) {
                    return array();
                } else {
                    return $retSiteItemPageInstance;
                }
            }
        } else {
            return array();
        }
    }


    /**
     * SNS 연동 로그인
     * 트위터
    */
    public function twitterOauthAction() {
        $state = $_GET['state'];

        require_once '../vendor/twitteroauth/autoload.php';
        // https://api.twitter.com/2/users?id=12

        define('CONSUMER_KEY', "ab98ZpJYEFq61410ykWBGl5dd");
        define('CONSUMER_SECRET', "wmQiqm7e5khVzT6XKacHpJTrNrDb43JcPbnvU2eAqH7pBQy21l");
        $redirectUrl = $this->view->staticUrl.'/common/authTwitter';
        define('OAUTH_CALLBACK', $redirectUrl);
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));

        $url = $connection->url('oauth/authenticate', array('oauth_token' => $request_token['oauth_token']));

        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        $_SESSION['twitter_state'] = $state;

        header('Location: ' . $url);
    }

}
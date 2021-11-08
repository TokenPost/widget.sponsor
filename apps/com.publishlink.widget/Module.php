<?php
namespace PL\Widget;

use Exception;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ModuleDefinitionInterface;

use PL\Models\Site\Item\Widget\Widget;
use PL\Models\Util\Detect\Bot;

use PL\Models\Info\Menu\Menu;
use PL\Models\Info\Site;
use PL\Models\Info\Mapping\Mapping;

use PL\Models\Util\Date;
use PL\Models\Util\Util;
use PL\Models\Util\LoadTime;
use PL\Models\Mobile\Detector;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

use PL\Models\Client\Client;
use PL\Models\News\News;
use PL\Models\Admin\Admin;
use PL\Models\Timeline\Timeline;

use PL\Models\Mobile\MobileDetect;
use PL\Models\News\Article\Article;
use PL\Models\Board\Container as BoardContainer;
use PL\Models\News\Article\Container as ArticleContainer;
use PL\Models\Language\Container as LanguageContainer;

use PL\Models\Publish\Publish;
use PL\Models\Publish\Container as publishContainer;
use PL\Models\Page\Context\Container as PageContextContainer;

use PL\Models\Banner\Container as BannerContainer;
use PL\Models\Design\Layout\Banner\Container as DesignBannerContainer;

use PL\Models\Cache\Calendar\Container as CacheCalendarContainer;

use PL\Models\Chat\Auth\Token as ChatAuthToken;
use PL\Models\Topic\Topic;

use PL\Models\Statistic\View\View as StatisticView;
use PL\Models\Statistic\View\Container as ViewContainer;

use PL\Models\Site\Item\Widget\Container as WidgetContainer;

use PL\Models\Info\Bar\Container as InfoBarContainer;

use PL\Models\Info\Bar\Item\Item as InfoBarItem;
use PL\Models\Info\Bar\Item\Container as InfoBarItemContainer;

use PL\Models\Info\Sns\Container as InfoSnsContainer;

use PL\Models\Info\About\Container as InfoAboutContainer;
use PL\Models\News\Category\Container as NewsCategoryContainer;
use PL\Models\Topic\Item\Container as TopicItemContainer;
use PL\Models\Timeline\Container as TimelineContainer;

class Module implements ModuleDefinitionInterface {

    //public function registerAutoloaders($di) {
    public function registerAutoloaders(\Phalcon\Di\DiInterface $dependencyInjector = null) {
        $loader = new Loader();
        $loader->registerNamespaces(
            array(
                //'Zend'             => '../models/Zend/',
                'Phalcon'            => '../models/Phalcon/',
                'ElephantIO'         => '../models/ElephantIO',
                'PL\Models'          => '../models/PL/',
                'Adapters'           => '../models/Adapters/',
                'PL\Widget\Controllers' => '../apps/com.publishlink.widget/controllers/'
            )
        );
        $loader->register();
    }

    public function registerServices(\Phalcon\Di\DiInterface $di = null) {
        $di->set('dispatcher', function () use ($di) {
            $evManager = $di->getShared('eventsManager');
            $evManager->attach(
                "dispatch:beforeException",
                function ($event, $dispatcher, $exception) {
                    switch ($exception->getCode()) {
                        case DispatcherException::EXCEPTION_HANDLER_NOT_FOUND:
                        case DispatcherException::EXCEPTION_ACTION_NOT_FOUND:
                            $dispatcher->forward(
                                [
                                    'controller' => 'error',
                                    'action'     => 'show404',
                                ]
                            );
                            return false;
                    }
                }
            );

            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('PL\Widget\Controllers');
            $dispatcher->setEventsManager($evManager);

            return $dispatcher;
        }, true);

        $di->set('infoSite', function () use ($di) {
            if(HTTP_HOST == 'widget.publishdemo.com' && APPLICATION_ENV == 'stage') {
                return Site::getInstance(1);
            } elseif(HTTP_HOST == 'supportw.publishdemo.com' && APPLICATION_ENV == 'stage') {
                return Site::getInstance(2);
            } else {
                // 개발 및 운영 일 경우
                return Site::getInstance(1);
            }
        });

        $di->set('view', function () use ($di) {
            $view = new View();
            $view->setViewsDir('../apps/com.publishlink.widget/views/');
            $view->registerEngines([
                '.volt' => 'volt'
            ]);

            define('IS_BOT', Bot::isBot());

            /**
             * 운영인 경우에는 https 강제 포워딩
             * 결제모듈있는경우 필수.
             */
            /*if (APPLICATION_ENV == 'production') {
                redirectToHttps();
            }*/

            $db        = $di->getShared('db');
            $db_master = $di->getShared('db_master');
            $session   = $di->getShared('session');
            $request   = $di->getShared('request');
            $router    = $di->getShared('router');
            $uriData   = parse_url(getenv('REQUEST_URI'));
            $infoSite  = $di->getShared('infoSite');

            // calc timezone offset
            $timezoneQuery = 'SET TIME_ZONE = ?';
            $db->query($timezoneQuery, array(Util::getStandardOffsetUTC($infoSite->getTimezone())));
            $db_master->query($timezoneQuery, array(Util::getStandardOffsetUTC($infoSite->getTimezone())));
            date_default_timezone_set($infoSite->getTimezone());

            $view->setVars( array(
                'APPLICATION_ENV' => APPLICATION_ENV,
                'moduleName'      => strrchr($router->getModuleName(),'.'),
                'controllerName'  => ($router->getControllerName() != '' ? $router->getControllerName() : 'index'),
                'actionName'      => ($router->getActionName() != '' ? $router->getActionName() : 'index'),
                'url'             => $_SERVER["REQUEST_URI"],
                'uri'             => ($router->getControllerName() != '' ? $router->getControllerName() : 'index') . '/' . ($router->getActionName() != '' ? $router->getActionName() : 'index'),
                'requestUrl'      => $_SERVER["REQUEST_URI"],
                'hostName'        => HTTP_HOST,
                'path'            => $uriData['path'],
                'now'             => new Date(),
                'time'            => time(),
                'localDate'       => Util::getLocalTime('Y-m-d'),

                'infoSite'         => $infoSite,
                'branchId'         => $infoSite->getBranchId(),
                'branchCode'       => $infoSite->getBranchCode(),
                'siteDomain'       => $infoSite->getDomain(),
                'siteLanguageId'   => $infoSite->getLanguageId(),
                'siteLanguageCode' => $infoSite->getLanguageCode(),
                'siteCountryId'    => $infoSite->getCountryId(),
                'siteCountryCode'  => $infoSite->getCountryCode(),
                'siteTimezone'     => $infoSite->getTimezone(),
                'siteTimezoneId'   => $infoSite->getTimezoneId(),
                'siteTimezoneCode' => $infoSite->getTimezoneCode(),

                'infoToken'        => $infoSite->getTokenInstance(),
//                'infoDesign'       => $infoSite->getDesignInstance(),

                'rightSection'       => 'Y',
                'rewardPopupDisplay' => 'N',


                'bottomSection'    => 'N',
                'isIndex'         => false,
                'contributor'     => null,
                'topicList'       => 'N',
                'bottomBanner'    => 'N',
                'isPopular'       => 'N',
                'adBannerDisplay' => 'Y',
                'adPopup'         => '',
                'topBanner'       => '',
                'rand'            => rand(0,3),
                'relatedCategoryArticles' => '',
            ));


            $view->setVars(array(
                'subId'     => 0,
                'tabId'     => 0,
                'sectionId' => 1,
                'sectionUtl' => '/',
                'sectionTitle' => '홈',
                'menuType' => 0,
                'menuId' => 0,
            ));


            if ($request->isAjax() === false){
                $viewInstance = ViewContainer::firstOrCreate($view->getVar('localDate'), StatisticView::Type_Site);
                if($viewInstance instanceof StatisticView == true) $viewInstance->addCount();
            } else {
                $viewInstance = ViewContainer::firstOrCreate($view->getVar('localDate'), StatisticView::Type_Ajax);
                if($viewInstance instanceof StatisticView == true) $viewInstance->addCount();
            }


            define('SITE_BRANCH_ID',     $infoSite->getBranchId());
            define('SITE_BRANCH_CODE',   $infoSite->getBranchCode());
            define('SITE_TITLE',         $infoSite->getTitle());
            define('SITE_TITLE_NATIVE',  $infoSite->getTitleNative());
            define('SITE_LANGUAGE_ID',   $infoSite->getLanguageId());
            define('SITE_LANGUAGE_CODE', $infoSite->getLanguageCode());
            define('SITE_COUNTRY_ID',    $infoSite->getCountryId());
            define('SITE_COUNTRY_CODE',  $infoSite->getCountryCode());
            define('SITE_TIMEZONE',      $infoSite->getTimezone());
            define('SITE_TIMEZONE_ID',   $infoSite->getTimezoneId());
            define('SITE_TIMEZONE_CODE', $infoSite->getTimezoneCode());
            define('SITE_DOMAIN',        $infoSite->getDomain());


            // set Timezone and off set

//            if($infoSite->getTimezone() != 'UTC'){
//
//                $datetime = new \DateTime();
//                var_dump($datetime->getTimezone());
//                if($datetime->getOffset() != 0){
//                    $offsetMin = floor($datetime->getOffset() / 60);
//                    $offsetSign  = ($offsetMin < 0 ? -1 : 1);
//                    $offsetMin = abs($offsetMin);
//                    $offsetHour = floor($offsetMin / 60);
//                    $offsetMin -= $offsetHour * 60;
//
//                    $offset = sprintf('%+d:%02d', $offsetHour * $offsetSign, $offsetMin);
//                }
//
//            }



            // @fixme: config 설정으로 전환
            if (APPLICATION_ENV == 'production') {
                $view->setVars(array(
                    'cssRevision' => '20210310r1',
                    'jsRevision'  => '20210205r1',
                    'chatServer'  => 'https://' . $infoSite->getChatDomain(),
                    'debug'       => false,
                    'isDev'       => false,
                    'protocol'    => 'https'
                ));
            } elseif (APPLICATION_ENV == 'stage') {
                if($view->hostName == 'widget.publishdemo.com') {
                    // stage
                    $staticUrl = 'widget.publishdemo.com';
                } elseif($view->hostName == 'supportw.publishdemo.com') {
                    // support 제출용
                    $staticUrl = 'supportw.publishdemo.com';
                } else {
                    // default
                    $staticUrl = HTTP_HOST;
                }
                $view->setVars(array(
                    'cssRevision'   => '20210518r1',
                    'jsRevision'    => '20210205r1',
                    'chatServer'    => 'https://' . $infoSite->getChatDomain(),
                    'debug'         => false,
                    'isDev'         => false,
                    'protocol'      => 'https',
                    'staticUrl'     => $staticUrl
                ));
            } else {
                $view->setVars(array(
                    'cssRevision'     => microtime(),
                    'jsRevision'      => microtime(),
                    'chatServer'      => 'https://dev.chat.pms.kr:8014',
                    'debug'           => true,
                    'isDev'           => true,
                    'protocol'        => 'http',
                    'adBannerDisplay' => 'N'
                ));
            }


            switch (HTTP_HOST) {

                case '106.241.190.58':
                    $view->setVar('staticUrl',  '106.241.190.58');
                    $view->setVar('fullUrl',    '106.241.190.58');
                    $view->setVar('serviceUrl', '106.241.190.58');
                    $view->setVar('imageUrl',    $infoSite->getFileDomain());
                    $view->setVar('fileUrl',     $infoSite->getFileDomain());
                    break;

                // STAGE
                case 'supportw.publishdemo.com' :
                case 'widget.publishdemo.com' :
                    $view->setVar('staticUrl',  HTTP_HOST);
                    $view->setVar('fullUrl',    $infoSite->getServiceDomain());
                    $view->setVar('serviceUrl', $infoSite->getServiceDomain());
                    $view->setVar('imageUrl',   $infoSite->getFileDomain());
                    $view->setVar('fileUrl',    $infoSite->getFileDomain());
                    break;

                case 'dev.widget.publishlink.com':
                case 'd1.widget.publishlink.com':
                case 'd2.widget.publishlink.com':
                case 'd3.widget.publishlink.com':
                case 'd4.widget.publishlink.com':
                case 'd5.widget.publishlink.com':
                case 'd6.widget.publishlink.com':
                case 'd7.widget.publishlink.com':
                    $view->setVar('staticUrl',  HTTP_HOST);
                    $view->setVar('fullUrl',   'dev.widget.publishlink.com');
                    $view->setVar('serviceUrl','dev.publishlink.com');
                    $view->setVar('imageUrl',   $infoSite->getFileDomain());
                    $view->setVar('fileUrl',    $infoSite->getFileDomain());
                    break;

                case 'dev_widget.elmindev1.com':
                case 'd1_widget.elmindev1.com':
                case 'd2_widget.elmindev1.com':
                case 'd3_widget.elmindev1.com':
                case 'd4_widget.elmindev1.com':
                case 'd5_widget.elmindev1.com':
                case 'd6_widget.elmindev1.com':
                case 'd7_widget.elmindev1.com':
                    $view->setVar('staticUrl',  HTTP_HOST);
                    $view->setVar('fullUrl',   'd1_widget.elmindev1.com');
//                    $view->setVar('serviceUrl','dev.elmindev1.com');
//                    $view->setVar('serviceUrl','d4.elmindev1.com');
                    $view->setVar('serviceUrl', $infoSite->getServiceDomain());
                    $view->setVar('imageUrl',   $infoSite->getFileDomain());
                    $view->setVar('fileUrl',    $infoSite->getFileDomain());
                    $view->setVar('protocol',   'https');
                    break;

                default:
                    $view->setVar('staticUrl',  $infoSite->getStaticDomain());
                    $view->setVar('fullUrl',    $infoSite->getServiceDomain());
                    $view->setVar('serviceUrl', $infoSite->getServiceDomain());
                    $view->setVar('imageUrl',   $infoSite->getFileDomain());
                    $view->setVar('fileUrl',    $infoSite->getFileDomain());
                    break;
            }

            $view->setVar('imageUrl', $view->getVar('protocol') . '://' . $view->getVar('imageUrl'));
            $view->setVar('staticUrl', $view->getVar('protocol') . '://' . $view->getVar('staticUrl'));



            $view->setVar('forwardingPort', '');
            if (APPLICATION_ENV == 'dev' && $di->getShared('forwarding_port') != '') {
                $port = $di->getShared('forwarding_port');
                $view->setVar('staticUrl',  $view->getVar('staticUrl')  . $port);
                $view->setVar('fullUrl',    $view->getVar('fullUrl')    . $port);
                $view->setVar('serviceUrl', $view->getVar('serviceUrl') . $port);
                $view->setVar('imageUrl',   $view->getVar('imageUrl')   . $port);
                $view->setVar('fileUrl',    $view->getVar('fileUrl')    . $port);
                $view->setVar('forwardingPort', ':' . $port);
                $port = null;
                unset($port);
            }

            // model에서 사용하기위함
            define('PROTOCOL', $view->getVar('protocol'));
            define('STATIC_URL', $view->getVar('staticUrl'));
            define('SERVICE_URL', $view->getVar('serviceUrl'));
            define('FULL_URL', $view->getVar('fullUrl'));
            define('IMAGE_URL', $view->getVar('imageUrl'));
            define('FILE_URL', $view->getVar('fileUrl'));


//            $menuContainer = $infoSite->getMenuContainer();
//            foreach ($menuContainer->getItems() as $var){
//                $view->setVar('use' . ucwords($var->getCode()), $var->getUseFront());
//            }


            $loadTime = new LoadTime();
            $loadTime->setUrl(($router->getControllerName() != '' ? $router->getControllerName() : 'index') . '/' . ($router->getActionName() != '' ? $router->getActionName() : 'index'));
            $loadTime->setModule(strrchr($router->getModuleName(),'.'));
            $view->setVar('loadTime', $loadTime);



            /**
             * Mobile사용자인지 체크할지 여부.
             */

            if (APPLICATION_ENV == 'production') {
                $mobileMode = true;
            } elseif (APPLICATION_ENV == 'stage') {
                $mobileMode = true;
            } else {
                $mobileMode = true;
            }

            $mobileMode = false;

            $view->setVars(array(
                'isMobile'   => false,
                'isTablet'   => false,
                'voltMobile' => ''
            ));

            if($mobileMode === true){
                $detector = new Detector();
                if($detector->isTablet() == true){
                    $view->setVar('isTablet', true);
                    $view->setVar('isMobile', false);
                } elseif ($detector->isMobile() == true){
                    $view->setVar('isTablet', false);
                    $view->setVar('isMobile', true);
                }
                if($view->getVar('isMobile') == true){
                    $view->setVar('voltMobile', '.m');
                }
            }



            // robot metaTab
            $view->setVar('robot', 'N');


            // 번역된 context를 불러오기 위한 container 선언.
            $view->pageContextContainer = new PageContextContainer();



            $filterContainer = new FilterContainer();

            if($view->isMobile != true){
                // 모바일이 아닐경우에만 불러온다.


            } else {
                // only mobile
            }

            // 위젯 정보 저장
            $widgetId = trim($request->get('widgetId'));
            $widgetCode = trim($request->get('widgetCode'));
            $widget = WidgetContainer::isItem($widgetCode, 'code');
            if($widget instanceof Widget == true) {
                $view->setVar('widget', $widget);
                $view->setVar('widgetCode', $widgetCode);
                $view->setVar('widgetId', $widgetId);
            }

            $view->session = $session;

            try {
                $clientId    = $session->get('clientId');
                $clientToken = $session->get('clientToken');


                if (is_numeric($clientId) == true && $clientId >= 1) {
                    $client = Client::getInstance($clientId);
                    if($client == false){
                        throw new Exception();
                    } else {

                        // 로그인된 사용자의 상태 체크
                        if($client && ($client->getStatus() == Client::Status_Inactive || $client->getStatus() == Client::Status_Block)){
                            throw new Exception();
                        }
                        

                        if($client->isToken($clientToken) != true){
                            // 중복로그인 허용하지 않을경우
                            if($client->getEmail() == 'test'){
                                // 개발서버용 테스트 아이디는 중복으로 사용.

                            } else {

                                // 중복로그인 허용하지 않을경우
                                /*unset($clientId);
                                unset($client);
                                $session->remove('client');
                                $session->remove('clientId');
                                $session->remove('rewardPopupDisplay');
                                $view->client = null;
                                $view->clientId = 0;
                                $view->session->remove('clientId');
                                $view->session->remove('clientToken');
                                $view->session->remove('rewardPopupDisplay');*/
                            }
                        }

                        

                        if($clientId >= 1){
                            // 정상적으로 로그인됨
                            $view->client = $client;
                            $view->clientId = $clientId;


                            $view->setVar('userTimezoneId',   $client->getTimezoneId());
                            $view->setVar('userTimezone',     $client->getTimezone());


                            $dateTime = new \DateTime();
                            $dateTime->setTimeZone(new \DateTimeZone($client->getTimezone()));
                            $view->setVar('userTimezoneCode', $dateTime->format('T'));
                            //$view->setVar('userTimezoneCode', $admin->getTimezoneCode());


                            if($view->getVar('siteTimezoneId') != $view->getVar('userTimezoneId')){
                                // calc timezone offset
                                $timezoneQuery = 'SET TIME_ZONE = ?';
                                $db->query($timezoneQuery, array(Util::getStandardOffsetUTC($view->getVar('userTimezone'))));
                                $db_master->query($timezoneQuery, array(Util::getStandardOffsetUTC($view->getVar('userTimezone'))));
                                date_default_timezone_set($view->getVar('userTimezone'));
                                $view->setVar('now', new Date());
                            }

                            $view->clientTimezone = $client->getTimezone();
                            if($view->clientTimezone != ''){
                                $dateTime = new \DateTime();
                                $dateTime->setTimeZone(new \DateTimeZone($client->getTimezone()));
                                $view->clientTimezoneAbbr = $dateTime->format('T');
                                $view->clientTimezoneOffset = timezone_offset_get( timezone_open( $client->getTimezone() ), $dateTime );
                            } else {
                                $view->clientTimezoneAbbr = 'UTC';
                                $view->clientTimezoneOffset = 0;
                            }
                            $clientRewardPopupDisplay = $session->get('rewardPopupDisplay');
                            if($clientRewardPopupDisplay == 'Y'){
                                $view->rewardPopupDisplay = 'Y';

                                $session->remove('rewardPopupDisplay');
                                $view->session->remove('rewardPopupDisplay');
                            }

                            $session->remove('clientId');
                            $session->set('clientId', $clientId);

                        }

                        // 로그인시 팝업 삭제
                        $view->adPopup = 'disable';

                    }
                } else {
                    $view->client = '';
                    $view->clientId = 0;
                    $view->clientTimezone = '';

                    $view->session->remove('clientId');
                    $view->session->remove('clientToken');
                    $view->session->remove('rewardPopupDisplay');
                    //throw new Exception('관리자만 접속할 수 있습니다.');
                }
            } catch (Exception $e) {
                $view->client = '';
                $view->clientId = 0;
                $view->clientTimezone = '';

                $view->session->remove('clientId');
                $view->session->remove('clientToken');
                $view->session->remove('rewardPopupDisplay');
                $view->setVar('error', $e->getMessage());


                // 로그인 되지 않은 상태에서는 로그인 페이지로만 이동
                $uri     = getenv('REQUEST_URI');
                $uriData = parse_url($uri);
                if (strpos($uriData['path'], '/login') === false) {
                    //die('<script type="text/javascript">location.replace("/login");</script>');
                }
            }

            if($view->getVar('userTimezone') != ''){
                define('USER_TIMEZONE',      $view->getVar('userTimezone'));
                define('USER_TIMEZONE_ID',   $view->getVar('userTimezoneId'));
                define('USER_TIMEZONE_CODE', $view->getVar('userTimezoneCode'));
            } else {
                define('USER_TIMEZONE',      $view->getVar('siteTimezone'));
                define('USER_TIMEZONE_ID',   $view->getVar('siteTimezoneId'));
                define('USER_TIMEZONE_CODE', $view->getVar('siteTimezoneCode'));
            }


            try {
                /*
                // 채팅 서버 인증 토큰
                $chatAuthToken   = new ChatAuthToken();
                $view->authToken = $chatAuthToken->issue($view->loggedUser->getId());
                */

            } catch (Exception $e) {
                /*
                $view->client      = '';
                $view->clientId    = '';
                $view->authToken   = '';
                */
            }

            
            
            // @todo: 위젯용 설명글
            $view->mainTitle   = $infoSite->getMetaTitle();
            $view->metaTitle   = $infoSite->getMetaTitle();
            $view->metaContent = $infoSite->getMetaDescription();
            $view->metaKeyword = $infoSite->getMetaKeyword();
            if($infoSite->getSnsId() >= 1 && $infoSite->getSnsUrl() != ''){
                $view->metaImage = $infoSite->getSnsUrl();
            }elseif($infoSite->getLogoId() >= 1 && $infoSite->getLogoUrl() != ''){
                $view->metaImage = $infoSite->getLogoUrl();
            }elseif($infoSite->getSymbolId() >= 1 && $infoSite->getSymbolUrl() != ''){
                $view->metaImage = $infoSite->getSymbolUrl();
            } else {
                $view->metaImage = '';
            }
            //$view->metaImage   = $view->getVar('protocol') . '://' . $view->serviceUrl . '/assets/images/common/snsSharet1.png';
            $view->metaUrl     = $view->getVar('protocol') . '://' . $view->serviceUrl . $_SERVER["REQUEST_URI"];

            /**
             * 로그기록 중지
            $request = $di->get('request');
            $pageLogContainer = new PageLogContainer();
            $view->pageLogId = $pageLogContainer->createLog($router, $request, $view->clientId);
             * */

            $_SESSION['com_pl_www_' . SITE_BRANCH_CODE . '_checker'] = '1';
            return $view;
        });

    }
}

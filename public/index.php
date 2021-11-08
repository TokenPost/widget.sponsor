<?php
/**
 * PublishLink
 *
 * @package    PL
 *
 * Framework Phalcon
 * Phalcon Version	4.0.0
 *
 */

use Phalcon\Loader;
use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Crypt;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Model\Manager as ModelManager;
//use Phalcon\Session\Adapter\Files as SessionAdapter;

use Phalcon\Session\Manager;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Storage\AdapterFactory;
//use Phalcon\Storage\AdapterFactory;
use Phalcon\Session\Adapter\Redis;


use Phalcon\Cache\Backend\Memcache;
use Phalcon\Cache\Frontend\Data as FrontendCache;

// 서버 환경 변수에 따른 작동 모드 (production / staging / dev)
//defined('PHALCONDEBUG') || define('PHALCONDEBUG', true);



if(!function_exists('session_start_samesite')) {
    function session_start_modify_cookie()
    {
        $headers = headers_list();
        krsort($headers);
        foreach ($headers as $header) {
            if (!preg_match('~^Set-Cookie: PHPSESSID=~', $header)) continue;
            $header = preg_replace('~; secure(; HttpOnly)?$~', '', $header) . '; secure; SameSite=None';
            header($header, false);
            break;
        }
    }

    function session_start_samesite($options = [])
    {
        $res = session_start($options);
        session_start_modify_cookie();
        return $res;
    }

    function session_regenerate_id_samesite($delete_old_session = false)
    {
        $res = session_regenerate_id($delete_old_session);
        session_start_modify_cookie();
        return $res;
    }
}

define('CLIENT_IP', getIP());


// 해당 서버의 도메인별 code 매핑된걸 불러온다.
// d1.publishlink.com -> demo
$domainRouteConfigFile = __DIR__ . '/../config/domain_route.php';
if (file_exists($domainRouteConfigFile) === false) {
    die('config not found: domain route');
}

$di = new FactoryDefault();
$domainRouteConfigFile = new Config(require($domainRouteConfigFile));

// 개발서버 포트포워딩 문제 해결
$httpHost = strtolower(getenv('HTTP_HOST'));
if(strpos($httpHost, ':') !== false){
    $di->set('forwarding_port', function () use ($httpHost) {
        return substr($httpHost, strpos($httpHost, ':'));
    });

    $httpHost = substr($httpHost, 0, strpos($httpHost, ':'));
} else {
    $di->set('forwarding_port', function () {
        return '';
    });
}
define('HTTP_HOST', $httpHost);


if(isset($domainRouteConfigFile[HTTP_HOST]) == true){
    defined('APPLICATION_SITE') || define('APPLICATION_SITE', (getenv('APPLICATION_SITE') ? : $domainRouteConfigFile[HTTP_HOST]));
} else {
    // 등록되지 않은 사이트.
    // @addme: logging
    $fp = fopen( "/var/log/publishlink/phalconError.log", "a");
    //$fp = fopen( "/var/log/publishlink/" . strtolower(APPLICATION_SITE) . "/phalconError.log", "a");
    fwrite($fp, "Now : " . date('Y-m-d H:i:s', time()) . " : " . time() . "\n");
    fwrite($fp, "Client Ip : " . CLIENT_IP . "\n");
    fwrite($fp, "Parse error : Not registered site : " . $httpHost . " \n\n");
    //exit;

    die('domain not found: ' . HTTP_HOST);

    /*echo '<script type="text/javascript">';
    echo 'alert("Not registered site");';
    //echo 'history.back();';
    echo '</script>';
    break;*/
}





// DI 세팅
$configFile = __DIR__ . '/../config/' . strtolower(APPLICATION_SITE) . '/conf.php';
if (file_exists($configFile) === false) {
    die('config not found: conf_' . strtolower(APPLICATION_SITE));
}
$di->set('config', function () use ($configFile) {
    return new Config(require($configFile));
});


$passwordFile = __DIR__ . '/../config/' . strtolower(APPLICATION_SITE) . '/password.php';
if (file_exists($passwordFile) === false) {
    die('password file not found');
}
$di->set('passwordKey', function () use ($passwordFile) {
    //return new Config(require($passwordFile));
    $f = fopen($passwordFile, 'r');
    $line = fgets($f);
    fclose($f);
    return trim($line);
});

$config = $di->getShared('config');

// 도메인 분기에서 파일로 dev check
if(isset($config->env) == true && $config->env == 'stage'){
    define('APPLICATION_ENV', 'stage');
} else {
    if (file_exists(__DIR__ . '/../../isDev') == true) {
        define('APPLICATION_ENV', 'dev');
    } else {
        define('APPLICATION_ENV', 'production');
    }
}



// UTC시간 설정.
date_default_timezone_set('UTC');

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');


// 데이터베이스 설정
$di->set('db', function () use ($di) {
    $config = $di->get('config');

    return new Mysql(
        array(
            'host'     => $config->database->db1->hostname,
            'username' => $config->database->db1->username,
            'password' => $config->database->db1->password,
            'dbname'   => $config->database->db1->dbname,
            "options" => array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            )
        )
    );
});

$di->set('db_master', function () use ($di) {
    $config = $di->get('config');

    return new Mysql(
        array(
            'host'     => $config->database->db1->hostname,
            'username' => $config->database->db1->username,
            'password' => $config->database->db1->password,
            'dbname'   => $config->database->db1->dbname,
            "options" => array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            )
        )
    );
});


/**
 * did 앱에서 접속 제어
 * 운영에서 접속했을 때 다른 페이지 접속 못하게 제어
 * /app/certificationStep1?code=DDYh5jWI5Mdwy8SL
 * /app/certificationStep2?mdl_tkn=*********************&code=DDYh5jWI5Mdwy8SL
*/

if(APPLICATION_ENV === 'production') {
    $code = 'DDYh5jWI5Mdwy8SL'; // 고유 번호

    $parsed_url = parse_url($_SERVER['REQUEST_URI']);   // 현재 URL 값들을 분리해서 추출
    $path = trim($parsed_url['path']);

    if($_GET['code'] == '' || $_GET['code'] != $code) {
        // code 존재하지 않음, code 불일치
        header(getenv('SERVER_PROTOCOL') . ' 404 Not Found');
        exit;
    } else {
        // code 존재함
        // 접근 권한 체크
        $whiteList = array();
        $whiteList[] = "/app/certificationStep1";
        $whiteList[] = "/app/certificationStep2";

        if(in_array($path, $whiteList) !== true){
            // 접근권한이 없는
            header(getenv('SERVER_PROTOCOL') . ' 404 Not Found');
            exit;
        }
    }
}


// 데이터베이스 설정 - 2서버
/*
$di->set('db_2', function () use ($di) {
    $config = $di->get('config');

    return new Mysql(
        array(
            'host'     => $config->database->db_2->hostname,
            'username' => $config->database->db_2->username,
            'password' => $config->database->db_2->password,
            'dbname'   => $config->database->db_2->dbname,
            "options" => array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            )
        )
    );
});*/



// 각 Branch별 conf에서 domain가져와서 확인한다.
$di->set('router', function () use ($di) {
    $config = $di->getShared('config');

    $router = new Router();
    $router->removeExtraSlashes(true);

    $routerModule = '';
    if(in_array(HTTP_HOST, $config->domain->web->toArray()) == true) {
        $routerModule = 'web';
    } elseif(in_array(HTTP_HOST, $config->domain->admin->toArray()) == true) {
        $routerModule = 'admin';
    } elseif(in_array(HTTP_HOST, $config->domain->intra->toArray()) == true) {
        $routerModule = 'intra';
    } elseif(in_array(HTTP_HOST, $config->domain->api->toArray()) == true) {
        $routerModule = 'api';
    } elseif(in_array(HTTP_HOST, $config->domain->widget->toArray()) == true) {
        $routerModule = 'widget';
    } elseif(HTTP_HOST == '106.241.190.58'){
        $routerModule = 'api';
    }

    switch ($routerModule) {

        // Widget ;
        case 'widget':
            $router->setDefaultModule('com.publishlink.widget');

            $router->add('/:controller/:action/:params', array(
                'controller' => 1,
                'action'     => 2,
                'params'     => 3
            ));
            break;
        // Intra Api ;
        case 'intra':
            $router->setDefaultModule('com.publishlink.intra');

            $router->add('/:controller/:action/:params', array(
                'controller' => 1,
                'action'     => 2,
                'params'     => 3
            ));
            break;

        // Api
        case 'api':
            $router->setDefaultModule('com.publishlink.api');

            $router->add('/:controller/:action/:params', array(
                'controller' => 1,
                'action'     => 2,
                'params'     => 3
            ));

            $router->add('/(widget|news)/:controller/:action/:params', array(
                'controller' => 1,
                'action'     => 2,
                'function'   => 3,
                'params'     => 4
            ));
            break;

        // admin 사이트 관리 페이지
        case 'admin':
            $router->setDefaultModule('com.publishlink.admin');

            $router->add('/(login|logout)', array(
                'controller' => 'index',
                'action'     => 1
            ));

            $router->add('/:controller/:action/:params', array(
                'controller' => 1,
                'action'     => 2,
                'params'     => 3
            ));

            $router->add('/(board|conference|communication|knowledge|petition)/:action/:int/:params', array(
                'controller' => 1,
                'action'     => 2,
                'int'        => 3,
                'params'     => 4
            ));

            $router->add('/(board|conference|communication|knowledge|petition)/(add|modify)/:int/:params', array(
                'controller' => 1,
                'action'     => 'add',
                'mode'       => 2,
                'int'        => 3,
                'params'     => 4
            ));

            $router->add('/banner/:action/:int/:params', array(
                'controller' => 'banner',
                'action'     => 1,
                'int'        => 2,
                'params'     => 3
            ));

            $router->add('/banner/(add|modify)/:int/:params', array(
                'controller' => 'banner',
                'action'     => 'add',
                'mode'       => 1,
                'int'        => 2,
                'params'     => 3
            ));


            break;

        /**
         * Front Web
         */
        case 'web':

            $router->setDefaultModule('com.publishlink.www');

            $router->add('/(login|popuplogin|logout|search2)', array(
                'controller' => 'index',
                'action'     => 1
            ));

            $router->add('/browser/:params', array(
                'controller' => 'news',
                'action'     => 'browser',
                'params'      => 1
            ));

            $router->add('/search/:params', array(
                'controller' => 'article',
                'action'     => 'search',
                'params'     => 1
            ));


            $router->add('/:controller/:action/:params', array(
                'controller' => 1,
                'action'     => 2,
                'params'     => 3
            ));

            //팩트체크 카테고리
            $router->add('/factcheck/:action/:params', array(
                'controller'    => 'factcheck',
                'action'        => 'route',
                'categoryUrl'   => 1,
                'params'        => 2
            ));

            $router->add('/factcheck/(view|ajax|popup)/:params', array(
                'controller'    => 'factcheck',
                'action'        => 1,
                'params'        => 2
            ));

            $router->add('/factcheck/(add|modify)/:params', array(
                'controller' => 'factcheck',
                'action'     => 'add',
                'mode'       => 1,
                'params'     => 2
            ));

            $router->add('/factcheck/answer/(add|modify)/:params', array(
                'controller'    => 'factcheck',
                'action'        => 'answerAdd',
                'mode'          => 1,
                'params'        => 2
            ));

            $router->add('/factcheck/answer/(view)/:params', array(
                'controller'    => 'factcheck',
                'action'        => 'answerView',
                'mode'          => 1,
                'params'        => 2
            ));


            // 기사관련 카테고리
            $router->add('/article/:action/:params', array(
                'controller'    => 'article',
                'action'        => 'route',
                'categoryUrl'   => 1,
                'params'        => 2
            ));

            $router->add('/article/(add|modify)/:params', array(
                'controller' => 'article',
                'action'     => 'add',
                'params'     => 2,
            ));

            $router->add('/article/(view|ajax|popup)/:params', array(
                'controller'    => 'article',
                'action'        => 1,
                'params'        => 2
            ));

            $router->add('/opinion/:action/:params', array(
                'controller'    => 'opinion',
                'action'        => 'list',
                'params'        => 2,
                'contributor'   => 1
            ));

            $router->add('/opinion/ajax/:params', array(
                'controller'    => 'opinion',
                'action'        => 'ajax',
                'params'        => 1
            ));

            // 게시판 시작
            $router->add('/board/:params', array(
                'controller'    => 'board',
                'action'        => 'index',
                'params'        => 1
            ));
            $router->add('/board/:action/:params', array(
                'controller'    => 'board',
                'action'        => 'list',
                'boardCode'     => 1,
                'params'        => 2
            ));

            $router->add('/board/:action/:int/:params', array(
                'controller'    => 'board',
                'action'        => 'view',
                'boardCode'     => 1,
                'itemId'        => 2,
                'params'        => 3
            ));

            $router->add('/board/:action/(add|modify)/:params', array(
                'controller'    => 'board',
                'action'        => 'add',
                'boardCode'     => 1,
                'mode'          => 2,
                'params'        => 3
            ));

            $router->add('/board/ajax/:params', array(
                'controller'    => 'board',
                'action'        => 'ajax',
                'params'        => 1
            ));

            $router->add('/board/filedown/:params', array(
                'controller'    => 'board',
                'action'        => 'filedown',
                'params'        => 1
            ));




            $router->add('/rss', array(
                'controller'    => 'sitemap',
                'action'        => 'rss'
            ));


            $router->add('/topics/:params', array(
                'controller'    => 'topics',
                'action'        => 'index',
                'params'        => 1
            ));


            $router->add('/robots.txt', array(
                'controller'    => 'index',
                'action'        => 'robots'
            ));



            break;

        default:
            header(getenv('SERVER_PROTOCOL') . ' 404 Not Found');
            exit;
    }


    $request = new Phalcon\Http\Request();
    $router->handle($request->getURI());
    return $router;
});

// 세션 설정
$di->setShared('session', function () use ($di) {
    $config = $di->get('config');
    switch (HTTP_HOST) {

        case '192.168.0.12':
            session_set_cookie_params(7200, '/', '192.168.0.12');
            break;
        case '106.241.190.58':
            session_set_cookie_params(7200, '/', '106.241.190.58');
            break;


        // stage
        case 'mypage.publishdemo.com':
        case 'widget.publishdemo.com':
        case 'supportm.publishdemo.com':
        case 'supportw.publishdemo.com':
        case 'widget_stage.publishdemo.com':
        case 'widget_stage_widget.publishdemo.com':
            // stage
            session_set_cookie_params(7200, '/', 'publishdemo.com');
            break;

        case 'dev_api.elmindev1.com':
        case 'd1_api.elmindev1.com':
        case 'd2_api.elmindev1.com':
        case 'd3_api.elmindev1.com':
        case 'd4_api.elmindev1.com':
        case 'd5_api.elmindev1.com':
        case 'd6_api.elmindev1.com':
        case 'd7_api.elmindev1.com':
            session_set_cookie_params(7200, '/', '.elmindev1.com');
            break;

        case 'dev_admin.elmindev1.com':
        case 'd1_admin.elmindev1.com':
        case 'd2_admin.elmindev1.com':
        case 'd3_admin.elmindev1.com':
        case 'd4_admin.elmindev1.com':
        case 'd5_admin.elmindev1.com':
        case 'd6_admin.elmindev1.com':
        case 'd7_admin.elmindev1.com':
            session_set_cookie_params(7200, '/', '.elmindev1.com');
            break;

        case 'dev_widget.elmindev1.com':
        case 'd1_widget.elmindev1.com':
        case 'd2_widget.elmindev1.com':
        case 'd3_widget.elmindev1.com':
        case 'd4_widget.elmindev1.com':
        case 'd5_widget.elmindev1.com':
        case 'd6_widget.elmindev1.com':
        case 'd7_widget.elmindev1.com':
            session_set_cookie_params(7200, '/', '.elmindev1.com');
            break;

        case 'dev.elmindev1.com':
        case 'd1.elmindev1.com':
        case 'd2.elmindev1.com':
        case 'd3.elmindev1.com':
        case 'd4.elmindev1.com':
        case 'd5.elmindev1.com':
        case 'd6.elmindev1.com':
        case 'd7.elmindev1.com':
            session_set_cookie_params(7200, '/', '.elmindev1.com');
            break;

        case 'api.publishlink.com':
        case 'dev.api.publishlink.com':
        case 'd1.api.publishlink.com':
        case 'd2.api.publishlink.com':
        case 'd3.api.publishlink.com':
        case 'd4.api.publishlink.com':
        case 'd5.api.publishlink.com':
        case 'd6.api.publishlink.com':
        case 'd7.api.publishlink.com':
            session_set_cookie_params(7200, '/', 'api.publishlink.com');
            break;
        case 'admin.publishlink.com':
        case 'dev.admin.publishlink.com':
        case 'd1.admin.publishlink.com':
        case 'd2.admin.publishlink.com':
        case 'd3.admin.publishlink.com':
        case 'd4.admin.publishlink.com':
        case 'd5.admin.publishlink.com':
        case 'd6.admin.publishlink.com':
        case 'd7.admin.publishlink.com':
            session_set_cookie_params(7200, '/', 'admin.publishlink.com');
            break;

        case 'widget.publishlink.com':
        case 'dev.widget.publishlink.com':
        case 'd1.widget.publishlink.com':
        case 'd2.widget.publishlink.com':
        case 'd3.widget.publishlink.com':
        case 'd4.widget.publishlink.com':
        case 'd5.widget.publishlink.com':
        case 'd6.widget.publishlink.com':
        case 'd7.widget.publishlink.com':
            session_set_cookie_params(7200, '/', 'publishlink.com');
            break;

        case 'publishlink.com':
        case 'dev.publishlink.com':
        case 'd1.publishlink.com':
        case 'd2.publishlink.com':
        case 'd3.publishlink.com':
        case 'd4.publishlink.com':
        case 'd5.publishlink.com':
        case 'd6.publishlink.com':
        case 'd7.publishlink.com':
            session_set_cookie_params(7200, '/', 'publishlink.com');
            break;

        default:
            session_set_cookie_params(7200, '/', HTTP_HOST);
            //session_set_cookie_params(0, '/', '.pms.com');
            break;
    }
    //ini_set("session.gc_maxlifetime", "0");

    //ini_set("session.gc_maxlifetime", "0");
    //ini_set("session.cookie_domain", '.econotimes.com');
    //ini_set("session.save_path", "/var/www/session");


    $options = [
        'host'  => '127.0.0.1',
        'port'  => 6379,
        'index' => '1',
    ];
    session_start_samesite();
    //session_start_modify_cookie();
    $session = new Manager();

    /*$session->setOptions(
        [
            'cookie_secure' => true,
            'cookie_samesite' => 'None'
        ]
    );*/

    $serializerFactory = new SerializerFactory();
    $factory = new AdapterFactory($serializerFactory);
    $redis   = new Redis($factory, $options);
    $session->setAdapter($redis);

    /*
     * 멀티서버일경우 DB  // no longer available
     * 개별서버는 Files
     */

    $session->start();
    return $session;
});


// 암호화 키 설정
$di->set('crypt', function () {
    $crypt = new Crypt();
    //$crypt->setMode(MCRYPT_MODE_CFB);
    $crypt->setKey('h*H*uKpE*y4*A7Xg*8c7304');
    return $crypt;
});

// 쿠키 암호화
$di->set('cookies', function () {
    $cookies = new Cookies();
    $cookies->useEncryption(false);
    return $cookies;
});


// volt 템플릿 엔진 설정
$di->set('volt', function ($di) {
    $volt = new Volt($di);

    $volt->setOptions(
        array(
            'path'   => '../cache/volt/',
            'stat'   => true,
            'always' => true
            // 부모 페이지 변경이 되더라도 하위 페이지는 변동되지 않은 경우 재 컴파일 하지 않음. true 설정하여 재 컴파일 설정.
        )
    );

    $compiler = $volt->getCompiler();
    $compiler->addFilter('number_format', 'number_format');
    $compiler->addFilter('substr', 'substr');
    $compiler->addFilter('mb_substr', 'mb_substr');
    $compiler->addFilter('array_reverse', 'array_reverse');
    $compiler->addFilter('addslashes', 'addslashes');
    $compiler->addFilter('delComma', 'delComma');
    $compiler->addFilter('numberFormat', 'numberFormat');
    $compiler->addFilter('numberFormatSigned', 'numberFormatSigned');
    $compiler->addFilter('floor', 'floor');
    $compiler->addFilter('strip_tags', 'strip_tags');
    $compiler->addFilter('changeQuot', 'changeQuot');
    $compiler->addFilter('changeArrow', 'changeArrow');

    return $volt;
});

// 해당 사이트의 BranchId
// 나중에 여러 branch 동일하게 사용할경우 domain별로 파싱
define('BRANCH_ID', $config->branchId);
define('BRANCH_CODE', APPLICATION_SITE);


// 기본 헤더 전송
header('Content-Type: text/html; charset=UTF-8');

// Cross-Origin Resource Sharing
/*$allowedOrigin = array(
    'http://' . DOMAIN_WEB,
    'http://' . DOMAIN_ADMIN,
    'http://' . DOMAIN_STATIC,
    'http://' . DOMAIN_API,
);*/

//$httpOrigin = HTTP_HOST;
$httpOrigin = getenv('HTTP_ORIGIN');


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
header("Access-Control-Allow-Methods: POST, GET");
// 전체 블럭 풀기
/*
if (in_array($httpOrigin, $allowedOrigin) === true) {
    //header('Access-Control-Allow-Origin: ' . $httpOrigin);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
    header("Access-Control-Allow-Methods: POST, GET");
} elseif(APPLICATION_ENV == 'dev') {
    //header('Access-Control-Allow-Origin: ' . $httpOrigin);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
    header("Access-Control-Allow-Methods: POST, GET");
} else {
    if(BRANCH_ID == 1){
        header('Access-Control-Allow-Origin: *');
    } else {
        header('Access-Control-Allow-Origin: ' . 'http://' . HTTP_HOST);
    }

    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
    header("Access-Control-Allow-Methods: POST, GET");
}*/

require('../vendor/autoload.php');

try {

    $application = new Application($di);
    $application->registerModules(
        array(
            'com.publishlink.www'        => array(
                'className' => 'PL\Web\Module',
                'path'      => '../apps/com.publishlink.www/Module.php'
            ),
            'com.publishlink.admin'      => array(
                'className' => 'PL\Admin\Module',
                'path'      => '../apps/com.publishlink.admin/Module.php'
            ),
            'com.publishlink.intra'      => array(
                'className' => 'PL\Intra\Module',
                'path'      => '../apps/com.publishlink.intra/Module.php'
            ),
            'com.publishlink.api'      => array(
                'className' => 'PL\Api\Module',
                'path'      => '../apps/com.publishlink.api/Module.php'
            ),
            'com.publishlink.widget'      => array(
                'className' => 'PL\Widget\Module',
                'path'      => '../apps/com.publishlink.widget/Module.php'
            )
        )
    );

    /**
     * Handle the request
     */

    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
    /*if (empty($_GET)) {
        $uri = '/';
    } else {
        $uri = $_GET['_url'];
    }
    $application->handle($uri);*/

    /*$request = new Phalcon\Http\Request();
    $response = $application->handle($request->getURI());*/
    //$response = $application->handle( $_SERVER["REQUEST_URI"] );

    //$response->send();
    /*
    $request = new Phalcon\Http\Request();
    $response = $application->handle($request->getURI());
#$response = $application->handle(""); //This guy stopped working
    $response->send();*/
    //echo $application->handle($request->getURI())->getContent();

} catch (Exception $e) {
    // @addme: logging
    /*$dispatcher = $di->getShared('dispatcher');
    $dispatcher->forward(
        [
            'controller' => 'error',
            'action'     => 'show404',
        ]
    );*/


    // get domain
    $fp = fopen( "/var/log/publishlink/phalconError.log", "a");
    fwrite($fp, "Now : " . date('Y-m-d H:i:s', time()) . "\n");
    fwrite($fp, "Parse error : " . addslashes($e->getMessage()) . "\n\n");
    //exit;

    echo '<script type="text/javascript">';
    echo 'alert("' . addslashes($e->getMessage()) . '");';
    //echo 'history.back();';
    echo '</script>';
}

/**
 * 클라이언트 접속자의 IP를 가져옵니다
 * HTTP_X_FORWARDED_FOR -> Project shield 경유한 경우.
 * HTTP_CF_CONNECTING_IP -> CloudFlare 경유한 경우.
 * @return string
 */
function getIP() {
    if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) === true && $_SERVER["HTTP_X_FORWARDED_FOR"] != ''){
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if(isset($_SERVER["HTTP_CF_CONNECTING_IP"]) === true && $_SERVER["HTTP_CF_CONNECTING_IP"] != '') {
        $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
    } else {
        $ip = getenv('HTTP_X_REAL_IP');
        if ($ip == '') {
            $ip = getenv('REMOTE_ADDR');
        }
    }

    if(strpos($ip, ',') !== false && strpos($ip, ',') >= 7){
        $ip = substr($ip, 0, strpos($ip, ','));
    }

    return $ip;
}

/**
 * http를 https로 redirect 한다.
 * cf 경유의 경우 정보가 다르게온다.
 * project shield 경유할경우 다를수도.
 */
function redirectToHttps() {
    if(isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) === true && $_SERVER["HTTP_X_FORWARDED_PROTO"] != ''){
        $protocol = $_SERVER["HTTP_X_FORWARDED_PROTO"];
    } else {
        //$protocol = $_SERVER['HTTPS'];
        $protocol = $_SERVER['REQUEST_SCHEME'];
    }
    if(strtolower($protocol) != 'https') {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location:$redirect");
    }
}

function delComma($string){
    return \PL\Models\Util\Util::delComma($string);
}

function changeQuot($string){
    return \PL\Models\Util\Util::changeQuot($string);
}

function changeArrow($string){
    return \PL\Models\Util\Util::changeArrow($string);
}

function numberFormat($number){
    return \PL\Models\Util\Util::numberFormat($number);
}

function numberFormatSigned($number){
    return \PL\Models\Util\Util::numberFormatSigned($number);
}

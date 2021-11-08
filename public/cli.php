<?php
use Phalcon\Exception;
use Phalcon\Loader;
use Phalcon\Config;
use Phalcon\CLI\Console as ConsoleApp;
use Phalcon\DI\FactoryDefault\CLI as CliDI;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;

define('VERSION', '1.0.0');
chdir(__DIR__);

/*
 * 모든 서버의 경로가 같기때문에 사용하지 않는다.
// 서버 환경 변수에 따른 작동 모드 (production / stage / dev )
if (__DIR__ == '/var/www/public') {
    define('APPLICATION_ENV', 'production');
} else {
    define('APPLICATION_ENV', 'dev');
}
*/

// cli만 실행가능.
if(php_sapi_name() != 'cli'){
    exit;
}

$console = new ConsoleApp();
$arguments = array();
$params    = array();

foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['site'] = $arg;
    } elseif ($k == 2) {
        $arguments['task'] = $arg;
    } elseif ($k == 3) {
        $arguments['action'] = $arg;
    } elseif ($k >= 4) {
        $params[] = $arg;
    }
}

if (count($params) > 0) {
    $arguments['params'] = $params;
}


define('APPLICATION_SITE', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_TASK', (isset($argv[2]) ? $argv[2] : null));
define('CURRENT_ACTION', (isset($argv[3]) ? $argv[3] : null));

$domainRouteConfigFile = __DIR__ . '/../config/domain_route.php';
if (file_exists($domainRouteConfigFile) === false) {
    die('config not found: domain route');
}


// branchCode만 받는다.
//$configFile = __DIR__ . '/../config/conf_' . strtolower(APPLICATION_SITE) . '.php';
//$configFile = __DIR__ . '/../config/' . strtolower(APPLICATION_SITE) . '/conf.php';
$configFile = __DIR__ . '/../config/dev/conf.php';

var_dump($configFile);

if (file_exists($configFile) === false) {
    die('config not found: ' . APPLICATION_SITE);
}

$di = new CliDI();

$di->set('config', function () use ($configFile) {
    return new Config(require($configFile));
});



$config = $di->get('config');
define('BRANCH_ID', $config->branchId);



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


if (file_exists(__DIR__ . '/../../isCron') == true) {
    define('APPLICATION_ENV_CRON', 'Y');
} else {
    // 테스트용 강제.
    define('APPLICATION_ENV_CRON', 'N');
    //define('APPLICATION_ENV_CRON', 'Y');
}


//$passwordFile = __DIR__ . '/../config/' . strtolower(APPLICATION_SITE) . '/password.php';
$passwordFile = __DIR__ . '/../config/dev/password.php';

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


// volt 템플릿 엔진 설정
$di->set('volt', function ($view, $di) {
    $volt = new Volt($view, $di);

    $volt->setOptions(
        array(
            'compiledPath'  => '../cache/volt/',
            'stat'          => true,
            'compileAlways' => true
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

    return $volt;
});

$di->set('view', function () use ($di) {
    $view = new View();
    $view->setViewsDir('../apps/com.publishlink.www/views/');
    $view->registerEngines([
        '.volt' => 'volt'
    ]);
    return $view;
});


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
            'host'     => $config->database->db_master->hostname,
            'username' => $config->database->db_master->username,
            'password' => $config->database->db_master->password,
            'dbname'   => $config->database->db_master->dbname,
            "options" => array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            )
        )
    );
});



$console->setDI($di);
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

$loader = new Loader();
$loader->registerDirs(
    array(
        APPLICATION_PATH . '/tasks'
    )
);

require('../vendor/autoload.php');
$loader->registerNamespaces(
    array(
        //    'Zend'            => '../models/Zend/',
        'Phalcon'    => '../models/Phalcon/',
        'PL\Models'  => '../models/PL/',
        'ElephantIO' => '../models/ElephantIO'
    )
);
$loader->register();

define('CLIENT_IP', getIP());

try {
    $console->handle($arguments);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(255);
}

function getIP() {
    $ip = '0.0.0.0';

    return $ip;
}
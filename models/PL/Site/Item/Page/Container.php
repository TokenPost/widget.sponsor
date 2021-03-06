<?php
namespace PL\Models\Site\Item\Page;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Util\HTML;
use Phalcon\Http\Client\Provider\Curl as Curl;

use PL\Models\Site\Item\Item;
use PL\Models\Site\Item\Host\Container as HostContainer;



class Container extends AbstractContainer {

    protected $_itemInstance;
    protected $_itemId;

    protected $_typeId;

    public function __construct(Item $itemInstance = null) {
        parent::__construct(Page::tableName);
        $this->setTableName(Page::tableName);

        if(is_null($itemInstance) == false) {
            $this->setItemInstance($itemInstance);
        }
    }

    public static function getTableNameStatic(){
        return Page::tableName;
    }

    public static function getObjectInstanceStatic($data) : Page {
        return Page::getInstance($data);
    }

    public function getObjectInstance($data) : Page {
        return Page::getInstance($data);
    }

    public function getItemInstance(){
        return $this->_itemInstance;
    }

    public function setItemInstance(Item $itemInstance){
        $this->_itemInstance = $itemInstance;
        $this->setItemId($itemInstance->getId());
    }

    public function getItemId(){
        return $this->_itemId;
    }

    public function setItemId($itemId) {
        $this->_itemId = $itemId;
    }

    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getItemId()) == true) $where[] = '`itemId` = "' . $this->getItemId() . '"';
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

    public function addPageInfomation($url) {

        if($url == '' || !(strpos($url, 'http://') == 0 || strpos($url, 'https://') == 0) ) {
            throw new Exception('http(s)??? ???????????? ?????? URL??? ??????????????????.');
        }

        if(APPLICATION_ENV == 'dev') {
            $urlData = parse_url($url);
            $scheme = trim($urlData['scheme']);
            $host = trim($urlData['host']);
            $path = trim($urlData['path']);
            $surl = explode($path, $url);
        }

        $url = 'https://www.tokenpost.kr/article-7292';
        $html = $this->getContentWithAgent($url);
        $domain = '';

        if($html == ''){
            // ????????? url ?????? ??????????
            throw new Exception('URL??? ???????????? ????????????. code : 111');
        } else {
            // check exist domain
            $urlInfo = parse_url($url);

            if(isset($urlInfo['host']) == true && $urlInfo['host'] != ''){
                $domain = $urlInfo['host'];
            } else {
                //parse error
            }
        }
        if($domain == '') throw new Exception('URL??? ???????????? ????????????. code : 222');

        // DNS check
        if(checkdnsrr($domain , "A") != true) throw new Exception('URL??? ???????????? ????????????.');


        // ???????????? URL, Domain
        // ?????? ??????????????? ??????
        $hostContainer = new HostContainer();
        $existDomain = $hostContainer->_isItem($domain, 'host');

        // path ??????
        $path = $urlInfo['path'];
        $pageContainer = $existDomain->getItemInstance()->getPageContainer();
        $existPath = $pageContainer->_isItem($path, 'path');

        if($existPath){
            // ????????? ????????? ????????????.
            return $existPath;
        } else {
            // ????????? ?????? ??????
            $html = substr($html, 0, strpos($html, '<body>'));
            $htmlInstance = new HTML($html);

            // ???????????? ??? ??????
            // title, image, description, author, regDate
            $title = $htmlInstance->getOne('/html/head/title');
            $author = '';
            $image = '';
            $description = '';

            foreach ($htmlInstance->getRows('/html/head/meta') as $key => $var) {
                if($author == '') {
                    if(strtolower($var->getAttr('//meta', 'name')) == 'author') {
                        $author = $var->getAttr('//meta', 'content');
                    } elseif($author == '' && strtolower($var->getAttr('//meta', 'name')) == 'writer') {
                        $author = $var->getAttr('//meta', 'content');
                    }
                }

                if($image == '') {
                    if($var->getAttr('//meta', 'property') == 'og:image') {
                        $image = $var->getAttr('//meta', 'content');
                    } elseif($var->getAttr('//meta', 'name') == 'twitter:image') {
                        $author = $var->getAttr('//meta', 'content');
                    }
                }

                if($description == '') {
                    if($var->getAttr('//meta', 'property') == 'og:description') {
                        $description = $var->getAttr('//meta', 'content');
                    } elseif($var->getAttr('//meta', 'name') == 'twitter:description') {
                        $description = $var->getAttr('//meta', 'content');
                    } elseif(strtolower($var->getAttr('//meta', 'name')) == 'description') {
                        $description = $var->getAttr('//meta', 'content');
                    }
                }
            }

            if($author == ''){
                if($title != '' ){
                    if(strrpos($title, '-') != false){
                        $author = trim(substr($title, strrpos($title,'-') + 1));
                    }elseif(strrpos($title, '_') != false){
                        $author = trim(substr($title, strrpos($title,'_') + 1));
                    }
                }
            }

            // ????????? ????????? copyright???????
            if($author == ''){
                foreach ($htmlInstance->getRows('/html/head/meta') as $key => $var){
                    if($author == ''){
                        if($var->getAttr('//meta', 'name') == 'copyright'){
                            $author = $var->getAttr('//meta', 'content');
                        }elseif($var->getAttr('//meta', 'property') == 'og:site_name'){
                            $author = $var->getAttr('//meta', 'content');
                        }
                    }
                }
            }

            if($author == ''){
                $author = $title;
            }

            $newPageItem = array();
            $newPageItem['itemId']          = $existDomain->getItemInstance()->getId();
            $newPageItem['host']            = $domain;
            $newPageItem['path']            = $path;
            $newPageItem['author']          = $author;
            $newPageItem['title']           = $title;
            $newPageItem['image']           = $image;
            $newPageItem['description']     = $description;
            $newPageItem['regDate']         = Util::getLocalTime();

            $ret = $pageContainer->addNew($newPageItem);
            if($ret == false){
                // ??????
                throw new Exception('????????? ?????? ????????? ?????????????????????.');
            }

            $pageInstance = Page::getInstance($ret);
            if($pageInstance instanceof Page != true) {
                return false;
            } else {
                return $pageInstance;
            }
        }
    }

    public function parseUrl($url) {

        if($url == '' || !(strpos($url, 'http://') == 0 || strpos($url, 'https://') == 0) ){
            throw new Exception('http(s) ??? ???????????? ?????? URL??? ??????????????????');
        }
        $html = $this->getContentWithAgent($url);
        $domain = '';

        if($html == ''){
            // ????????? url ?????? ??????????
            if(APPLICATION_ENV != 'dev') {
                throw new Exception('URL??? ???????????? ????????????.');
            } else {
                return array();
            }
        } else {
            // check exist domain
            $urlInfo = parse_url($url);

            if(isset($urlInfo['host']) == true && $urlInfo['host'] != ''){
                $domain = $urlInfo['host'];
            } else {
                //parse error
            }
        }

        if($domain == '') throw new Exception('URL??? ???????????? ????????????.');

        // DNS check
        if(APPLICATION_ENV != 'dev') {
            if(checkdnsrr($domain , "A") != true) throw new Exception('URL??? ???????????? ????????????.');
        }

        $title = '';
        $author = '';
        $description = '';
        $image = '';

        if(APPLICATION_ENV != 'dev') {
            $html = substr($html, 0, strpos($html, '<body>'));
            $htmlInstance = new HTML($html);

            if($htmlInstance->getOne('/html/head/title') != '') {
                $title = $htmlInstance->getOne('/html/head/title');
            }

            foreach ($htmlInstance->getRows('/html/head/meta') as $key => $var) {
                // ????????? ???
                if(strtolower($var->getAttr('//meta', 'name')) == 'author') {
                    $author = $var->getAttr('//meta', 'content');
                }

                if(strtolower($var->getAttr('//meta', 'name')) == 'description') {
                    $description = $var->getAttr('//meta', 'content');
                }

                if(strtolower($var->getAttr('//meta', 'name')) == 'image') {
                    $image = $var->getAttr('//meta', 'content');
                }
            }

            $newPageItem = array();
            $newPageItem['author'] = $author;
            $newPageItem['title'] = $title;
            $newPageItem['description'] = $description;
            $newPageItem['image'] = $image;

            return $newPageItem;
        } else {
            return array();
        }

    }

}
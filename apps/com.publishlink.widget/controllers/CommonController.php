<?php
namespace PL\Widget\Controllers;

use Exception;
use Phalcon\DI;
use Phalcon\Mvc\Controller;

use Facebook;
use Abraham\TwitterOAuth\TwitterOAuth;

use PL\Models\Util\Util;

use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;

class CommonController extends Controller {

    /**
     * SNS 연동 로그인
     * 트위터
     */
    public function authTwitterAction() {
        if (is_null($this->session->clientId) != true) {
            // 로그인 되어있는 정보 삭제
            $this->session->remove('clientId');
            $this->session->destroy();
        }

        require_once '../vendor/twitteroauth/autoload.php';

        define('CONSUMER_KEY', "ab98ZpJYEFq61410ykWBGl5dd");
        define('CONSUMER_SECRET', "wmQiqm7e5khVzT6XKacHpJTrNrDb43JcPbnvU2eAqH7pBQy21l");
        $redirectUrl = $this->view->staticUrl.'/common/authTwitter';
        define('OAUTH_CALLBACK', $redirectUrl);

        unset($_SESSION['twitter_state']); // 세션 삭제
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
        $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_GET['oauth_verifier']]);
        $_SESSION['access_token'] = $access_token;

        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $result = $connection->get('account/verify_credentials', ['include_email' => 'true']);

        if(isset($result) == true) {
            $name = $result->name;
            $email = $result->email;
            if($name==null || $email == '') { echo "트위터 정보 가져오는데 실패했습니다."; exit; }

            $loginType = Client::Login_Twitter;

            $clientContainer = new ClientContainer();
            $clientInstance = $clientContainer->_isItem($email, 'email');

            if(empty($clientInstance) == true) {
                // 신규 가입
                // Save it in session : client email, sns login type
                $_SESSION['sns_email'] = $email;
                $_SESSION['sns_type'] = Client::Login_Twitter;

                // 약관동의 페이지로 이동
                $html = "";
                $html .= "<script>";
                $html .= "window.opener.location.replace('". $this->view->serviceUrl ."/common/join');";
                $html .= "window.close();";
                $html .= "</script>";
            } else {
                // 이미 존재하는 회원
                // 동일한 다른 SNS 계정이 있을 수도 있고 해당 이메일로 가입했을 수도 있음
                if($clientInstance == false) throw new Exception($this->view->pageContextContainer->getContext($this->view->siteLanguageCode , 1500));
                if($clientInstance->getLoginType() == $loginType) {
                    // 로그인 처리
                    // 트위터로 가입한 회원
                    $this->session->set('clientId', $clientInstance->getId());
                    $this->session->set('clientToken', $clientInstance->newToken());

                    $clientInstance->addLoginHits();
                    $ret = $clientInstance->addLoginLog(Util::getClientIp(), Client::Login_Twitter);

                    $html = "";
                    $html .= "<script>";
                    // 위젯에서 온 경우
                    $html .= "window.close();";
                    $html .= "window.opener.location.reload();";
                    $html .= "</script>";
                } else {
                    // 로그인 페이지로 이동
                    // 다른 계정(동일한 이메일)으로 가입한 회원
                    $html = "";
                    $html .= "<script>";
                    $html .= "window.close();";
                    $html .= "alert('다른 간편 로그인으로 가입된 이메일입니다.');";
                    $html .= "</script>";
                }
            }
            echo $html;
        } else {
            echo "트위터 정보 가져오는데 실패했습니다.";
            exit;
        }
    }

    /**
     * SNS 연동 로그인
     * Google
    */
    public function authGoogleAction() {
        try{
            if(isset($_GET['code'])) {

                if (is_null($this->session->clientId) != true) {
                    // 로그인 되어있는 정보 삭제
                    $this->session->remove('clientId');
                    $this->session->destroy();
                }

                $redirect_uri = $this->view->staticUrl . "/common/authGoogle";

                $ch = curl_init();
                $fields = [
                    'code'=>$_GET['code']
                    ,'client_id'=>"993658457227-2mr318j8rbuml7ab7tiktnhrcohpfv0c.apps.googleusercontent.com"
                    ,'client_secret'=>"7M9F0-DLx15F4BcHjs105ISL"
                    ,'redirect_uri'=> $redirect_uri
                    ,'grant_type'=>'authorization_code'
                ];

                // access token 요청
                $postvars = '';
                foreach($fields as $key => $value) {
                    $postvars .= $key . "=" . $value . "&";
                }
                $url = 'https://www.googleapis.com/oauth2/v3/token';
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_POST, 1);                //0 for a get request
                curl_setopt($ch,CURLOPT_POSTFIELDS, $postvars);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,3);
                curl_setopt($ch,CURLOPT_TIMEOUT, 20);
                $response = curl_exec($ch);
                $oJson  = json_decode($response);
                curl_close ($ch);
                if(!isset($oJson->access_token)) {
                    echo 'Error Occureed '.__LINE__;
                    exit;
                }

                //access token으로 계정 정보 요청
                $ch = curl_init();
                $url = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $oJson->access_token;
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
                curl_setopt($ch,CURLOPT_TIMEOUT, 20);
                $response = curl_exec($ch);
                $oJson = json_decode($response);
                curl_close ($ch);

                if(!isset($oJson->name)) {
                    echo 'Error Occureed '.__LINE__;
                    exit;
                }

                $loginType = Client::Login_Google;
                $email = trim($oJson->email);

                $url = parse_url($_SERVER['REQUEST_URI']);
                $query = $url['query'];

                /* 수신 데이터를 이용하여 로그인 처리 및 회원 가입 */
                // 존재하는 회원인지 확인
                $clientContainer = new ClientContainer();
                $clientInstance = $clientContainer->_isItem($email, 'email');
                if(empty($clientInstance) == true) {
                    // 신규 가입
                    // Save it in session : client email, sns login type
                    $_SESSION['sns_email'] = $email;
                    $_SESSION['sns_type'] = Client::Login_Google;

                    // 약관동의 페이지로 이동
                    $html = "";
                    $html .= "<script>";
                    $html .= "window.opener.location.replace('". $this->view->serviceUrl ."/common/join');";
                    $html .= "window.close();";
                    $html .= "</script>";

                } else {
                    // 이미 존재하는 회원
                    // 동일한 다른 SNS 계정이 있을 수도 있고 해당 이메일로 가입했을 수도 있음
                    if($clientInstance == false) throw new Exception($this->view->pageContextContainer->getContext($this->view->siteLanguageCode , 1500));
                    if($clientInstance->getLoginType() == $loginType) {
                        // 로그인 처리
                        // Google 로 가입한 회원
                        $this->session->set('clientId', $clientInstance->getId());
                        $this->session->set('clientToken', $clientInstance->newToken());

                        $clientInstance->addLoginHits();
                        $ret = $clientInstance->addLoginLog(Util::getClientIp(), Client::Login_Google);

                        $html = "";
                        $html .= "<script>";
                        // 위젯에서 온 경우
                        $html .= "window.close();";
                        $html .= "window.opener.location.reload();";
                        $html .= "</script>";
                    } else {
                        // 로그인 페이지로 이동
                        // 다른 계정(동일한 이메일)으로 가입한 회원
                        $html = "";
                        $html .= "<script>";
                        $html .= "window.close();";
                        $html .= "alert('다른 간편 로그인으로 가입된 이메일입니다.');";
                        $html .= "</script>";
                    }
                }
                echo $html;
                exit;
            } else {
                echo 'token is needed';
                exit;
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * SNS 연동 로그인
     * 카카오
     */
    public function authKakaoAction() {

        if (is_null($this->session->clientId) != true) {
            // 로그인 되어있는 정보 삭제
            $this->session->remove('clientId');
            $this->session->destroy();
        }

        $client_id = "ed08b75c9d6ecf7211e066f6dfab5dc1";
        $code = $_GET["code"];

        $redirectURI = urlencode($this->view->staticUrl."/common/authKakao");
        $url = "https://kauth.kakao.com/oauth/token?grant_type=authorization_code&client_id=".$client_id."&redirect_uri=".$redirectURI."&code=".$code;
        $is_post = false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);
        if($status_code != 200) {
            echo "Error 내용:".$response;
            exit;
        }

        $oResponse = json_decode($response, true);
        $token = $oResponse['access_token'];
        $header = "Bearer ".$token;
        $url = "https://kapi.kakao.com/v2/user/me";
        $is_post = false;

        // -------------------------------------------------------------------------------------------------------------
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = "Authorization: ".$header;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);

        if($status_code == 200) {
            $oResult = json_decode($response, true);

            $loginType = Client::Login_KaKao;
            $email = trim($oResult["kakao_account"]["email"]);

            $clientContainer = new ClientContainer();
            $clientInstance = $clientContainer->_isItem($email, 'email');
            if(is_null($clientInstance) == true) {
                // 신규 가입
                // Save it in session : client email, sns login type
                $_SESSION['sns_email'] = $email;
                $_SESSION['sns_type'] = Client::Login_KaKao;

                // 약관동의 페이지로 이동
                $html = "";
                $html .= "<script>";
                $html .= "window.opener.location.replace('". $this->view->serviceUrl ."/common/join');";
                $html .= "window.close();";
                $html .= "</script>";
            } else {
                // 이미 존재하는 회원
                // 동일한 다른 SNS 계정이 있을 수도 있고 해당 이메일로 가입했을 수도 있음
                if($clientInstance == false) throw new Exception($this->view->pageContextContainer->getContext($this->view->siteLanguageCode , 1500));
                if($clientInstance->getLoginType() == $loginType) {
                    // 로그인 처리
                    // 카카오로 가입한 회원
                    $this->session->set('clientId', $clientInstance->getId());
                    $this->session->set('clientToken', $clientInstance->newToken());

                    $clientInstance->addLoginHits();
                    $ret = $clientInstance->addLoginLog(Util::getClientIp(), Client::Login_KaKao);

                    $html = "";
                    $html .= "<script>";
                    // 위젯에서 온 경우
                    $html .= "window.close();";
                    $html .= "window.opener.location.reload();";
                    $html .= "</script>";
                } else {
                    // 로그인 페이지로 이동
                    // 다른 계정(동일한 이메일)으로 가입한 회원
                    $html = "";
                    $html .= "<script>";
                    $html .= "window.close();";
                    $html .= "alert('다른 간편 로그인으로 가입된 이메일입니다.');";
                    $html .= "</script>";
                }
            }
            echo $html;
        } else {
            echo "Error 내용:".$response;
            exit;
        }
    }


    /**
     * SNS 연동 로그인
     * 네이버
     */
    public function authNaverAction() {

        if (is_null($this->session->clientId) != true) {
            // 로그인 되어있는 정보 삭제
            $this->session->remove('clientId');
            $this->session->destroy();
        }


        if($this->view->staticUrl == 'supportm.publishdemo.com') {
            // support
            $client_id = "ZI7_ddX9huQOQ_NYetvg";
            $client_secret = "m9zKe3maTA";
        } else {
            // stage
            $client_id = "eb90otHBjoBZsdjE09dp";
            $client_secret = "o1c47WMafe";
        }
        $code = $_GET["code"];
        $state = $_GET["state"];

        $redirectURI = urlencode($this->view->staticUrl. "/common/authNaver");
        $url = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id=".$client_id."&client_secret=".$client_secret."&redirect_uri=".$redirectURI."&code=".$code."&state=".$state;
        $is_post = false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close ($ch);
        if($status_code != 200) {
            echo "Error 내용:".$response;
            exit;
        }

        $oResponse = json_decode($response, true);
        $token = $oResponse['access_token'];
        $header = "Bearer ".$token; // Bearer 다음에 공백 추가
        $url = "https://openapi.naver.com/v1/nid/me";
        $is_post = false;

        // -------------------------------------------------------------------------------------------------------------
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = "Authorization: ".$header;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);

        if($status_code == 200) {
            // 성공
            $oResult = json_decode($response, true);
            $loginType = Client::Login_Naver;
            $email = trim($oResult['response']['email']);

            /* 수신 데이터를 이용하여 로그인 처리 및 회원 가입 */
            $clientContainer = new ClientContainer();
            $clientInstance = $clientContainer->_isItem($email, 'email');
            if(is_null($clientInstance) == true) {
                // 신규 가입
                // Save it in session : client email, sns login type
                $_SESSION['sns_email'] = $email;
                $_SESSION['sns_type'] = Client::Login_Naver;

                // 약관동의 페이지로 이동
                $html = "";
                $html .= "<script>";
                $html .= "window.opener.location.replace('". $this->view->serviceUrl ."/common/join');";
                $html .= "window.close();";
                $html .= "</script>";
            } else {
                // 이미 존재하는 회원
                // 동일한 다른 SNS 계정이 있을 수도 있고 해당 이메일로 가입했을 수도 있음
                if($clientInstance == false) throw new Exception($this->view->pageContextContainer->getContext($this->view->siteLanguageCode , 1500));
                if($clientInstance->getLoginType() == $loginType) {
                    // 네이버로 가입한 회원
                    $this->session->set('clientId', $clientInstance->getId());
                    $this->session->set('clientToken', $clientInstance->newToken());

                    $clientInstance->addLoginHits();
                    $ret = $clientInstance->addLoginLog(Util::getClientIp(), Client::Login_Naver);

                    $html = "";
                    $html .= "<script>";
                    // 위젯에서 온 경우
                    $html .= "window.close();";
                    $html .= "window.opener.location.reload();";
                    $html .= "</script>";

                } else {
                    // 로그인 페이지로 이동
                    // 다른 계정(동일한 이메일)으로 가입한 회원
                    $html = "";
                    $html .= "<script>";
                    $html .= "window.close();";
                    $html .= "alert('다른 간편 로그인으로 가입된 이메일입니다.');";
                    $html .= "</script>";
                }
            }
            echo $html;
        } else {
            echo "Error 내용:".$response;
            exit;
        }

    }




    /**
     * ajax Action 분기처리.
     * @param $mode
     */
    public function ajaxAction($mode) {

        switch (trim($mode)){
            case 'authFacebook' :
                $this->_ajaxAuthFacebookAction();
                break;
            default:
                $this->_ajaxErrorAction();
                break;
        }
    }

    /**
     * ErrorAction
     */
    public function _ajaxErrorAction(){
        $response            = array();
        $response['error']   = 1;
        $response['message'] = $this->view->pageContextContainer->getContext($this->view->siteLanguageCode , 1459);

        die(json_encode($response));
    }

    /**
     * SNS 연동 로그인 - Facebook
     */
    private function _ajaxAuthFacebookAction() {
        try {

            if (is_null($this->session->clientId) != true) {
                // 로그인 되어있는 정보 삭제
                $this->session->remove('clientId');
                $this->session->destroy();
            }

            $token = $this->request->get('t');

            if(!isset($token)) {
                echo 'token is needed'; exit;
            }

            $fb = new Facebook\Facebook([
                'app_id' => '593057295186118',
                'app_secret' => 'cb22473a355331b89b4f50570da44f20',
                'default_graph_version' => 'v12.0',
                'REQUEST_HEADERS'   => 'User-Agent'
            ]);


            try {
                // Get the \Facebook\GraphNodes\GraphUser object for the current user.
                // If you provided a 'default_access_token', the '{access-token}' is optional.
                $response = $fb->get('/me?fields=id,name,email', $token);
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo json_encode(['result'=>$e->getMessage()]); exit;
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo json_encode(['result'=>$e->getMessage()]); exit;
            }

            $user = $response->getGraphUser();
            $email = $user['email'];
            $loginType = Client::Login_Facebook;

            $response            = array();
            /* 수신 데이터를 이용하여 로그인 처리 및 회원 가입 */
            // 존재하는 회원인지 확인
            $clientContainer = new ClientContainer();
            $clientInstance = $clientContainer->_isItem($email, 'email');

            if(empty($clientInstance) == true) {
                // 신규 가입
                // Save it in session : client email, sns login type
                $_SESSION['sns_email']  = $email;
                $_SESSION['sns_type']   = Client::Login_Facebook;

                $response['error']      = 0;
                $response['status']     = 200;
                $response['type']       = 'new';
            } else {
                // 이미 존재하는 회원
                // 동일한 다른 SNS 계정이 있을 수도 있고 해당 이메일로 가입했을 수도 있음
                if($clientInstance == false) throw new Exception($this->view->pageContextContainer->getContext($this->view->siteLanguageCode , 1500));
                if($clientInstance->getLoginType() == $loginType) {
                    // 로그인 처리
                    // 페이스북으로 가입한 회원
                    $this->session->set('clientId', $clientInstance->getId());
                    $this->session->set('clientToken', $clientInstance->newToken());

                    $clientInstance->addLoginHits();
                    $ret = $clientInstance->addLoginLog($this->request->getClientAddress(), Client::Login_Facebook);

                    $response['error']      = 0;
                    $response['status']     = 200;
                    $response['type']       = 'exist';
                    $response['snsLoginId'] = $clientInstance->getId();
                } else {
                    // 로그인 페이지로 이동
                    // 다른 계정(동일한 이메일)으로 가입한 회원
                    $response['error']      = 0;
                    $response['status']     = 200;
                    $response['type']       = 'other';
                }
            }
            
        } catch (Exception $e) {
            $response            = array();
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }
        die(json_encode($response));
    }



}

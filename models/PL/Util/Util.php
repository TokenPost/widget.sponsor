<?php
namespace PL\Models\Util;

use Phalcon\Db;

use PL\Models\File\File;
use PL\Models\File\Container as FileContainer;
use PL\Models\News\Article\Article;
use DateTime;
use PL\Models\Wiki\Wiki;

use Phalcon\Http\Client\Provider\Curl;

use PL\Models\Client\Container as ClientContainer;

/**
 * 공통사용 함수 목록
 */


class Util{

    /**
     * @param $url
     * @return bool
     * URL 사용중인경우 기본 예약어 확인
     */
    public static final function isReservedUrl($url){
        $url = strtolower(trim($url));
        if($url == 'all') return true;
        if($url == 'ajax') return true;
        if($url == 'popup') return true;
        if($url == 'index') return true;
        if($url == 'popular') return true;
        if($url == 'mostpopular') return true;
        if($url == 'latest') return true;
        return false;
    }

    public static final function getMenuList($branchId = 1){
        /**                         'Read','Write','Edit','Delete','Publish' */

        if($branchId == 1){
            return [
                'news'              => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'publish'           => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'topic'             => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'store'             => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'newsUpdate'        => array('N', 'N', 'N', 'N', 'Y'),
                'calendarIndicator' => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'calendarItem'      => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'calendarUpload'    => array('N', 'Y', 'N', 'N', 'Y'),
                'calendarRelease'   => array('N', 'Y', 'N', 'N', 'Y'),
                //'dailyNews'         => array('Y', 'N', 'N', 'N', 'Y'),
                //'announcement'      => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'report'            => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'proAnnouncement'   => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'client'            => array('Y', 'N', 'N', 'N', 'N')
            ];
        } elseif($branchId == 2){
            // China
            return [
                'news'              => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'publish'           => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'topic'             => array('Y', 'Y', 'Y', 'Y', 'Y')
                //'conference'        => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'store'             => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'newsUpdate'        => array('N', 'N', 'N', 'N', 'Y'),
                //'calendarIndicator' => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'calendarItem'      => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'calendarUpload'    => array('N', 'Y', 'N', 'N', 'Y'),
                //'dailyNews'         => array('Y', 'N', 'N', 'N', 'Y'),
                //'announcement'      => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'report'            => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'proAnnouncement'   => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'client'            => array('Y', 'N', 'N', 'N', 'N')
            ];
        } elseif($branchId == 3){
            // TP KR
            return [
                'news'              => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'publish'           => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'topic'             => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'conference'        => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'translate'         => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'board'             => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'comment'           => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'timeline'          => array('Y', 'Y', 'Y', 'Y', 'Y'),
                'wiki'              => array('Y', 'Y', 'Y', 'Y', 'Y'),
                //'store'             => array('Y', 'Y', 'Y', 'Y', 'Y'),
            /*,
            'newsUpdate'        => array('N', 'N', 'N', 'N', 'Y'),
            'calendarIndicator' => array('Y', 'Y', 'Y', 'Y', 'Y'),
            'calendarItem'      => array('Y', 'Y', 'Y', 'Y', 'Y'),
            'calendarUpload'    => array('N', 'Y', 'N', 'N', 'Y'),
            'dailyNews'         => array('Y', 'N', 'N', 'N', 'Y'),
            'announcement'      => array('Y', 'Y', 'Y', 'Y', 'Y'),
            'report'            => array('Y', 'Y', 'Y', 'Y', 'Y'),
            'proAnnouncement'   => array('Y', 'Y', 'Y', 'Y', 'Y'),*/
            'client'            => array('Y', 'N', 'N', 'N', 'N'),
            'recruit'           => array('Y', 'N', 'N', 'N', 'Y'),
            'event'           => array('Y', 'N', 'N', 'N', 'Y')
            ];
        }
    }

    /**
     * @param $url  값을 던질 url
     * @param int $is_post   post 통신이면 1, get 이면0
     * @param array $data    전달할 값
     * @param null $custom_header    header를 같이 전송할경우
     * @return mixed
     */
    public static function request_curl($url, $is_post=0, $data=array(), $custom_header=null) {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_SSLVERSION,1);
        curl_setopt ($ch, CURLOPT_POST, $is_post);
        if($is_post) {
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt ($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt ($ch, CURLOPT_HEADER, true);

        if($custom_header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_header);
        }
        $result[0] = curl_exec ($ch);
        $result[1] = curl_errno($ch);
        $result[2] = curl_error($ch);
        $result[3] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);
        return $result;
    }


    public static final function decimalMath(String $var1, String $condition, String $var2, $scale = null){
        $debug = false;

        if($debug == true){
            echo "var1 : " . $var1 . "<br>" . PHP_EOL;
            echo "var2 : " . $var2 . "<br>" . PHP_EOL;
        }

        $decimalCount = 0;
        $result = false;
        if(is_numeric($var1) == false || is_numeric($var2) == false) return false;
        switch($condition){
            case '+':
            case '-':
            case '*':
            case '/':
            case '^':
                // 정상
                break;
            default:
                return false;
                break;
        }
        if($condition == '/' && $var2 == 0) return 0;

        /// 소수가있을경우 length count
        if(strpos($var1, '.') !== false){
            // 소수가있다.
            $decimalCount++;
            $tmp1 = explode('.', $var1);
            $var1Number = strlen($tmp1[0]);
            $var1Decimal = strlen((string)$tmp1[1]);
        } else {
            $var1Number = strlen($var1);
            $var1Decimal = 0;
        }

        if(strpos($var2, '.') !== false){
            // 소수가있다.
            $decimalCount++;
            $tmp2 = explode('.', $var2);
            $var2Number = strlen($tmp2[0]);
            $var2Decimal = strlen($tmp2[1]);
        } else {
            $var2Number = strlen($var2);
            $var2Decimal = 0;
        }

        // 각각 자리수 계산.
        $maxDecimal = max($var1Decimal,$var2Decimal);
        $maxNumber = max($var1Number,$var2Number);

        if($debug == true){
            echo "var1Decimal : " . $var1Decimal . "<br>" . PHP_EOL;
            echo "var2Decimal : " . $var2Decimal . "<br>" . PHP_EOL;
            echo "maxDecimal : " . $maxDecimal . "<br>" . PHP_EOL;
            echo "var1Number : " . $var1Number . "<br>" . PHP_EOL;
            echo "var2Number : " . $var2Number . "<br>" . PHP_EOL;
            echo "maxNumber : " . $maxNumber . "<p>&nbsp;<br>" . PHP_EOL;
        }

        if($maxDecimal == 0){
            // 그냥 계산.
        } else {
            // 소수점을 빼고 맞춰준다.
            if(strpos($var1, '.') !== false){
                // 소수가있다.
                $tmp1 = explode('.', $var1);
                $var1 = $tmp1[0].str_pad($tmp1[1],$maxDecimal,0, STR_PAD_RIGHT);
            } else {
                $var1 = str_pad($var1,strlen($var1) + $maxDecimal,0, STR_PAD_RIGHT);
            }
            if(strpos($var2, '.') !== false){
                // 소수가있다.
                $tmp2 = explode('.', $var2);
                $var2 = $tmp2[0].str_pad($tmp2[1],$maxDecimal,0, STR_PAD_RIGHT);
            } else {
                $var2 = str_pad($var2,strlen($var2) + $maxDecimal,0, STR_PAD_RIGHT);
            }
        }
        $var1 = (int)$var1;
        $var2 = (int)$var2;

        switch($condition){
            case '+':
                $result = $var1 + $var2;
                break;
            case '-':
                $result = $var1 - $var2;
                break;
            case '*':
                $result = $var1 * $var2;
                break;
            case '/':
                $result = $var1 / $var2;
                break;
            case '^':
                $result = pow($var1, $var2);
                break;
        }

        $resultSign = '';
        if($result < 0){
            $resultSign = '-';
            $result = substr($result, 1);
        }

        if($debug == true){
            echo "result : " . $result . "<br>" . PHP_EOL;
            echo "resultSign : " . $resultSign . "<p>&nbsp;<br>" . PHP_EOL;
        }

        if(strlen($result) < max(strlen($var1), strlen($var2))) $result = str_pad($result,max(strlen($var1), strlen($var2)),0, STR_PAD_LEFT);

        $result = trim($result . ' ');

        if($condition == '/'){
            if($scale !== null){
                $result = number_format($result,$scale);
            } else {
                if(strpos($result, '.') !== false){
                    if($maxDecimal == 0){
                        // check max decimal
                        $tmp3 = explode('.', $result);
                        if(strlen($tmp3[1]) < 2){
                            return $result;
                        } else {
                            $result = number_format($result, 2);
                        }
                    } else {
                        $result = number_format($result,$maxDecimal);
                    }
                } else {
                    return $result;
                }
            }
        } else {
            if($scale === null) $scale = $maxDecimal;
            if($maxDecimal >= 1){
                $result = str_pad($result,$maxNumber+$maxDecimal,0, STR_PAD_LEFT);
                switch($condition){
                    case '+':
                        $result = substr($result, 0, strlen($result) - $maxDecimal) . '.' . substr($result, strlen($result) - $maxDecimal);
                        break;
                    case '-':
                        $result = substr($result, 0, strlen($result) - $maxDecimal) . '.' . substr($result, strlen($result) - $maxDecimal);
                        break;
                    case '*':
                        $result = substr($result, 0, strlen($result) - ($maxDecimal * 2)) . '.' . substr($result, strlen($result) - ($maxDecimal * 2));
                        //$result = substr($result, 0, strlen($result) - ($maxDecimal * 2)) . '.' . substr($result, strlen($result) - ($maxDecimal * 2));
                        break;
                    case '^':
                        //??
                        break;
                }

                if(strpos($result, '.') !== false){
                    // 소수가있다.
                    $tmp3 = explode('.', $result);
                    if($tmp3[0] >= 1){
                        $result = intval($tmp3[0]) . '.' . $tmp3[1];
                    } else {
                        $result = '0.' . $tmp3[1];
                    }
                }
                while(true){
                    if(substr($result, -1) == '.'){
                        $result = substr($result, 0, strlen($result)-1);
                        break;
                    }elseif(substr($result, -1) != 0){
                        break;
                    } else {
                        $result = substr($result, 0, strlen($result)-1);
                    }
                }


                if(strpos($result, '.') !== false){
                    // 소수가있다.
                    $tmp4 = explode('.', $result);
                    if(strlen($tmp4[1]) > $scale){
                        $result = intval($tmp4[0]) . '.' . substr($tmp4[1], 0, $scale);
                    } else {
                        $result = intval($tmp4[0]) . '.' . str_pad($tmp4[1],$scale,  0, STR_PAD_RIGHT);
                    }
                }
            }
        }


        return $resultSign.$result;
    }

    public static final function rgbToHex($rgb) {
        $rgb      = str_replace('(', '', $rgb);
        $rgb      = str_replace(')', '', $rgb);
        $rgbArray = explode(",",$rgb,3);
        return sprintf("#%02x%02x%02x", $rgbArray[0], $rgbArray[1], $rgbArray[2]);
    }

    public static final function hexToRgb($hex, $alpha = false, $result = 'css') {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        if( !($length == 6 || $length == 3) ) return '';
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
        if ( $alpha ) {
            $rgb['a'] = $alpha;
        }

        switch ($result){
            case 'array':
                return $rgb;
                break;
            case 'css':
            default:
                return implode(array_keys($rgb)) . '(' . implode(', ', $rgb) . ')';
                break;
        }
    }

    public static final function sanitizeHex($rgb, $default = 'ffffff')
    {
        if(self::isHex($rgb) == true) return str_replace('#', '', $rgb);
        return strtolower($default);
    }

    public static final function isHex($rgb) :bool
    {
        if (preg_match('/^#{0,1}[a-fA-F0-9]{6}$/', strtolower($rgb))) {
            return true;
        }
        return false;
    }



    public static final function isDate($date , $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    // is digit?
    public static final function isNumeric($var, $minimum = '1') : bool
    {
        if(is_numeric($var) !== true) return false;
        if(is_float($var) !== false) return false;
        if(strpos($var, '.') !== false) return false;

        if($minimum != 'x'){
            if($var < $minimum) return false;
        }
        return true;
    }

    public static final function isInteger($var, $minimum = '1') : bool
    {
        if($var == '' || is_numeric($var) == false) return false;
        if (preg_match("/^\d+$/", $var) != true)
        {
            return false;
        }
        if($minimum != 'x'){
            if($var < $minimum) return false;
        }
        return true;
    }

    public static final function isAlphanumeric($str, $allowCharacter = "") : bool
    {
        if(preg_match("/^[a-zA-Z0-9" . $allowCharacter . "]+$/u", $str)) return true;
        return false;
    }

    public static final function isAlphanumericLowercase($str, $allowCharacter = "") : bool
    {
        if(preg_match("/^[a-z0-9" . $allowCharacter . "]+$/u", $str)) return true;
        return false;
    }

    public static final function isAlphanumericKorean($str, $allowCharacter = "") : bool
    {
        if(preg_match("/^[a-zA-Z0-9가-힣" . $allowCharacter . "]+$/u", $str)) return true;
        return false;
    }

    public static function numberOfDecimals($value) {
        if ((int)$value == $value) {
            return 0;
        } else if (is_numeric($value) != true) {
            return -1;
        }
        return strlen($value) - strrpos($value, '.') - 1;
    }

    public static final function sanitizeNumeric($str) : int {
        $str = preg_replace("/[^0-9.]+/u", "", $str);
        return $str;
    }

    public static final function sanitizeDigit($str) : int {
        $str = preg_replace("/[^0-9]+/u", "", $str);
        return $str;
    }

    public static final function sanitizeAlphabet($str, $allowCharacter = " ") : string {
        $str = preg_replace("/[^a-zA-Z" . $allowCharacter . "]+/u", "", $str);
        return $str;
    }

    public static final function sanitizeAlphanumeric($str, $allowCharacter = " ") : string {
        $str = preg_replace("/[^a-zA-Z0-9" . $allowCharacter . "]+/u", "", $str);
        return $str;
    }

    public static final function sanitizeAlphanumericKorean($str, $allowCharacter = " ") : string {
        $str = preg_replace("/[^a-zA-Z가-힣0-9" . $allowCharacter . "]+/u", "", $str);
        return $str;
    }

    public static final function sanitizeKorean($str, $allowCharacter = " ") : string {
        $str = preg_replace("/[^가-힣" . $allowCharacter . "]+/u", "", $str);
        return $str;
    }

    public static function removeBom($data) {
        if (0 === strpos(bin2hex($data), 'efbbbf')) {
            return substr($data, 3);
        }
        return $data;
    }

    public static final function phoneNumberFormat($number) {
        $number = preg_replace("/[^0-9]/", "", $number);
        return preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $number);
    }

    public static function decimalFormat($number, $decimal = -1){
        $number = floatval($number);
        return self::numberFormat($number, $decimal);
    }

    public static function numberFormatNoComma($number, $decimal = -1){
        return str_replace(',', '', self::numberFormat($number, $decimal));
    }

    public static function numberFormatNoComma2($number){
        return str_replace(',', '', number_format($number));
    }

    public static final function numberFormat($number, $decimal = -1){
        //if(is_numeric($number) == false) return $number;

        $exp = explode('.', $number);

        if(sizeof($exp) == 1){
            // 소수점 없다.
            return number_format($number);
        } else {
            if($decimal >= 1){
                return number_format($exp[0]) . '.' . substr($exp[1], 0, $decimal);
            }elseif($decimal == 0){
                return number_format($exp[0]);
            } else {
                return number_format($exp[0]) . '.' . $exp[1];
            }

        }
    }

    public static final function numberFormatSigned($number, $decimal = -1){
        if(is_numeric($number) == false) return $number;
        if($number > 0){
            $sign = '+';
        } elseif($number < 0) {
            $sign = '-';
        } else {
            $sign = '';
        }
        $exp = explode('.', $number);

        if(sizeof($exp) == 1){
            // 소수점 없다.
            $ret = number_format($number);
        } else {
            if($decimal >= 1){
                $ret = number_format($exp[0]) . '.' . substr($exp[1], 0, $decimal);
            }elseif($decimal == 0){
                $ret = number_format($exp[0]);
            } else {
                $ret = number_format($exp[0]) . '.' . $exp[1];
            }
        }
        return $sign . $ret;
    }

    public static final function validEmailFormat($email){
        // valid email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // invalid email address
            return false;
        } else {
            return true;
        }
    }


    public static final function generateRandomNumber($length = 1, $addedString = '') {
        $characters = '0123456789';
        if($addedString != '') $characters .= $addedString;
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static final function generateRandomString($length = 1, $addedString = '') {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if($addedString != '') $characters .= $addedString;
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static final function generateRandomAlphanumeric($length = 1, $addedString = '') {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if($addedString != '') $characters .= $addedString;
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static final function generateRandomAlphanumericLowercase($length = 1, $addedString = '') {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        if($addedString != '') $characters .= $addedString;
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static final function generateRandomEasyAlphanumericLowercase($length = 1, $addedString = '') {
        $characters = '23456789abcdefghjknprstuvwxyz';
        if($addedString != '') $characters .= $addedString;
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static final function generateRandomStringCustom($length = 1, $characters = '0123456789abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ') {
        //$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param $string
     * @param $patten
     * @param $length
     * @return mixed
     * String 중간에 패턴을 넣는다.
     * 4글자당 공백인경우
     * IN : 123456789012, ' ', 4
     * OUT : 1234 5678 9012
     */
    public static final function strInsertPattern($string, $patten, $length)
    {
        $preg = "/[^ \n<>]{".$length."}/i";
        $string = preg_replace($preg , "\\0$patten", $string);
        // 마지막 글자가 패턴과 동일하면 삭제한다.
        if(substr($string, -1) == $patten){
            $string = substr($string, 0, -1);
        }
        return $string;
        //"\\0\n",
    }



    public static final function UTF2UCS($str, $s) {
        $str = strtolower($str);
        $char = 'UTF-8';
        $arr = array();
        $out = "";
        $c = mb_strlen($str,$char);
        $t = false;

        for($i =0;$i<$c;$i++){
            $arr[]=mb_substr($str,$i,1,$char);
        }

        foreach($arr as $i=>$v){
            if(preg_match('/\w/i',$v,$match)){
                $out .= $v;
                $t = true;
            }else{
                if($t) $out .= " ";
                if(isset($s) && $s) $out .= "+";
                $out .= bin2hex(iconv("UTF-8","UCS-2",$v))." ";
                $t = false;
            }
        }
        return $out;
    }

    public static final function aes128Encrypt($key, $data) {
        if(16 !== strlen($key)) $key = hash('MD5', $key, true);
        $padding = 16 - (strlen($data) % 16);
        $data .= str_repeat(chr($padding), $padding);
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, str_repeat("\0", 16)));
    }

    public static final function aes128Decrypt($key, $data) {
        $data = base64_decode($data);
        if(16 !== strlen($key)) $key = hash('MD5', $key, true);
        $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, str_repeat("\0", 16));
        $padding = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padding);
    }

    public static final function entityDecode($text){
        return html_entity_decode(html_entity_decode($text));
    }

    public static function getTxUrl($txId){
        if(APPLICATION_ENV == 'production'){
            // 메인넷
            $url = 'https://www.eosx.io/tx/';
        } else {
            // 테스트넷
            $url = 'https://kylin.eosx.io/tx/';
        }
        $url .= trim($txId);
        return $url;
    }

    public static final function getTitleUrl($title, $id = 0, $type = 0,Article $articleInstance = null){
        $url = Util::delBar(str_replace(' ', '-', str_replace('/', '-', Util::delHtml($title, 'title'))));
        //$url = '/' . preg_replace("/[^-A-Za-z0-9 ]/", '', strtolower($url));
        $url = '/' . preg_replace("/[^-A-Za-z0-9]/", '', $url);
        if(strlen($url) <= 1){
            $url = '/news/article/' . $id;
        } else {
            $url = '/news/article' . $url . '-' . $id;
        }
        return $url;
    }

    public static final function goLogin($rdUrl = '/') {
        die('<script type="text/javascript">location.replace("/login?rdUrl=' . $rdUrl . ');</script>');
    }

    public static final function getCookie($cookie, $cookieName) {
        if(isset($cookie[$cookieName]) === true) return $cookie[$cookieName];
        return '';
    }

    public static final function alert($msg, $url = '/') {
        die("<script>alert('" . $msg . "');location.replace('" . $url . "')</script>");
    }

    public static final function redirect($url = '/') {
        die("<script>location.replace('" . $url . "')</script>");
    }

    public static final function dbAddslashes($string){
        return addslashes(trim($string));
    }

    public static final function str_limit($string, $limit_length, $add_string){
        return mb_strimwidth($string, 0, $limit_length, $add_string);
    }

    public static final function getLocalTime($type = 'Y-m-d H:i:s'){
        return date($type, time());
    }

    public static final function getElapsedTime($date){

        $textArray = array();
        $textArray['en']['secs']   = 'secs ago';
        $textArray['ko']['secs']   = '초 전';
        $textArray['en']['mins']   = 'mins ago';
        $textArray['ko']['mins']   = '분 전';
        $textArray['en']['hours']  = 'hours ago';
        $textArray['ko']['hours']  = '시간 전';
        $textArray['en']['days']   = 'days ago';
        $textArray['ko']['days']   = '일 전';
        $textArray['en']['months'] = 'months ago';
        $textArray['ko']['months'] = '달 전';
        $textArray['en']['years']  = 'years ago';
        $textArray['ko']['years']  = '년 전';

        $localTime = time();
        $date1 = strtotime($date);
        $interval = ceil($localTime - $date1);

        if($interval <= 60) {
            return $interval . $textArray[SITE_LANGUAGE_CODE]['secs'];
        } else if($interval <= 60 * 60){
            return floor($interval / 60 ) . $textArray[SITE_LANGUAGE_CODE]['mins'];
        } else if($interval <= 60 * 60 * 24) {
            return floor($interval / (60 * 60)) . $textArray[SITE_LANGUAGE_CODE]['hours'];
        } else if($interval <= 60 * 60 * 24 * 30) {
            return floor($interval / (60 * 60 * 24)) . $textArray[SITE_LANGUAGE_CODE]['days'];
        } else if($interval <= 60 * 60 * 24 * 365) {
            return floor($interval / (60 * 60 * 24 * 30)) . $textArray[SITE_LANGUAGE_CODE]['months'];
        } else {
            return floor($interval / (60 * 60 * 24 * 365)) . $textArray[SITE_LANGUAGE_CODE]['years'];
        }
    }

    public static final function getDbTimestamp(){
        return new Db\RawValue('NOW()');
    }

    public static final function getDbNow($type = 'Y-m-d H:i:s', $mode = null){
        /**
         * 사이트에서 입력되는 모든 now를 관리.
         * UTC를 기본으로 하며 서버 local로 전환할 경우 type를 지정해준다.
         * 시간을 정하고 DB로 보내기 때문에 delay가 있을수 있다.
         */
        //$mode = 'local';
        $currentTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        if($mode == 'local'){
            date_default_timezone_set($currentTimezone);
            $return = date($type, time());
        }else if($mode == 'db'){
            $return = new Db\RawValue('NOW()');
        }else if($mode == 'est'){
            date_default_timezone_set('America/New_York');
            $return = date($type, time());
            //date_default_timezone_set('UTC');
        }else if($mode == 'kst'){
            date_default_timezone_set('Asia/Seoul');
            $return = date($type, time());
            //date_default_timezone_set('UTC');
        } else {
            $return = date($type, time());
        }

        date_default_timezone_set($currentTimezone);
        return $return;
    }

    public static final function getDateDOW($date, $languageCode = 'en'){
        $week['en'] = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
        $week['enFull'] = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
        $week['ko'] = array("일", "월", "화", "수", "목", "금", "토");
        $week['cn'] = array("日", "月", "火", "水", "木", "金", "土");
        $week['zh'] = array("日", "月", "火", "水", "木", "金", "土");
        $week['ja'] = array("日", "月", "火", "水", "木", "金", "土");


        if($date == '') return '';

        switch ($languageCode){
            case 'koFull':
                return $week['ko'][date('w', strtotime($date))] . '요일';
            default:
                return $week[$languageCode][date('w', strtotime($date))];
        }
    }


    public static final function getStandardOffsetUTC($timezone) {
        if($timezone == 'UTC') {
            return '+00:00';
        } else {
            $timezone = new \DateTimeZone($timezone);
            $transitions = array_slice($timezone->getTransitions(), -3, null, true);

            foreach (array_reverse($transitions, true) as $transition)
            {
                if ($transition['isdst'] == 1)
                {
                    continue;
                }

                //return sprintf('UTC %+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
                return sprintf('%+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
            }

            return false;
        }
    }

    public static final function getDate($now = '',$calDate = '-0 day', $format = 'Y-m-d H:i:s'){
        if($now == '' || $now == null) $now = Util::getDbNow();
        return date($format, strtotime($calDate, strtotime($now)));
    }

    public static final function convertDateFormat($date, $in = 'n/j/Y', $out = 'Y-m-d'){
        if($date == '0000-00-00') return '0000-00-00';
        return date_format(date_create_from_format($in , $date), $out);
    }

    public static final function convertTimezone($now = '', $zone = 'UTC', $format = 'Y-m-d H:i:s'){
        if(is_numeric($now) == true){
            $now = date('Y-m-d H:i:s', $now);
        }
        $dateTime = new \DateTime($now);
        $dateTime->setTimeZone(new \DateTimeZone($zone));
        return $dateTime->format($format);
    }

    public static final function convert2Timezone($now = '', $zone1 = 'UTC', $zone2 = 'America/New_York', $format = 'Y-m-d H:i:s'){
        $currentTimezone = date_default_timezone_get();
        date_default_timezone_set($zone1);
        if(is_numeric($now) == true){
            $now = date('Y-m-d H:i:s', $now);
        }

        $dateTime = new \DateTime($now);
        $dateTime->setTimeZone(new \DateTimeZone($zone2));
        date_default_timezone_set($currentTimezone);
        return $dateTime->format($format);
    }

    public static final function get2DateDiff($date1, $date2, $abs = 'Y', $format = 'd', $int = 'ceil'){
        $format = strtolower($format);
        $diff = strtotime($date2) - strtotime($date1);
        if($abs == 'Y') $diff = abs($diff);

        switch($format) {
            case 'h:m':
                if($int == 'ceil') return ceil($diff/3600) . ':' . ceil($diff%3600);
                return floor($diff/3600) . ':' . floor($diff%3600);
                break;
            case 'd':
                if($int == 'ceil') return ceil($diff/3600/24);
                return floor($diff/3600/24);
                break;
            case 'h':
                if($int == 'ceil') return ceil($diff/3600);
                return floor($diff/3600);
                break;
            case 'm':
                if($int == 'ceil') return ceil($diff/60);
                return floor($diff/60);
                break;
            case 's':
                return $diff;
                break;
        }
        return $diff;
    }

    public static final function getDateDiff($date, $abs = 'Y', $format = 'd', $int = 'ceil'){
        // abs Y 일 경우에는 과거는 - 당일은 0(-0)  미래는 +로 나온다.
        $format = strtolower($format);
        $diff = strtotime($date) - strtotime(Util::getDbNow());

        if($abs == 'Y') $diff = abs($diff);

        switch($format) {
            case 'h:m':
                if($int == 'ceil') return ceil($diff/3600) . ':' . ceil($diff%3600);
                return floor($diff/3600) . ':' . floor($diff%3600);
                break;
            case 'd':
                if($int == 'ceil') return ceil($diff/3600/24);
                return floor($diff/3600/24);
                break;
            case 'h':
                if($int == 'ceil') return ceil($diff/3600);
                return floor($diff/3600);
                break;
            case 'm':
                if($int == 'ceil') return ceil($diff/60);
                return floor($diff/60);
                break;
            case 's':
                return $diff;
                break;
        }
        return $diff;
    }

    public static final function dateDiffKr($date){
        $diff = Util::getDateDiff($date,'Y','d','floor');

//        if($diff == 0) {
//            return "오늘 ". Util::getDateDOW($date, 'koFull'). " " . date_format(new DateTime($date), 'Y년 m월 d일');
//        } else if($diff == 1) {
//            return "어제 ". Util::getDateDOW($date, 'koFull'). " " . date_format(new DateTime($date), 'Y년 m월 d일');
//        } else {
//            return Util::getDateDOW($date, 'koFull'). " " . date_format(new DateTime($date), 'Y년 m월 d일');
//        }

        if($diff == 0) {
            return "오늘 ";
        } else if($diff == 1) {
            return "어제 ";
        } else {
            return "";
        }
    }

    public static final function calcDuration($entryDate, $entryTime, $exitDate, $exitTime) {
        if($entryDate == '' || $exitDate == '') return '';

        if($entryTime != '' && $exitTime != ''){
            $entry = $entryDate . ' ' . $entryTime;
            $exit = $exitDate . ' ' . $entryTime;
        } else {
            $entry = $entryDate . ' 00:00:00';
            $exit = $exitDate . ' 00:00:00';
        }
        return Util::getDateDiff2Date($exit, $entry, 'N', 'auto');
    }

    public static final function getDateDiff2Date($date1, $date2, $abs = 'Y', $format = 'd', $int = 'ceil'){
        $format = strtolower($format);
        $diff = strtotime($date2) - strtotime($date1);
        if($abs == 'Y') $diff = abs($diff);

        switch($format) {
            case 'h:m':
                if($int == 'ceil') return ceil($diff/3600) . ':' . ceil($diff%3600);
                if($int == 'floor') return floor($diff/3600) . ':' . floor($diff%3600);
                if($int == 'round') return round($diff/3600) . ':' . round($diff%3600);
                return floor($diff/3600) . ':' . floor($diff%3600);
                break;
            case 'd':
                if($int == 'ceil') return ceil($diff/3600/24);
                if($int == 'floor') return floor($diff/3600/24);
                if($int == 'round') return round($diff/3600/24);
                return floor($diff/3600/24);
                break;
            case 'h':
                if($int == 'ceil') return ceil($diff/3600);
                if($int == 'floor') return floor($diff/3600);
                if($int == 'round') return round($diff/3600);
                return floor($diff/3600);
                break;
            case 'm':
                if($int == 'ceil') return ceil($diff/60);
                if($int == 'floor') return floor($diff/60);
                if($int == 'round') return round($diff/60);
                return floor($diff/60);
                break;
            case 's':
                return $diff;
                break;
            case 'auto':
                $ret = '';
                //if($diff >= (3600 * 24) ){
                if(floor(($diff / (60 * 60 * 24)) % 365) >= 1) $ret .= floor(($diff / (60 * 60 * 24)) % 365) . 'D ';

                if(floor(($diff / ( 60 * 60 )) % 24) >= 1 ) $ret .= floor(($diff / ( 60 * 60 )) % 24) . 'H ';

                if(floor(($diff / 60) % 60) >= 1) $ret .= floor(($diff / 60) % 60) . 'm ';

                if(floor($diff % 60) >= 1) $ret .= floor($diff % 60) . 's';
                return trim($ret);
                break;
        }
        return $diff;
    }

    public static final function trim($str){
        return preg_replace('/\s{2,}+/', ' ', trim($str));
    }

    public static final function delLine($str) {
        $str = str_replace("&nbsp;", "", $str);
        $str = str_replace("\n", "", $str);
        return trim($str);
    }

    public static final function getFirstLine($str) {

        preg_match('/^(.+)([<br>|<br\/>|<br \/>|<\/p>]+)/', $str, $result);
        var_dump($result);
        var_dump(strip_tags($result[0]));
        var_dump($str);
        exit;
        return ($str);
    }

    public static final function checkAmpQuotesAngleBracket($str) : Bool
    {
        if(self::checkQuotes($str) != true) return false;
        if(strpos($str, "&") != false) return false;
        if(strpos($str, "<") != false) return false;
        if(strpos($str, ">") != false) return false;
        return true;
    }

    public static final function checkQuotes($str) : Bool
    {
        if(strpos($str, "'") != false) return false;
        if(strpos($str, '"') != false) return false;
        if(strpos($str, "’") != false) return false;
        if(strpos($str, "`") != false) return false;
        return true;
    }

    public static final function delBar($str){
        $str = trim(preg_replace("/(-){2,}/",'-', $str));
        return trim(preg_replace("/( ){2}/",' ', $str));
    }

    public static final function delSpecialCharacter($str){
        return trim(preg_replace("/&(.){2,5};/",'', $str));
    }

    public static final function delQuot($str) {
        $str = str_replace('"', "", $str);
        return trim($str);
    }

    public static final function delComma($str) {
        $str = str_replace("’", "", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace('"', "", $str);
        $str = str_replace("‘", "", $str);
        return trim($str);
    }


    public static final function delHtml($str, $type = '') {
        $str = Util::delSpecialCharacter($str);
        $str = str_replace("’", "", $str);
        $str = str_replace("‘", "", $str);
        $str = str_replace("&", "", $str);
        $str = str_replace("#", "", $str);
        $str = str_replace("{", "", $str);
        $str = str_replace("}", "", $str);
        $str = str_replace("<", "", $str);
        $str = str_replace(">", "", $str);
        $str = str_replace("?", "", $str);
        if($type != 'mail') $str = str_replace("@", "", $str);
        $str = str_replace("%", "", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace('"', "", $str);
        $str = str_replace('`', "", $str);
        //$str = str_replace('“', "", $str);
        //$str = str_replace('”', "", $str);
        $str = str_replace(':', "", $str);
        $str = str_replace(';', "", $str);
        if($type == 'title'){
            $str = str_replace("“", "", $str);
            $str = str_replace("”", "", $str);
            $str = str_replace('(', "", $str);
            $str = str_replace(')', "", $str);
            $str = str_replace(',', "", $str);
            $str = str_replace('.', "", $str);
        }
        return trim($str);
    }

    public static final function changeMetaText($str, $mode = 'all') {
        // meta tag용
        $str = htmlspecialchars($str);
        //$str = str_replace('"', "''", $str);
        /*
        $str = str_replace("’", "", $str);
        $str = str_replace("&", "&amp;", $str);
        $str = str_replace("{", "&#123;", $str);
        $str = str_replace("}", "&#125;", $str);
        $str = str_replace("<", "&lt;", $str);
        $str = str_replace(">", "&gt;", $str);
        $str = str_replace("?", "&#63;", $str);
        //if($mode != 'email') $str = str_replace("@", "&#64;", $str);
        $str = str_replace("'", "&#39;", $str);
        $str = str_replace('`', "&#96;", $str);*/
        return $str;
    }


    public static final function changeQuotArrow($str) {
        return self::changeArrow(self::changeQuot($str));
    }

    public static final function changeQuot($str) {
        $str = str_replace("'", "&#39;", $str);
        $str = str_replace('"', "&quot;", $str);
        return $str;
    }

    public static final function changeArrow($str) {
        $str = trim($str);
        $str = str_replace("<", "&lt;", $str);
        $str = str_replace(">", "&gt;", $str);
        return $str;
    }

    public static final function changeHtml($str, $mode = 'all') {
        $str = str_replace("’", "", $str);
        $str = str_replace("&", "&amp;", $str);
        $str = str_replace("{", "&#123;", $str);
        $str = str_replace("}", "&#125;", $str);
        $str = str_replace("<", "&lt;", $str);
        $str = str_replace(">", "&gt;", $str);
        $str = str_replace("?", "&#63;", $str);
        //if($mode != 'email') $str = str_replace("@", "&#64;", $str);
        $str = str_replace("'", "&#39;", $str);
        $str = str_replace('"', "&quot;", $str);
        $str = str_replace('`', "&#96;", $str);
        return $str;
    }

    public static final function decodeHtml($str, $mode = 'all') {
        $str = str_replace("&amp;", "&", $str);
        $str = str_replace("&#123;","{",  $str);
        $str = str_replace("&#125;","}",  $str);
        $str = str_replace("&lt;","<",  $str);
        $str = str_replace("&gt;",">",  $str);
        $str = str_replace("&#63;","?",  $str);
        //if($mode != 'email') $str = str_replace("@", "&#64;", $str);
        $str = str_replace("&#39;", "'", $str);
        $str = str_replace("&quot;", '"', $str);
        $str = str_replace("&#96;", '`', $str);
        return $str;
    }

    public static final function getUrl() {
        $url = $_SERVER['REQUEST_URI'];
        $url = explode('?', $url);
        if (sizeof($url) == 2) {
            $url = '?' . $url[1];
        } else {
            $url = null;
        }
        return $url;
    }

    public static final function getDomain($format = '') {
        $host = HTTP_HOST;
        if($format == 'all') return $host;
        $host = explode('.', $host);
        if($format == 'array') return $host;

        $domain = $host[sizeof($host)-2] . '.' . $host[sizeof($host)-1];
        //$host2 = substr(strchr(strtolower(getenv('SERVER_NAME')),'.'),1);

        return $domain;
    }

    public static final function getUrlToString($url) {
        /* Paging등을 위한 조건검색 url화 */
        if (sizeof($url) > 0) {
            $url = '?' . join('&', $url);
        } else {
            $url = '';
        }
        return $url;
    }

    public static final function objToArray($items, $key){
        $ret = array();
        $key = explode(',',$key);

        if(sizeof($key) < 1) return false;
        foreach($items as $idx => $var){
            foreach($key as $k){
                $k = trim($k);
                if($k == 'language'){
                    $ret[$idx][$k] = trim($var->getNative());
                } else {
                    $ret[$idx][$k] = trim($var->getItemByKey($k));
                }
            }
            //if(in_array('time',$key) == false) $ret[$idx]['time'] = $var->getItemByKey('time');
            //if(in_array('date',$key) == false) $ret[$idx]['date'] = $var->getItemByKey('date');
        }
        return $ret;
    }

    public static final function arrayToArray($items, $key){
        $ret = array();
        $key = explode(',',$key);
        $idx = 0;

        if(sizeof($key) < 1) return false;
        foreach($items as $var){
            foreach($key as $k){
                $k = trim($k);
                $ret[$idx][$k] = trim($var[$k]);
            }
            $idx++;
        }
        return $ret;
    }

    public static final function modelToArray($items, $key, $mode = ''){
        $count = 0;
        $ret = array();
        $key = explode(',',$key);

        if(sizeof($key) < 1) return false;
        foreach($items as $var){
            foreach($key as $k){
                if($k == 'releaseDate'){
                    if($var->readAttribute($k) != ''){
                        $temp = Util::findNearlyDate($var->readAttribute($k), '', $var->readAttribute('originPeriodDate'));
                        $ret[$count][$k] = date('j-M-y',strtotime($temp['release']));
                    } else {
                        $ret[$count][$k] = '';
                    }
                } elseif($k == 'originPeriodDate'){
                    // 필요 없기때문에 통과.
                } elseif($k == 'statusName'){
                    $ret[$count][$k] = trim($var->getStatusName());
                } else {
                    $ret[$count][$k] = trim($var->getItemByKey($k));
                }
            }
            $count++;
            //if(in_array('time',$key) == false) $ret[$idx]['time'] = $var->getItemByKey('time');
            //if(in_array('date',$key) == false) $ret[$idx]['date'] = $var->getItemByKey('date');
        }
        /*
        if($mode == 'calendarPopup'){
            foreach($ret as $key => $var){

                if($var['releaseDate'] != ''){
                    $temp = Util::findNearlyDate($var['releaseDate'], '', $var['originPeriodDate']);
                    $ret[$key]['releaseDate'] = date('j-M-y',strtotime($temp['release']));
                }
            }
        }
        */
        return $ret;
    }

    public static final function getPaging($url, $page, $maxPage, $pageSize, $class = '') {

        if (strpos($url, "?") === false) {
            $url .= "?page=%d";
        } else {
            $url .= "&page=%d";
        }

        $startPage = 0;
        $endPage   = 0;

        $pageSizeStatus = '';
        $pageStatus = '';

        if(1 <= $page && $page <= 1 + floor($pageSize/2)){
            $pageStatus = 'start';
            $startPage = 1;
            $endPage   = $pageSize;
        } elseif (1 + floor($pageSize/2) + 1 <= $page && $page <= $maxPage - (floor($pageSize/2) + 1) ) {
            $pageStatus = 'middle';
            $startPage = $page - floor($pageSize/2);
            $endPage   = $page + floor($pageSize/2);
        } elseif ($maxPage - (floor($pageSize/2)) <= $page && $page <= $maxPage ) {
            $pageStatus = 'end';
            $startPage = $maxPage - $pageSize + 1;
            $endPage   = $maxPage;
        }

        if($pageSize >= $maxPage){
            $pageSizeStatus = 'under';
            $startPage = 1;
            $endPage   = $maxPage;
        } elseif ($pageSize < $maxPage) {
            $pageSizeStatus = 'over';
        }


        $startPageStr = '';
        $middlePageStr = '';
        $endPageStr = '';

        $startPageStr .= '<li><a href="' . sprintf($url, $page - 1) . '" class="st1"><img src="/assets/images/front/common/icon/linkBtnPrevious.png" /></a></li>&nbsp;';
        $startPageStr .= '<li><a href="' . sprintf($url, 1) . '"><span class="prev">1</span></a></li>&nbsp;...&nbsp;';

        for ($i = $startPage; $i <= $endPage; $i++) {
            if($i == $page){
                $middlePageStr .= '<li class="select"><a href="' . sprintf($url, $i) . '"><span class="scriptPaging on" data-page="' . $i . '" data-type="' . $class . '">' . $i . '</span></a></li>&nbsp;';
            } else {
                $middlePageStr .= '<li><a href="' . sprintf($url, $i) . '"><span class="scriptPaging" data-page="' . $i . '" data-type="' . $class . '">' . $i . '</span></a></li>&nbsp;';
            }
        }

        $endPageStr .= '... <li><a href="' . sprintf($url, $maxPage) . '"><span class="next">' . $maxPage. '</span></a></li>';
        $endPageStr .= '&nbsp;<li><a href="' . sprintf($url, $page + 1) . '" class="st2"><img src="/assets/images/front/common/icon/linkBtnNext.png" /></a></li>&nbsp;';

        $resultStr = "<ul>";

        if($pageSizeStatus == 'under'){
            $resultStr .= $middlePageStr;
        } else if($pageSizeStatus == 'over') {

            if($pageStatus == 'start'){

                $resultStr .= $middlePageStr . $endPageStr;
            } else if($pageStatus == 'middle') {

                $resultStr .= $startPageStr . $middlePageStr . $endPageStr;
            } else if($pageStatus == 'end') {

                $resultStr .= $startPageStr . $middlePageStr;
            }
        }
        $resultStr .= "</ul>";

        return $resultStr;
    }

    public static final function getMobliePaging($url, $page, $maxPage, $pageSize, $class = '') {

        if (strpos($url, "?") === false) {
            $url .= "?page=%d";
        } else {
            $url .= "&page=%d";
        }

        $pageStatus = '';

        if(1 == $page){
            $pageStatus = 'start';
        } elseif ($page == $maxPage) {
            $pageStatus = 'end';
        } else {
            $pageStatus = 'middle';
        }

        $startPageStr = '';
        $middlePageStr = '';
        $endPageStr = '';

        $startPageStr .= '<li><a href="' . sprintf($url, $page - 1) . '" class="st1"><img src="/assets/images/front/common/icon/linkBtnPrevious.png" /></a></li>';
        $middlePageStr .= '<li><span style="color: #5D5FEF;">'.$page.'</span> / <span>'.$maxPage.'</span></li>';
        $endPageStr .= '<li><a href="' . sprintf($url, $page + 1) . '" class="st2"><img src="/assets/images/front/common/icon/linkBtnNext.png" /></a></li>';

        $resultStr = "<ul>";


        if($pageStatus == 'start'){
            $resultStr .= $middlePageStr . $endPageStr;
        } else if($pageStatus == 'middle') {
            $resultStr .= $startPageStr . $middlePageStr . $endPageStr;
        } else if($pageStatus == 'end') {
            $resultStr .= $startPageStr . $middlePageStr;
        }

        $resultStr .= "</ul>";

        return $resultStr;
    }

    public static final function getRandomUserAgent(){
        $userAgents=array(
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
            "Opera/9.20 (Windows NT 6.0; U; en)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 8.50",
            "Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.1) Opera 7.02 [en]",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; fr; rv:1.7) Gecko/20040624 Firefox/0.9",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48"
        );
        $random = rand(0,count($userAgents)-1);

        return $userAgents[$random];
    }

    public static final function getBrowserName(){
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $isChrome = strpos($userAgent, 'Chrome');
        $isChromeMobile = strpos($userAgent, 'CriOS');
        $isSamsungBrowser = strpos($userAgent, 'SamsungBrowser');
        $isWindows = strpos($userAgent, 'Windows NT');
        $isFireFox = strpos($userAgent, 'Firefox');
        $isEdge = strpos($userAgent, 'Edge');
        $isIE = strpos($userAgent, 'Trident');

        if ($isEdge > 0) return 'edge';
        if ($isIE > 0) return 'ie';
        if ($isChromeMobile > 0) return 'chromeMobile';
        if ($isFireFox > 0) return 'fireFox';
//        if ($isWindows> 0) return 'windows NT';
        if ($isChrome > 0) return 'chrome';

        return '';

    }

    public static final function getClientIp() {
        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) === true && $_SERVER["HTTP_X_FORWARDED_FOR"] != '') return $_SERVER["HTTP_X_FORWARDED_FOR"];
        if(isset($_SERVER["HTTP_CF_CONNECTING_IP"]) === true && $_SERVER["HTTP_CF_CONNECTING_IP"] != '') return $_SERVER["HTTP_CF_CONNECTING_IP"];

        $ip = getenv('HTTP_X_REAL_IP');
        if ($ip == '') {
            $ip = getenv('REMOTE_ADDR');
        }

        return $ip;
    }


    public static final function brandNewImage($path, $randomFilename, $ext, $branchId = 1, $typeId = 1){
        /**
         * 공통으로 이미지 썸네일 생성
         */

        if(strpos($randomFilename, '_th')){
            // source가 썸네일이면 패스
            return;
        }

        foreach(self::getThumbnailSize($branchId, $typeId) as $var){
            self::generateImageThumbnail($path, $randomFilename.$ext, $randomFilename.'_th_' . $var[0] .'x' . $var[1] .$ext, $var[0], $var[1]);
        }
    }

    public static final function getThumbnailSize($branchId, $typeId){

        // 각 어레이에 코딩.(그냥 만드는 곳에 하고 돌리는게 더 보기 편하지 않을까...?)

        // const 쓰까..?
        // branchId = 1 . pms
        // branchId = 2 . 본다빈치
        // branchId = 3 . 빅이슈

        // typeId = 1 Article
        // typeId = 2 Directory
        // typeId = 3 Magazine

        // array 1차 키 branchId , 2차 키 typeId

        $data[1][1] =  array(
            /**
             * pms 1안 1280 web
             */
            [627,418], // index 메인기사
            [52,52],   // index 주요기사
            [300,200], // index 서브기사
            [300,200], // index 카테고리
            [300,200], // index 기획
            [186,124], // article list 썸네일

            /**
             * pms 1안 970 web
             */
            [635,418], // index 메인기사
            [52,52],   // index 주요기사
            [300,200], // index 서브기사
            [300,200], // index 카테고리
            [300,200], // index 기획
            [186,124], // article list 썸네일

            /**
             * pms 1안 mobile
             */

            [0,0], // index 메인기사
            [130,75],   // index 주요기사
            [130,75], // index 서브기사
            [130,75], // index 카테고리
            [130,75], // index 기획
            [186,124], // article list 썸네일


            /**
             * pms 2안 1280
             */


            /**
             * pms 2안 970
             */

            [635,425], // index 메인기사
            [75,75],   // index 주요기사
            [180,120], // index 서브기사
            [280,186], // index 카테고리
            [280,186], // index 기획
            [186,124], // article list 썸네일

            /**
             * pms 2안 mobile은 1안과 동일하므로 array 추가 안함.
             */



        );


//        $data[2][1] = array(

        /**
         * bdvc web
         */

        /**
         * bdvc mobile
         */
//        );

        $data[2][1] = $data[1][1];

//
//        // 통합일때. 추후 삭제.
//        $data[3][2] = array(
//            /**
//             * bis web
//             */
//
//            [140, 85] ,  // 카테고리 5종
//            [225, 290] , // 신간 (잡지표지) 근데 얘는 썸네일 읍는데...?
//            [240, 160] , // 빅이슈 판매원 list
//            [140, 100] , // 빅판의 이모저모 list 섬네일
//            [140, 100] , // 빅판의 이모저모 하단 섬네일
//            [132, 173] , // 신간과월호 list 근데 얘는 썸네일 읍는데...?
//            [306, 403] , // 신간과월호 view 표지
//            [150, 100] , // 신간과월호 view 하단 섬네일
//            [132, 173] , // 신간과월호 view 하단 표지
//            [150, 100] , // 신간과월호 기사 view 하단 섬네일
//            [150, 100] , // 파트너십 list 하단 섬네일
//            [150, 100] , // 파트너십 view 하단 섬네일
//
//            /**
//             * bis mobile
//             */
//
//            [100,  60] , // 카테고리 5종
//            [160, 230] , // 신간 (잡지표지) 근데 얘는 썸네일 읍는데...?
//            [100,  60] , // 빅이슈 판매원 list
//            [100,  60] , // 빅판의 이모저모 list 섬네일
//            [100,  60] , // 빅판의 이모저모 하단 섬네일
//            [132, 173] , // 신간과월호 list 근데 얘는 썸네일 읍는데...?
//            [190, 250] , // 신간과월호 view 표지
//            [100,  60] , // 신간과월호 view 하단 섬네일
//            [105, 138] , // 신간과월호 view 하단 표지
//            [100,  60] , // 신간과월호 기사 view 하단 섬네일
//            [100,  60] , // 파트너십 list 하단 섬네일
//            [100,  60] , // 파트너십 view 하단 섬네일
//
//        );

        $data[3][1] = array(
            /**
             * bis web article
             */

            [140, 85] ,  // 카테고리 5종
            [140, 100] , // 빅판의 이모저모 list 섬네일
            [140, 100] , // 빅판의 이모저모 하단 섬네일
            [150, 100] , // 신간과월호 기사 view 하단 섬네일
            [150, 100] , // 파트너십 list 하단 섬네일
            [150, 100] , // 파트너십 view 하단 섬네일

            /**
             * bis mobile article
             */

            [100,  60] , // 카테고리 5종
            [100,  60] , // 빅판의 이모저모 list 섬네일
            [100,  60] , // 빅판의 이모저모 하단 섬네일
            [100,  60] , // 신간과월호 view 하단 섬네일
            [100,  60] , // 신간과월호 기사 view 하단 섬네일
            [100,  60] , // 파트너십 list 하단 섬네일
            [100,  60] , // 파트너십 view 하단 섬네일

        );

        $data[3][2] = array(
            /**
             * bis web directory
             */

            [240, 160] , // 빅이슈 판매원 list

            /**
             * bis mobile directory
             */

            [100,  60] , // 빅이슈 판매원 list

        );


        $data[3][3] = array(
            /**
             * bis web magazine
             */

            [225, 290] , // 신간 (잡지표지) 근데 얘는 썸네일 읍는데...?
            [132, 173] , // 신간과월호 list 근데 얘는 썸네일 읍는데...?
            [306, 403] , // 신간과월호 view 표지
            [150, 100] , // 신간과월호 view 하단 섬네일
            [132, 173] , // 신간과월호 view 하단 표지
            [150, 100] , // 신간과월호 기사 view 하단 섬네일

            /**
             * bis mobile magazine
             */

            [160, 230] , // 신간 (잡지표지) 근데 얘는 썸네일 읍는데...?
            [132, 173] , // 신간과월호 list 근데 얘는 썸네일 읍는데...?
            [190, 250] , // 신간과월호 view 표지
            [100,  60] , // 신간과월호 view 하단 섬네일
            [105, 138] , // 신간과월호 view 하단 표지
            [100,  60] , // 신간과월호 기사 view 하단 섬네일

        );





        // branch와 type에 해당하는 어레이가 없다면 그냥 공통으로 쓴다고 치고 1,1로
        if (array_key_exists($branchId, $data) == false || array_key_exists($branchId, $data[$branchId]) == false){
            return $data[1][1];
        } else {
            return $data[$branchId][$typeId];
        }
    }

    public static final function generateImageThumbnail($path, $sourceImageName, $thumbnailImageName, $maxWidth, $maxHeight){
        if(Util::isExist($path, $thumbnailImageName) == true){
            // 파일이 이미 있을경우 패스
            return true;
        }

        if(Util::isExist($path, $sourceImageName) != true){
            // 기본 파일이 없을경우 패스
            return false;
        }

        $sourceGdImage = false;
        list($sourceImageWidth, $sourceImageHeight, $sourceImageType) = getimagesize($path.$sourceImageName);
        switch ($sourceImageType) {
            case IMAGETYPE_BMP:
                $sourceGdImage = self::imageCreateFromBmp($path.$sourceImageName);
                break;
            case IMAGETYPE_GIF:
                $sourceGdImage = imagecreatefromgif($path.$sourceImageName);
                break;
            case IMAGETYPE_JPEG:
                $sourceGdImage = imagecreatefromjpeg($path.$sourceImageName);
                break;
            case IMAGETYPE_PNG:
                $sourceGdImage = imagecreatefrompng($path.$sourceImageName);
                break;
        }
        if ($sourceGdImage === false) {
            return false;
        }
        $sourceAspectRatio = $sourceImageWidth / $sourceImageHeight;

        if($maxHeight == 0){
            if ($sourceImageWidth <= $maxWidth) {
                $thumbnailImageWidth = $sourceImageWidth;
                $thumbnailImageHeight = $sourceImageHeight;
            } else {
                $thumbnailImageWidth = $maxWidth;
                $thumbnailImageHeight = (int) ($maxWidth / $sourceAspectRatio);
            }
        } else {
            $thumbnailAspectRatio = $maxWidth / $maxHeight;
            if ($sourceImageWidth <= $maxWidth && $sourceImageHeight <= $maxHeight) {
                $thumbnailImageWidth = $sourceImageWidth;
                $thumbnailImageHeight = $sourceImageHeight;
            } elseif ($thumbnailAspectRatio > $sourceAspectRatio) {
                $thumbnailImageWidth = (int) ($maxHeight * $sourceAspectRatio);
                $thumbnailImageHeight = $maxHeight;
            } else {
                $thumbnailImageWidth = $maxWidth;
                $thumbnailImageHeight = (int) ($maxWidth / $sourceAspectRatio);
            }
        }



        /*
        if($thumbnail_image_width != $max_width || $thumbnail_image_height != $max_height){
            // 비율이 다르므로 crop
            if($thumbnail_image_width < $max_width){
                // 가로가 짧다.
                $start_width = ($max_width - $thumbnail_image_width) / 2;
                $start_height = 0;
            } else {
                $start_width = 0;
                $start_height = ($max_height - $thumbnail_image_height) / 2;
            }
        } else {
            $start_width = 0;
            $start_height = 0;
        }
        */

        $thumbnailGdImage = imagecreatetruecolor($thumbnailImageWidth, $thumbnailImageHeight);

        imagecopyresampled($thumbnailGdImage, $sourceGdImage, 0, 0,  0, 0,$thumbnailImageWidth, $thumbnailImageHeight, $sourceImageWidth, $sourceImageHeight);
        imagejpeg($thumbnailGdImage, $path.$thumbnailImageName, 100);


        //$thumbnail_gd_image2 = imagecreatetruecolor($max_width, $max_height);
        //$white = imagecolorallocate($thumbnail_gd_image2, 255, 255, 255);
        //imagefill($thumbnail_gd_image2, 0, 0, $white);

        //imagecopyresampled($thumbnail_gd_image2, $thumbnail_gd_image, $start_width, $start_height, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $thumbnail_image_width, $thumbnail_image_height);
        //imagejpeg($thumbnail_gd_image, $path.$thumbnail_image_name2, 100);

        //imagejpeg($thumbnail_gd_image, $path.$thumbnail_image_name, 90);
        imagedestroy($sourceGdImage);
        imagedestroy($thumbnailGdImage);
        //imagedestroy($thumbnail_gd_image2);
        return true;
    }

    /**
     * 파일이 있을때 true가 return 됨을 한번더 확인.
     * @param string $path
     * @param $fileName
     * @return bool
     */
    public static final function isExist($path = '/var/www/uploads/' . SITE_BRANCH_CODE . '/', $fileName){
        if(file_exists($path . $fileName)){
            return true;
        }
        return false;
    }

    public static final function imageCreateFromBmp($p_sFile){
        //    Load the image into a string
        $file    =    fopen($p_sFile,"rb");
        $read    =    fread($file,10);
        while(!feof($file)&&($read<>""))
            $read    .=    fread($file,1024);

        $temp    =    unpack("H*",$read);
        $hex    =    $temp[1];
        $header    =    substr($hex,0,108);

        //    Process the header
        //    Structure: http://www.fastgraph.com/help/bmp_header_format.html
        if (substr($header,0,4)=="424d")
        {
            //    Cut it in parts of 2 bytes
            $header_parts    =    str_split($header,2);

            //    Get the width        4 bytes
            $width            =    hexdec($header_parts[19].$header_parts[18]);

            //    Get the height        4 bytes
            $height            =    hexdec($header_parts[23].$header_parts[22]);

            //    Unset the header params
            unset($header_parts);
        }

        //    Define starting X and Y
        $x                =    0;
        $y                =    1;

        //    Create newimage
        $image            =    imagecreatetruecolor($width,$height);

        //    Grab the body from the image
        $body            =    substr($hex,108);

        //    Calculate if padding at the end-line is needed
        //    Divided by two to keep overview.
        //    1 byte = 2 HEX-chars
        $body_size        =    (strlen($body)/2);
        $header_size    =    ($width*$height);

        //    Use end-line padding? Only when needed
        $usePadding        =    ($body_size>($header_size*3)+4);

        //    Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption
        //    Calculate the next DWORD-position in the body
        for ($i=0;$i<$body_size;$i+=3)
        {
            //    Calculate line-ending and padding
            if ($x>=$width)
            {
                //    If padding needed, ignore image-padding
                //    Shift i to the ending of the current 32-bit-block
                if ($usePadding)
                    $i    +=    $width%4;

                //    Reset horizontal position
                $x    =    0;

                //    Raise the height-position (bottom-up)
                $y++;

                //    Reached the image-height? Break the for-loop
                if ($y>$height)
                    break;
            }

            //    Calculation of the RGB-pixel (defined as BGR in image-data)
            //    Define $i_pos as absolute position in the body
            $i_pos    =    $i*2;
            $r        =    hexdec($body[$i_pos+4].$body[$i_pos+5]);
            $g        =    hexdec($body[$i_pos+2].$body[$i_pos+3]);
            $b        =    hexdec($body[$i_pos].$body[$i_pos+1]);

            //    Calculate and draw the pixel
            $color    =    imagecolorallocate($image,$r,$g,$b);
            imagesetpixel($image,$x,$height-$y,$color);

            //    Raise the horizontal position
            $x++;
        }

        //    Unset the body / free the memory
        unset($body);

        //    Return image-object
        return $image;
    }

    public static final function intToAlphabet($i) { return chr( 65 + $i ); }


    public static final function sendSmsAction($clientId, $message = ''){

        $client = ClientContainer::isItem($clientId);
        if ($client->getCertificationId() > 0) {
            $phoneNumber = $client->getCertificationInstance()->getPhone();
        } else if ($client->getInformationInstance()) {
            $phoneNumber = $client->getInformationInstance()->getPhone();
        } else {
            $phoneNumber = $client->getPhone();
        }

        if (Util::isNumeric($phoneNumber) == false || strlen($phoneNumber) < 9 ) return false;
        if ($message == '' ) return false;

        // appKey 처리 필요.
        $appKey = 'nLesySceFBmpCANK';

        $url = 'https://api-sms.cloud.toast.com';
        $post = '/sms/v2.1/appKeys/' . $appKey . '/sender/sms';

        $postBody = array();
//        $postBody["templateId"] = "";
//        $postBody["body"] = "##name## 님" .$message; // 내용. 필수
        $postBody["body"] = $message; // 내용. 필수
        $postBody["sendNo"] = "025400329"; // 발신번호. 필수
//        $postBody["requestDate"] = Util::getLocalTime();
//        $postBody["senderGroupingKey"] = "";

        // "recipientList":[{"recipientNo":"01027714551","countryCode":"82","internationalRecipientNo":""}]
        // 애들이 원하는 배열이 이런식. 이렇게 만들어주려면 이렇게 해야함.

        $recipientList = array();
        $recipientList["recipientNo"] = $phoneNumber; // 수신번호 국가코드와 조합 가능. 필수
        $recipientList["countryCode"] = "82";
        $recipientList["internationalRecipientNo"] = "";
        // 원하는 대로 키 추가 가능. 사용법은 ##key## 위에 body 참고
        // 안쓸때 어레이 선언되있으면 에러.
//        $recipientList["templateParameter"] = array();
//        $recipientList["templateParameter"]['name'] = $client->getName();

        $postBody["recipientList"][] = $recipientList;

        $postBody["userId"] = "admin"; // 발송 구분자. 어디에서 쓰이는지는 잘...

        $jsonBody = \GuzzleHttp\json_encode($postBody);


        $curl = new Curl();
//        $curl->setOption(CURLINFO_CONTENT_TYPE, 'application/json;charset=UTF-8');
        $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        $curl->setOption(CURLOPT_HEADER, false);
        /*
                $curl->setOption(CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json'
                ));*/

        $curl->setOption(CURLOPT_TIMEOUT, 5000);
        $curl->header->set("Content-Type" , "application/json;charset=UTF-8");
        /*
                var_dump('<br>');
                var_dump($postBody);
                var_dump('<br>');
                var_dump($jsonBody);
                var_dump('<br>');
        */


        $curlRetData = $curl->post($url . $post, $jsonBody);
        $curlRetBody = json_decode($curlRetData->body, true);

        var_dump($curlRetBody);exit;
        if ($curlRetBody != false && $curlRetBody['header']['resultCode'] == 0){
            var_dump('트루');
            return true;
        } else {
            var_dump('펄스');
            return false;
        }
    }

    public static final function getRate($val1, $val2){
        $result = '-';
        if($val2 != 0){
            $result = round( ($val1 - $val2) /$val2*100, 2);
        }
        return $result;
    }


}
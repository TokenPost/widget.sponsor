<?php
namespace PL\Models\Site\Item\File;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Info\Site;
use PL\Models\Admin\Admin;
use PL\Models\News\Article\Container as ArticleContainer;
//use PL\Models\Client\Payment\Container as PaymentContainer;
use PL\Models\News\Article\Article as NewsArticle;
use PL\Models\File\DownloadLog\DownloadLog;
use PL\Models\File\DownloadLog\Container as DownloadLogContainer;

class File extends AbstractSingleton {

    const tableName = 'SiteItemFile';

    /**
     * 상수 설정
     */
    const Status_Active   = 0; // Active
    const Status_Inactive = 1; // Inactive
    const Status_Block    = 5;

    const UploaderType_Bot        = 1;
    const UploaderType_Admin      = 2;
    const UploaderType_Client     = 3;
    const UploaderType_Guest      = 4;

    const FileType_File      = 1;
    const FileType_Image     = 2;
    const FileType_Pdf       = 3;

    protected $_infoSite;

    // const UploadPath = '/var/www/econotimes.com/public/assets/uploads/';

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (is_numeric($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }


    public static function getUploadPath(){
        if(APPLICATION_ENV == 'stage') {
            return '/var/www/uploads_publishlink/' . SITE_BRANCH_CODE . '/';
        } else {
            return '/var/www/uploads_publishlink/' . SITE_BRANCH_CODE . '/';
        }
    }

    // @fixme: cron서버가 다른경우등?
    public static function getCronUploadPath(){
        return '/var/www/uploads_publishlink/' . SITE_BRANCH_CODE . '/';
    }

    public static function getBranchCode(){
        return SITE_BRANCH_CODE;

        /*$infoSite = Site::getInstance(1);
        return $infoSite->getBranchCode();*/
        //return DI::getDefault()->getShared('infoSite')->getUploadPath();
    }

    public function getLocalPath(){
        return self::getUploadPath() . $this->getFullPath();
    }


    public function getImageInfo(){
        if($this->getFileTypeId() == self::FileType_Image){
            return getimagesize($this->getLocalPath());
        }
        return null;
    }



    public function isDelete(){
        switch($this->getStatus()){
            case self::Status_Inactive:
                return true;
                break;

            default:
                return false;
            //return '(알 수 없음)';
        }
    }


    public function isOwner($uploaderType, $uploaderId) :bool
    {
        if($this->getUploaderType() != $uploaderType) return false;
        if($this->getUploaderId() != $uploaderId) return false;
        return true;
    }

    public function getImageSizeClass($frameWidth, $frameHeight)
    {

        $size = $this->getImageSizeInfo();
        if($size[0] < 1 || $size[1] < 1) return 'imageError';

        $newWidth = $imageWidth = $size[0];
        $newHeight = $imageHeight = $size[1];




        // 프레임보다 이미지가 작은경우
        if($imageWidth < $frameWidth && $imageHeight < $frameHeight){

            $newWidth = $frameWidth;
            $newHeight = $imageHeight * $frameWidth / $imageWidth;

            if($newHeight >= $frameHeight){
                return 'verticalCrop';
            } else {
                return 'horizonCrop';
            }

            //return 'noCrop';
        }

        if($imageWidth > $imageHeight){
            if($imageWidth > $frameWidth){
                $newWidth = $frameWidth;
                $newHeight = $imageHeight * $frameWidth / $imageWidth;
            }

            if($newHeight >= $frameHeight){
                return 'verticalCrop';
            } else {
                return 'horizonCrop';
            }

        } else {

            if($imageHeight > $frameHeight){
                $newHeight = $frameHeight;
                $newWidth = $frameHeight * $imageWidth / $imageHeight;
            }
            if($newWidth >= $frameWidth){
                return 'horizonCrop';
            } else {
                return 'verticalCrop';
            }
        }


        /*var_dump('image width : ' . $imageWidth); echo "<br>" . PHP_EOL;
        var_dump('image height : ' . $imageHeight); echo "<br>" . PHP_EOL;
        var_dump('frame width : ' . $frameWidth); echo "<br>" . PHP_EOL;
        var_dump('frame height : ' . $frameHeight); echo "<br>" . PHP_EOL;
        var_dump('new width : ' . $newWidth); echo "<br>" . PHP_EOL;
        var_dump('new height : ' . $newHeight); echo "<br>" . PHP_EOL;
        exit;*/

    }

    public function isPortrait() :bool
    {
        $size = $this->getImageSizeInfo();
        if($size[0] < $size[1]) return true;
        return false;
    }

    public function getImageSizeInfo()
    {
        if ($this->getFileTypeId() == self::FileType_Image) {
            $imagePath = self::getUploadPath();
            $imagePath .= substr($this->getFullPath(), 1);

            if (file_exists($imagePath) == true) {
                $info = getimagesize($imagePath);
                if($info == false) return array(0, 0);

                return $info;
            }


        }
        return array(0, 0);
    }

    public function getImageSize($glue = ' * ', $suffix = ' px'){
        $ret = '';

        $imageInfo = $this->getImageSizeInfo();
        if($imageInfo[0] >= 1 && $imageInfo[1] >= 1){
            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];
            if(is_numeric($imageWidth) == true && is_numeric($imageHeight) == true){
                $ret .= $imageWidth . $glue . $imageHeight . $suffix;
            }
        }

        return $ret;
    }




    public function getId() {
        return $this->_info['id'];
    }

    public function getUploaderTypeId() {
        return $this->getUploaderType();
    }

    public function getUploaderType() {
        return $this->_info['uploaderType'];
    }

    public function setUploaderType($uploaderType) {
        $this->_info['uploaderType']    = $uploaderType;
        $this->_changes['uploaderType'] = $this->_info['uploaderType'];
    }

    public function getUploaderToken() {
        return $this->_info['uploaderToken'];
    }

    public function setUploaderToken($uploaderToken) {
        $this->_info['uploaderToken']    = $uploaderToken;
        $this->_changes['uploaderToken'] = $this->_info['uploaderToken'];
    }

    public function getUploaderId() {
        return $this->_info['uploaderId'];
    }

    public function setUploaderId($uploaderId) {
        $this->_info['uploaderId']    = $uploaderId;
        $this->_changes['uploaderId'] = $this->_info['uploaderId'];
    }

    public function getAllowFileTypeId() {
        return $this->_info['allowFileTypeId'];
    }

    public function setAllowFileTypeId($allowFileTypeId) {
        $this->_info['allowFileTypeId']    = $allowFileTypeId;
        $this->_changes['allowFileTypeId'] = $this->_info['allowFileTypeId'];
    }

    public function getExternal() {
        return $this->_info['external'];
    }

    public function setExternal($external) {
        $this->_info['external']    = $external;
        $this->_changes['external'] = $this->_info['external'];
    }

    public function getExternalType() {
        return $this->_info['externalType'];
    }

    public function setExternalType($externalType) {
        $this->_info['externalType']    = $externalType;
        $this->_changes['externalType'] = $this->_info['externalType'];
    }

    public function getLink() {
        return $this->getExternalLink();
    }

    public function getExternalLink() {
        return $this->_info['externalLink'];
    }

    public function setExternalLink($externalLink) {
        $this->_info['externalLink']    = $externalLink;
        $this->_changes['externalLink'] = $this->_info['externalLink'];
    }

    public function getCaption() {
        return $this->_info['caption'];
    }

    public function setCaption($caption) {
        $this->_info['caption']    = $caption;
        $this->_changes['caption'] = $this->_info['caption'];
    }

    public function getProvider() {
        return $this->_info['provider'];
    }

    public function setProvider($provider) {
        $this->_info['provider']    = $provider;
        $this->_changes['provider'] = $this->_info['provider'];
    }

    public function getFileTypeId() {
        return $this->_info['fileTypeId'];
    }

    public function setFileTypeId($fileTypeId) {
        $this->_info['fileTypeId']    = $fileTypeId;
        $this->_changes['fileTypeId'] = $this->_info['fileTypeId'];
    }

    public function getFileType() {
        return $this->_info['fileType'];
    }

    public function setFileType($fileType) {
        $this->_info['fileType']    = $fileType;
        $this->_changes['fileType'] = $this->_info['fileType'];
    }

    public function getFileName() {
        return $this->_info['fileName'];
    }

    public function setFileName($fileName) {
        $this->_info['fileName']    = $fileName;
        $this->_changes['fileName'] = $this->_info['fileName'];
    }

    public function getFullLinkUrl($thumbnail = '') {
        $url = $this->getFullPath($thumbnail);
        if($this->getExternal() == 'Y'){
            return $url;
        }
        $domain = '';

        if(APPLICATION_ENV == 'dev'){
            if(defined('IMAGE_URL') == true){
                $domain .= IMAGE_URL . '';
            }elseif(defined('FILE_URL') == true){
                $domain .= PROTOCOL . '://' . FILE_URL . '';
            }elseif(defined('SERVICE_URL') == true){
                $domain .= PROTOCOL . '://file.' . SERVICE_URL . '';
            } else {
                $domain .= '';
            }
        } else {
            if(defined('IMAGE_URL') == true){
                $domain .= IMAGE_URL . '';
            }else if(defined('SERVICE_URL') == true){
                $domain .= PROTOCOL . '://f1.' . SERVICE_URL . '';
            } else {
                $domain .= '';
            }
        }


        return $domain . $url;
    }

    public function getFullPath($thumbnail = '') {
        $url = '';
        if($this->getExternal() == 'Y'){
            $url .= $this->getLink();
            $url .= $this->getRandomName() . $this->getExt(); // 썸네일 미존재
        } else {
            $url .= '/' . $this->getYearMonth() . '/';
            $url .= $this->getFullName($thumbnail);
        }
        return $url;
    }

    public function getFullName($thumbnail = '') {
        if(strtolower($this->getExt()) == '.svg') $thumbnail = '';
        if($thumbnail != '') $thumbnail = '_th_' . $thumbnail;
        return $this->getRandomName() . $thumbnail . $this->getExt();
    }

    public function getYearMonth() {
        return $this->_info['yearMonth'];
    }

    public function setYearMonth($yearMonth) {
        $this->_info['yearMonth']    = $yearMonth;
        $this->_changes['yearMonth'] = $this->_info['yearMonth'];
    }

    public function getRandomName() {
        return $this->_info['randomName'];
    }

    public function setRandomName($randomName) {
        $this->_info['randomName']    = $randomName;
        $this->_changes['randomName'] = $this->_info['randomName'];
    }

    public function getSize() {
        return $this->_info['size'];
    }

    public function setSize($size) {
        $this->_info['size']    = $size;
        $this->_changes['size'] = $this->_info['size'];
    }

    public function getExt() {
        return $this->_info['ext'];
    }

    public function setExt($ext) {
        $this->_info['ext']    = $ext;
        $this->_changes['ext'] = $this->_info['ext'];
    }

    public function getDownload() {
        return $this->_info['download'];
    }

    public function setDownload($download) {
        $this->_info['download']    = $download;
        $this->_changes['download'] = $this->_info['download'];
    }

    public function addDownload($clientId){
        $this->setDownload($this->getDownload()+1);

        $downloadLog = new DownloadLogContainer($this);
        $downloadLog->create($clientId);
    }

    public function getIp() {
        return $this->_info['ip'];
    }

    public function setIp($ip) {
        $this->_info['ip']    = $ip;
        $this->_changes['ip'] = $this->_info['ip'];
    }

    public function getRevision() {
        return $this->_info['revision'];
    }

    public function setRevision($revision) {
        $this->_info['revision']    = $revision;
        $this->_changes['revision'] = $this->_info['revision'];
    }

    public function addRevision() {
        $this->setRevision($this->getRevision()+1);
    }

    public function getUpstream() {
        return $this->_info['upstream'];
    }

    public function setUpstream($upstream) {
        $this->_info['upstream']    = $upstream;
        $this->_changes['upstream'] = $this->_info['upstream'];
    }

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

    public function getRegTimestamp($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regTimestamp']));
    }

    public function setRegTimestamp($regTimestamp) {
        $this->_info['regTimestamp']    = $regTimestamp;
        $this->_changes['regTimestamp'] = $this->_info['regTimestamp'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }

    /**
     * 파일의 상태 값을 텍스트로 반환합니다
     *
     * @return string
     */
    public function getStatusName() {
        switch ($this->_info['status']) {
            case self::Status_Active:
                return 'active';
                break;
            case self::Status_Inactive:
                return 'inactive';
                break;

            default:
                return '(??)';
        }
    }

}
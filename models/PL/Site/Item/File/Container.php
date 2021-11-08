<?php
namespace PL\Models\Site\Item\File;

use Exception;
use function GuzzleHttp\Promise\all;
use Phalcon\Db;
use Phalcon\DI;
use Phalcon\Http\Client\Provider\Curl;

use PL\Models\Adapter\AbstractContainer;

use PL\Models\News\News;
use PL\Models\News\Article\Article;
use PL\Models\News\Article\Image\Image as NewsArticleImage;
use PL\Models\Util\Util;
use PL\Models\Source\Source;

class Container extends AbstractContainer
{

    public function __construct()
    {
        parent::__construct(File::tableName);
        $this->setTableName(File::tableName);
    }

    public static function getTableNameStatic()
    {
        return File::tableName;
    }

    public static function getObjectInstanceStatic($date) : File
    {
        return File::getInstance($date);
    }

    public function getObjectInstance($date) : File
    {
        return File::getInstance($date);
    }


    public static function isFileName($fileName)
    {
        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE CONCAT(`randomName`, `ext`) = ? LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->getShared('master/slave db');
        $data = $db->query($query, array($fileName))->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }
        return false;
    }

    /**
     * @param string $mode 파일인지 URL인지
     * @param string $type 허용 타입
     * @param int $uploaderType 업로더 Type - Admin Client Bot
     * @param $uploaderId 업로더 ID
     * @param $values / caption
     * @param string $path 업로드 Path
     * @return array
     */
    public function createFile($mode = 'file', $allowFileType = 'image', $uploaderType = File::UploaderType_Admin, $uploaderId = 0, $values, $path = '')
    {
        try{
            $url = '';
            $uploadFile = null;
            if($path == '') $path = File::getUploadPath();
            $branchCode = File::getBranchCode();

            // Branch upload directory check
            //$path .= $branchCode . '/';

            if (file_exists($path) != true) {
                mkdir($path , 0755);
            }


            $now = Util::getDbNow();
            $time = time();
            $uploadYearMonth = date('Y/m', $time);
            // 내부에서만 사용하는 파일.
            // 년월별이기때문에 폴더 존재여부부터 확인.

            // 크롭에서 폴더를 생성하면 루트소유가 되므로 적절하게 바꿔준다.
            // 년도
            if (file_exists($path . date('Y/', $time)) != true) {
                mkdir($path . date('Y/', $time), 0755);
                chown($path . date('Y/', $time), 'www-data');
                chgrp($path . date('Y/', $time), 'www-data');
            } else {
                // 이미 존재
            }

            //월
            if (file_exists($path . $uploadYearMonth . '/') != true) {
                mkdir($path . date('Y/m/', $time) , 0755);
                chown($path . date('Y/m/', $time) , 'www-data');
                chgrp($path . date('Y/m/', $time) , 'www-data');
            } else {
                // 이미 존재
            }

            $path .= $uploadYearMonth . '/';

            $item = array();
            $imageWhiteExt = array('.bmp', '.jpg', '.jpeg', '.gif', '.png');

            if(isset($values['url']) == true)            $url            = trim($values['url']);
            if(isset($values['file']) == true)           $uploadFile     = $values['file'];
            if(isset($values['fileCaption']) == true){
                $fileCaption = $values['fileCaption'];
            } else {
                $fileCaption = '';
            }
            if(isset($values['itemCaption']) == true){
                $itemCaption = trim($values['itemCaption']);
            } else {
                $itemCaption = '';
            }
            if(isset($values['provider']) == true){
                $provider = trim($values['provider']);
            } else {
                $provider = '';
            }

            // 모두다 설치된게 아니라 미적용.
            if($mode == 'file' && false) {
                // scaning virus
                exec("clamscan '" . $uploadFile['tmp_name'] . "'", $output, $result);
                /*
                            var_dump($path.$randomFilename.$ext);
                            var_dump($output);
                            var_dump($result);*/
                if ($result === 0) {
                    // everything ok
                } elseif ($result === 1) {
                    exec("rm -f " . $uploadFile['tmp_name']);
                    throw new Exception('Upload failed. The file have some virus. : code : 1');

                    //  a virus
                } elseif ($result === 2) {
                    exec("rm -f " . $uploadFile['tmp_name']);
                    //other errors.
                    throw new Exception('Upload failed. Virus check fail. : code : 2');
                }
            }

            /**
            $fileCaption = trim($this->request->getPost('fileCaption'));
            $articleCaption = trim($this->request->getPost('articleCaption'));

            if($this->view->adminId != $this->request->getPost('adminId')){
            throw new Exception('Admin does not exist');
            }
            $adminId = $this->view->adminId;
             **/

            if($mode == 'file'){

                if($uploadFile['size'] > 1024 * 1024 * 10)
                {
                    throw new Exception('Upload file size over:10M : code : 4');
                }

                if ($uploadFile['error'] > 0 ) {
                    switch ($uploadFile['error']){
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            throw new Exception('파일 최대 사이즈를 초과 했습니다. code : '  . $uploadFile['error']);
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            throw new Exception('파일이 일부분만 전송되었습니다. code : '  . $uploadFile['error']);
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new Exception('파일 전송에 실패하였습니다. code : '  . $uploadFile['error']);
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                        case UPLOAD_ERR_CANT_WRITE:
                            throw new Exception('서버처리중 오류가 발생하였습니다. code : '  . $uploadFile['error']);
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            throw new Exception('서버처리중 오류가 발생하였습니다. code : '  . $uploadFile['error']);
                            break;
                        default:
                            throw new Exception('서버처리중 알수없는 오류가 발생하였습니다. code : 99');
                            break;
                    }
                }

                $ext = strtolower(strrchr($uploadFile['name'],"."));
                $filename = $uploadFile['name'];
                $size = $uploadFile['size'];

                if($allowFileType == 'image'){
                    // check images
                    if(in_array($ext, $imageWhiteExt) != true){
                        throw new Exception('File upload fail : "' . $ext . '"  : not allowed image extension : code : 7');
                    }

                    if(!($uploadFile['type'] == 'image/jpeg' || $uploadFile['type'] == 'image/png'  || $uploadFile['type'] == 'image/gif' ||$uploadFile['type'] == 'image/bmp')){
                        throw new Exception('File upload fail : not image : code : 8');
                    }
                    $item['fileType'] = substr(strrchr($uploadFile['type'], '/'), 1);
                    $item['fileTypeId'] = File::FileType_Image;
                } else {
                    if($ext == '.jpg' || $ext == '.jpeg' || $ext == '.png' || $ext == '.gif' || $ext == '.bmp'){
                        if(!($uploadFile['type'] == 'image/jpeg' || $uploadFile['type'] == 'image/png' || $uploadFile['type'] == 'image/gif' || $uploadFile['type'] == 'image/bmp')){
                            throw new Exception('File upload fail : not image : code : 9');
                        }
                        $item['fileType'] = substr(strrchr($uploadFile['type'], '/'), 1);
                        $item['fileTypeId'] = File::FileType_Image;
                    }elseif($ext == '.pdf'){
                        /*if(!($uploadFile['type'] == 'image/jpeg' || $uploadFile['type'] == 'image/png' || $uploadFile['type'] == 'image/gif' || $uploadFile['type'] == 'image/bmp')){
                            throw new Exception('File upload fail : not pdf : code : 10');
                        }*/
                        $item['fileType'] = 'pdf'; // ?? 맞나
                        $item['fileTypeId'] = File::FileType_Pdf;
                    } else {
                        //$item['fileType'] = 'file';
                        //$item['fileType'] = substr($ext, 1); // 확장자로 해야하나? 체크 하는방법은?
                        $item['fileType'] = $uploadFile['type'];
                        $item['fileTypeId'] = File::FileType_File;
                    }
                }

            } elseif($mode == 'url') {
                if($url == '') throw new Exception('Url does not exist : code : 10');

                $filename = '';
                $imageUrl = $url;
                if(strpos($url , 'naver.net') != false || strpos($url , 'pstatic.net') != false) {
                    if(strpos($imageUrl , '?') !== false){
                        $imageUrl = strchr($imageUrl , '?', true);
                    }
                    $filename = substr(strrchr($imageUrl , '/'), 1);

                    $imageUrl = str_replace($filename, urlencode($filename), $url);
                } else {

                }


                // @fixme: 뒤에 ? 파라메터 삭제. naver글가져오거나 print하는곳은 막혀서 삭제.
                if(strpos($imageUrl , '?') != false) {
                    //$imageUrl = strchr($imageUrl , '?', true);
                }
                $ext = strtolower(strrchr($imageUrl , '.'));
                if($filename == ''){
                    $filename = substr(strrchr($imageUrl , '/'), 1);
                    if(strpos($filename , '?') != false) {
                        $filename = strchr($filename , '?', true);
                    }
                }


                // 확장자가 없는경우가 있나?
                //if($ext == '' || $ext == '.') throw new Exception('Url image does not exist extension. : code : 11');
                // print일경우 없을수 있다.

                // 이미지 모드일때만 확장자 체크
                if($allowFileType == 'image'){
                    if(in_array($ext, $imageWhiteExt) != true){
                        //throw new Exception('Url image does not available extension. EXT : ' . $ext . ' : code : 12');
                        $ext = '.tmppp';
                    }
                }

                file_get_contents($imageUrl);

                //$refererUrl = "http://blog.naver.com/PostList.nhn?blogId=hansungnews&categoryNo=0&from=postList";
                $refererUrl = "http://blog.naver.com/PostView.nhn?blogId=hansungnews&logNo=221621358616";
                //var_dump($imageUrl);
                // 가져오기.
                //$ch = curl_init (urlencode($url));
                $ch = curl_init ($imageUrl);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                //curl_setopt($ch, CURLOPT_RANGE, "0-" . (1024 * 1024 * 10));// byte -> 10MB
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 7000);

                //curl_setopt($ch, CURLOPT_USERAGENT, Util::getRandomUserAgent());
                //curl_setopt($ch, CURLOPT_REFERER, $refererUrl);

                $raw = curl_exec($ch);
                $curlInfo = curl_getinfo($ch);
                curl_close ($ch);
                //var_dump($curlInfo);


                // 아래 언제나 잘 작동하는건가?
                /*if($allowFileType == 'image'){
                    if(strchr($curlInfo['content_type'], '/', true) != 'image'){
                        throw new Exception('File format does not image.');
                    }
                }*/

                $size = $curlInfo['size_download'];

            } else {
                throw new Exception('mode error : code : 14');
            }

            $timeConst = 1;
            while (true){
                //$randomFilename = substr(md5(time() + $timeConst), 3, 10);
                $randomFilename = Util::generateRandomAlphanumericLowercase(10);
                $fileExist = self::isExist($path, $randomFilename.$ext);
                if($fileExist != true){
                    // 없는 파일 생성 가능
                    break;
                } else {
                    //$timeConst += 22;
                    usleep(10);
                }
            };



            /**
             * write to file
             */
            if($mode == 'file') {

                // Rotate if mobile upload
                if($allowFileType == 'image' || $item['fileTypeId'] == File::FileType_Image){

                    $exif = exif_read_data($uploadFile['tmp_name']);
                    if(!empty($exif['Orientation'])) {

                        switch ($uploadFile['type']){
                            case 'image/jpeg':
                                $imageForRotate = imagecreatefromjpeg($uploadFile['tmp_name']);
                                break;
                            case 'image/png':
                                $imageForRotate = imagecreatefrompng($uploadFile['tmp_name']);
                                break;
                            case 'image/gif':
                                $imageForRotate = imagecreatefromgif($uploadFile['tmp_name']);
                                break;
                            case 'image/bmp':
                                $imageForRotate = imagecreatefromwbmp($uploadFile['tmp_name']);
                                break;
                            default:
                                $imageForRotate = null;
                                break;
                        }
                        if($imageForRotate == null){
                            throw new Exception('File upload fail : not image :  : code : 20');
                        }

                        switch($exif['Orientation']) {
                            case 8:
                                $imageForRotate = imagerotate($imageForRotate,90,0);
                                break;
                            case 3:
                                $imageForRotate = imagerotate($imageForRotate,180,0);
                                break;
                            case 6:
                                $imageForRotate = imagerotate($imageForRotate,-90,0);
                                break;
                        }
                        switch ($uploadFile['type']){
                            case 'image/jpeg':
                                imagejpeg($imageForRotate, $uploadFile['tmp_name']);
                                break;
                            case 'image/png':
                                imagepng($imageForRotate, $uploadFile['tmp_name']);
                                break;
                            case 'image/gif':
                                imagegif($imageForRotate, $uploadFile['tmp_name']);
                                break;
                            case 'image/bmp':
                                imagewbmp($imageForRotate, $uploadFile['tmp_name']);
                                break;
                        }
                    }
                }

                if (move_uploaded_file($uploadFile['tmp_name'], $path.$randomFilename.$ext) === false){
                    throw new Exception('File upload fail : not moved : code : 21');
                }
            } elseif($mode == 'url') {


                //var_dump($ext);
                // 확장자를 모르는경우
                if($ext == '.tmppp'){

                    $fp = fopen($path . $randomFilename.'.tmppp', 'x');
                    fwrite($fp, $raw);
                    fclose($fp);
                    chmod($path . $randomFilename.'.tmppp' , 0644);

                    $imageMime = image_type_to_mime_type(exif_imagetype($path . $randomFilename.'.tmppp'));
                    //var_dump($imageMime);
                    if($imageMime == '') throw new Exception('Upload failed. Image type error : code : 21-1');

                    $allowImageMime = array('jpeg', 'gif', 'png', 'bmp', 'tiff');
                    $mimeExplode = explode('/', $imageMime);


                    // 이미지가 아니다.
                    if(sizeof($mimeExplode) != 2) throw new Exception('Upload failed. Image type error : code : 21-2');
                    if($mimeExplode[0] != 'image'){
                        //throw new Exception('Upload failed. Image type error : code : 21-3');
                        // octet-stream
                        if(strpos($url , 'naver.net') != false) {
                            // 네이버에서 가져옴.

                            if(strpos($url , '?') !== false){
                                $url = strchr($url , '?', true);
                            }
                            $ext = strtolower(strrchr($url , '.'));
                            if($ext == '.jpeg'){
                                $ext = '.jpg';
                            }
                            $filename = substr(strrchr($url , '/'), 1);

                        } else {
                            throw new Exception('Upload failed. Image type error : code : 21-4');
                        }
                        $item['fileType'] = substr($ext, 1);

                    } else {
                        if(in_array($mimeExplode[1], $allowImageMime) != true) throw new Exception('Upload failed. Image type error : code : 21-4');

                        if($mimeExplode[1] != 'jpeg'){
                            $ext = '.' . $mimeExplode[1];
                        } else {
                            $ext = '.jpg';
                        }
                        $item['fileType'] = $mimeExplode[1];
                    }
                    rename($path . $randomFilename.'.tmppp', $path . $randomFilename . $ext);

                    //$item['fileType'] = substr(strrchr($uploadFile['type'], '/'), 1);
                } else {

                    $fp = fopen($path . $randomFilename.$ext, 'x');
                    fwrite($fp, $raw);
                    fclose($fp);
                    chmod($path . $randomFilename.$ext , 0644);
                }

                $clamscan = 'N';
                if($clamscan == 'Y'){
                    // scaning virus
                    exec("clamscan '" . $path.$randomFilename.$ext . "'", $output, $result);
                    /*
                                var_dump($path.$randomFilename.$ext);
                                var_dump($output);
                                var_dump($result);*/
                    if ($result === 0) {
                        // everything ok
                    } elseif($result === 1) {
                        exec("rm -f $path$randomFilename$ext");
                        throw new Exception('Upload failed. The file have some virus. : code : 22');

                        //  a virus
                    } elseif($result === 2) {
                        exec("rm -f $path$randomFilename$ext");
                        //other errors.
                        throw new Exception('Upload failed. Virus check fail. : code : 23');
                    }
                }

            }


            if(false && isset($item['fileTypeId']) == true && $item['fileTypeId'] == File::FileType_Image){
            //if(false && isset($item['fileType']) == true && $item['fileType'] == 'image'){
                // 모바일에서 올릴경우 회전된다.
                $exif = exif_read_data($path.$randomFilename.$ext);
                if(!empty($exif['Orientation'])) {
                    $imageForRotate = null;
                    $imagePath = $path.$randomFilename.$ext;
                    $imageInfo = getimagesize($imagePath);
                    switch ($imageInfo['mime']){
                        case 'image/jpeg':
                            $imageForRotate = imagecreatefromjpeg($imagePath);
                            break;
                        case 'image/png':
                            $imageForRotate = imagecreatefrompng($imagePath);
                            break;
                        case 'image/gif':
                            $imageForRotate = imagecreatefromgif($imagePath);
                            break;
                        case 'image/bmp':
                            $imageForRotate = imagecreatefromwbmp($imagePath);
                            break;
                        default:
                            $imageForRotate = null;
                            break;
                    }

                    if($imageForRotate == null){
                        // 이미지 오류?
                    } else {
                        switch($exif['Orientation']) {
                            case 8:
                                $imageForRotate = imagerotate($imageForRotate,90,0);
                                break;
                            case 3:
                                $imageForRotate = imagerotate($imageForRotate,180,0);
                                break;
                            case 6:
                                $imageForRotate = imagerotate($imageForRotate,-90,0);
                                break;
                        }
                        switch ($imageInfo['mime']){
                            case 'image/jpeg':
                                imagejpeg($imageForRotate, $imagePath);
                                break;
                            case 'image/png':
                                imagepng($imageForRotate, $imagePath);
                                break;
                            case 'image/gif':
                                imagegif($imageForRotate, $imagePath);
                                break;
                            case 'image/bmp':
                                imagewbmp($imageForRotate, $imagePath);
                                break;
                        }
                    }

                }
            }


            $imagePath = $path.$randomFilename.$ext;
            $imageInfo = getimagesize($imagePath);

            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];

            // resize
            if(isset($values['resize']) == true && Util::isInteger($values['resizeWidth'], 0) && Util::isInteger($values['resizeHeight'], 0)){

                /*$imagePath = $path.$randomFilename.$ext;
                $imageInfo = getimagesize($imagePath);*/

                $newWidth = $width = $imageWidth;
                $newHeight = $height = $imageHeight;


                // 프로필 최대 사이즈
                // 가로 세로 동일.
                $profileImageSize = 120;

                if($values['resizeCrop'] == 'Y'){
                    $crop = 'Y';
                } else {
                    $crop = 'N';
                }


                if($crop == 'Y'){
                    if($newWidth > $profileImageSize){
                        $newWidth = $profileImageSize;
                        $newHeight = $profileImageSize*$height/$width;
                    }

                    /**
                     * if($newHeight > $profileImageSize){
                    $newWidth = $profileImageSize*$newWidth/$newHeight;
                    $newHeight = $profileImageSize;
                    // width 작아졌는지 체크.
                    if($newWidth < $profileImageSize){
                    $newHeight = $profileImageSize*$newHeight/$newWidth;
                    $newWidth = $profileImageSize;
                    }
                    } else
                     */
                    // 조정한 사이즈가 정사이즈보다 작을경우 다시 키운다.
                    if($newHeight < $profileImageSize){
                        $newWidth = $profileImageSize*$newWidth/$newHeight;
                        $newHeight = $profileImageSize;
                    }

                    $srcX = 0;
                    $srcY = 0;
                    if($newWidth > $profileImageSize) $srcX = ($profileImageSize - $newWidth) / 2 * $width / $newWidth;
                    if($newHeight != $profileImageSize) $srcY = ($profileImageSize - $newHeight) / 2 * $width / $newWidth;

                    $newImage = imagecreatetruecolor($profileImageSize, $profileImageSize);
                    $backgroundColor = imagecolorallocate($newImage, 255, 255, 255);
                    imagefill($newImage, 0, 0, $backgroundColor);



                    // size 작은거처리.

                    $srcX = 0;
                    $srcY = 0;


                    if($width > $height){
                        $srcX = ($width - $height) / 2;
                    } else {
                        $srcY = ($height - $width) / 2;
                    }
                    /*
                                        $ratio = max($profileImageSize/$newWidth, $profileImageSize/$newHeight);
                                        $newHeight = $height / $ratio;
                                        $x = ($newWidth - $width / $ratio) / 2;
                                        $newWidth = $width / $ratio;

                                        ($w - $width)/2, ($h - $height)/2,*/
                    switch ($imageInfo['mime']){
                        case 'image/jpeg':
                            $originalImage = imagecreatefromjpeg($imagePath);
                            imagecopyresampled($newImage, $originalImage, 0, 0, $srcX, $srcY, $profileImageSize, $profileImageSize, $width-$srcX*2, $height-$srcY*2);
                            //                   ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
                            //0, 0, ($originalW - $width)/2, ($originalH - $height)/2, $width, $height, $w, $h);    $srcX - (($srcX - $width)/2), $srcY - (($srcY - $height)/2)
                            imagejpeg($newImage, $imagePath);
                            break;
                        case 'image/png':
                            $originalImage = imagecreatefrompng($imagePath);
                            imagecopyresampled($newImage, $originalImage, 0, 0, $srcX, $srcY, $profileImageSize, $profileImageSize, $width-$srcX*2, $height-$srcY*2);
                            imagepng($newImage, $imagePath);
                            break;
                        case 'image/gif':
                            $originalImage = imagecreatefromgif($imagePath);
                            imagecopyresampled($newImage, $originalImage, 0, 0, $srcX, $srcY, $profileImageSize, $profileImageSize, $width-$srcX*2, $height-$srcY*2);
                            imagegif($newImage, $imagePath);
                            break;
                        case 'image/bmp':
                            $originalImage = imagecreatefromwbmp($imagePath);
                            imagecopyresampled($newImage, $originalImage, 0, 0, $srcX, $srcY, $profileImageSize, $profileImageSize, $width-$srcX*2, $height-$srcY*2);
                            imagewbmp($newImage, $imagePath);
                            break;
                    }
                } else {
                    if($newWidth > $profileImageSize){
                        $newWidth = $profileImageSize;
                        $newHeight = $profileImageSize*$height/$width;
                    }

                    if($newHeight > $profileImageSize){
                        $newHeight = $profileImageSize;
                        $newWidth = $profileImageSize*$width/$height;
                    }

                    $dstX = 0;
                    $dstY = 0;
                    if($newWidth != $profileImageSize) $dstX = ($profileImageSize - $newWidth) / 2;
                    if($newHeight != $profileImageSize) $dstY = ($profileImageSize - $newHeight) / 2;

                    $newImage = imagecreatetruecolor($profileImageSize, $profileImageSize);
                    $backgroundColor = imagecolorallocate($newImage, 255, 255, 255);
                    imagefill($newImage, 0, 0, $backgroundColor);



                    switch ($imageInfo['mime']){
                        case 'image/jpeg':
                            $originalImage = imagecreatefromjpeg($imagePath);
                            imagecopyresampled($newImage, $originalImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $width,$height);
                            imagejpeg($newImage, $imagePath);
                            break;
                        case 'image/png':
                            $originalImage = imagecreatefrompng($imagePath);
                            imagecopyresampled($newImage, $originalImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $width,$height);
                            imagepng($newImage, $imagePath);
                            break;
                        case 'image/gif':
                            $originalImage = imagecreatefromgif($imagePath);
                            imagecopyresampled($newImage, $originalImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $width,$height);
                            imagegif($newImage, $imagePath);
                            break;
                        case 'image/bmp':
                            $originalImage = imagecreatefromwbmp($imagePath);
                            imagecopyresampled($newImage, $originalImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $width,$height);
                            imagewbmp($newImage, $imagePath);
                            break;
                    }
                }

            }
            // end of resize


            switch($ext){
                case '.pdf':
                    $item['fileType'] = 'pdf';
                    $uploadFileType = 'pdf';
                    $uploadFileTypeId = File::FileType_Pdf;
                    break;
                case '.jpg':
                case '.jpeg':
                case '.gif':
                case '.png':
                case '.bmp':
                    $item['fileType'] = 'image';
                    $uploadFileType = substr($ext, 1);
                    $uploadFileTypeId = File::FileType_Image;
                    break;
                default:
                    $item['fileType'] = 'file';
                    $uploadFileType = substr($ext, 1);
                    $uploadFileTypeId = File::FileType_File;
                    break;

            }

            // 위에서 계산
            //$item['type']         = 'image';

            switch ($allowFileType){
                case 'image':
                    $allowFileTypeId = File::FileType_Image;
                    break;
                case 'pdf':
                    $allowFileTypeId = File::FileType_Pdf;
                    break;
                case 'file':
                default:
                    $allowFileTypeId = File::FileType_File;
                    break;
            }

            $item['uploaderType']    = $uploaderType;
            $item['uploaderId']      = $uploaderId;
            $item['uploaderToken']   = Util::generateRandomAlphanumeric(10);
            $item['allowFileTypeId'] = $allowFileTypeId;
            $item['external']        = 'N';
            $item['externalType']    = ''; // 외부링크 가져오는게 아니기 때문
            $item['externalLink']    = $url;
            $item['provider']        = $provider;
            $item['caption']         = $fileCaption;
            $item['fileTypeId']      = $uploadFileTypeId;
            $item['fileType']        = $uploadFileType;
            $item['fileName']        = $filename;
            $item['yearMonth']       = $uploadYearMonth;
            $item['randomName']      = $randomFilename;
            $item['size']            = $size;
            $item['ext']             = $ext;
            $item['download']        = 0;
            $item['regIp']           = CLIENT_IP;
            $item['regDate']         = Util::getDbNow();
            $item['regTimestamp']    = Util::getLocalTime();
            $item['status']          = File::Status_Active;

            $ret = $this->addNew($item);

            if(is_numeric($ret) == true && $ret >= 1){
                //echo "업로드 성공 : ".$set_file."<br>";
                chmod($path . $randomFilename . $ext , 0644);
                // DB 업로드성공.

                // thumbnail generate
                if(isset($values['thumbnail']) == true && $values['thumbnail'] == 'Y'){

                    if(isset($values['thumbnailType']) == true && $values['thumbnailType'] != ''){
                        self::brandNewImage($path, $randomFilename, $ext, $values['thumbnailType']);
                    }

                }


                $response                      = array();
                $response['error']             = 0;
                $response['status']            = 200;
                $response['id']                = $ret;
                $response['ext']               = $ext;
                $response['width']             = $imageWidth;
                $response['height']            = $imageHeight;
                $response['caption']           = $fileCaption;
                $response['provider']          = $provider;
                $response['itemCaption']       = $itemCaption;
                $response['type']              = $item['fileType'];  //??
                $response['uploadFileName']    = $filename;
                $response['uploadedFileName']  = $randomFilename;
                $response['randomName']        = $randomFilename;
                $response['uploaderToken']     = $item['uploaderToken'];
                $response['yearMonth']         = $item['yearMonth'];
                $response['yearMonthUrl']      = '/' . $uploadYearMonth . '/';
                $response['fullUrl']           = '/' . $uploadYearMonth . '/' . $randomFilename . $ext;

            } else {
                // DB입력 실패시 파일 삭제.
                exec("rm -rf $path$randomFilename$ext");
                $response            = array();
                $response['error']   = 1;
                $response['status']  = 200;
                $response['message'] = 'DB Update Fail';
            }


        } catch (Exception $e) {

            /*if($path != '' && $randomFilename != '' && $ext != ''){
                exec("rm -f $path$randomFilename$ext");
            }*/

            $response            = array();
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }
        return $response;

    }

    public function copyFile($originFile, $allowFileType = 'image', $uploaderType = File::UploaderType_Admin, $uploaderId = 0, $values, $path = '')
    {
        try{
            if($originFile == '' || is_file($originFile) != true){
                // 로컬에 파일이 존재하는지 체크.
                throw new Exception('Origin file not exist. : code : 1');
            }

            $url = '';
            $uploadFile = null;
            if($path == '') $path = File::getUploadPath();
            $branchCode = File::getBranchCode();

            // Branch upload directory check
            //$path .= $branchCode . '/';

            if (file_exists($path) != true) {
                mkdir($path , 0755);
            }


            $now = Util::getDbNow();
            $time = time();
            $uploadYearMonth = date('Y/m', $time);
            // 내부에서만 사용하는 파일.
            // 년월별이기때문에 폴더 존재여부부터 확인.

            // 크롭에서 폴더를 생성하면 루트소유가 되므로 적절하게 바꿔준다.
            // 년도
            if (file_exists($path . date('Y/', $time)) != true) {
                mkdir($path . date('Y/', $time), 0755);
                chown($path . date('Y/', $time), 'www-data');
                chgrp($path . date('Y/', $time), 'www-data');
            } else {
                // 이미 존재
            }

            //월
            if (file_exists($path . $uploadYearMonth . '/') != true) {
                mkdir($path . date('Y/m/', $time) , 0755);
                chown($path . date('Y/m/', $time) , 'www-data');
                chgrp($path . date('Y/m/', $time) , 'www-data');
            } else {
                // 이미 존재
            }

            $path .= $uploadYearMonth . '/';

            $item = array();
            $imageWhiteExt = array('.bmp', '.jpg', '.jpeg', '.gif', '.png');

            if(isset($values['fileCaption']) == true){
                $fileCaption = $values['fileCaption'];
            } else {
                $fileCaption = '';
            }
            if(isset($values['itemCaption']) == true){
                $itemCaption = trim($values['itemCaption']);
            } else {
                $itemCaption = '';
            }
            if(isset($values['provider']) == true){
                $provider = trim($values['provider']);
            } else {
                $provider = '';
            }


            $originFileInfo = pathinfo($originFile);

            $ext = '.' . strtolower($originFileInfo['extension']);
            $filename = $originFileInfo['filename'];

            if($filename == ''){
                throw new Exception('File name format error : code : 6-1');
            }
            if($ext == '.'){
                throw new Exception('File name format error : code : 6-2');
            }

            if($allowFileType == 'image'){
                // check images
                if(in_array($ext, $imageWhiteExt) != true){
                    throw new Exception('File upload fail : "' . $ext . '"  : not allowed image extension : code : 7');
                }

                $item['fileType'] = strtolower($originFileInfo['extension']);
                $item['fileTypeId'] = File::FileType_Image;
            } else {
                if($ext == '.jpg' || $ext == '.jpeg' || $ext == '.png' || $ext == '.gif' || $ext == '.bmp'){
                    $item['fileType'] = $item['fileType'] = strtolower($originFileInfo['extension']);
                    $item['fileTypeId'] = File::FileType_Image;
                }elseif($ext == '.pdf'){
                    /*if(!($uploadFile['type'] == 'image/jpeg' || $uploadFile['type'] == 'image/png' || $uploadFile['type'] == 'image/gif' || $uploadFile['type'] == 'image/bmp')){
                        throw new Exception('File upload fail : not pdf : code : 10');
                    }*/
                    $item['fileType'] = 'pdf'; // ?? 맞나
                    $item['fileTypeId'] = File::FileType_Pdf;
                } else {
                    //$item['fileType'] = 'file';
                    //$item['fileType'] = substr($ext, 1); // 확장자로 해야하나? 체크 하는방법은?
                    $item['fileType'] = strtolower($originFileInfo['extension']);
                    $item['fileTypeId'] = File::FileType_File;
                }
            }

            $timeConst = 1;
            while (true){
                //$randomFilename = substr(md5(time() + $timeConst), 3, 10);
                $randomFilename = Util::generateRandomAlphanumericLowercase(10);
                $fileExist = self::isExist($path, $randomFilename.$ext);
                if($fileExist != true){
                    // 없는 파일 생성 가능
                    break;
                } else {
                    //$timeConst += 22;
                    usleep(10);
                }
            };



            /**
             * write to file
             */


            if(!copy($originFile, $path.$randomFilename.$ext)) {
                throw new Exception('File copy fail : code : 8-1');
            } else if(file_exists($path.$randomFilename.$ext)) {
                //echo "파일 복사 성공";
            } else {
                throw new Exception('File copy fail : code : 8-2');
            }

            $size = filesize($path.$randomFilename.$ext);
            if($size < 1) {
                throw new Exception('File copy fail : code : 8-3');
            }


            // @todo: 추후 개발필요시 적용

            $imagePath = $originFile;
            $imageInfo = getimagesize($originFile);

            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];


            if(false){
                // resize
                if(isset($values['resize']) == true && Util::isInteger($values['resizeWidth'], 0) && Util::isInteger($values['resizeHeight'], 0)){

                    /*$imagePath = $path.$randomFilename.$ext;
                    $imageInfo = getimagesize($imagePath);*/

                    $newWidth = $width = $imageWidth;
                    $newHeight = $height = $imageHeight;


                    // 프로필 최대 사이즈
                    // 가로 세로 동일.
                    $profileImageSize = 120;

                    if($values['resizeCrop'] == 'Y'){
                        $crop = 'Y';
                    } else {
                        $crop = 'N';
                    }


                    if($crop == 'Y'){
                        if($newWidth > $profileImageSize){
                            $newWidth = $profileImageSize;
                            $newHeight = $profileImageSize*$height/$width;
                        }

                        /**
                         * if($newHeight > $profileImageSize){
                        $newWidth = $profileImageSize*$newWidth/$newHeight;
                        $newHeight = $profileImageSize;
                        // width 작아졌는지 체크.
                        if($newWidth < $profileImageSize){
                        $newHeight = $profileImageSize*$newHeight/$newWidth;
                        $newWidth = $profileImageSize;
                        }
                        } else
                         */
                        // 조정한 사이즈가 정사이즈보다 작을경우 다시 키운다.
                        if($newHeight < $profileImageSize){
                            $newWidth = $profileImageSize*$newWidth/$newHeight;
                            $newHeight = $profileImageSize;
                        }

                        $srcX = 0;
                        $srcY = 0;
                        if($newWidth > $profileImageSize) $srcX = ($profileImageSize - $newWidth) / 2 * $width / $newWidth;
                        if($newHeight != $profileImageSize) $srcY = ($profileImageSize - $newHeight) / 2 * $width / $newWidth;

                        $newImage = imagecreatetruecolor($profileImageSize, $profileImageSize);
                        $backgroundColor = imagecolorallocate($newImage, 255, 255, 255);
                        imagefill($newImage, 0, 0, $backgroundColor);



                        // size 작은거처리.

                        $srcX = 0;
                        $srcY = 0;


                        if($width > $height){
                            $srcX = ($width - $height) / 2;
                        } else {
                            $srcY = ($height - $width) / 2;
                        }
                        /*
                                            $ratio = max($profileImageSize/$newWidth, $profileImageSize/$newHeight);
                                            $newHeight = $height / $ratio;
                                            $x = ($newWidth - $width / $ratio) / 2;
                                            $newWidth = $width / $ratio;

                                            ($w - $width)/2, ($h - $height)/2,*/
                        switch ($imageInfo['mime']){
                            case 'image/jpeg':
                                $originalImage = imagecreatefromjpeg($imagePath);
                                imagecopyresampled($newImage, $originalImage, 0, 0, $srcX, $srcY, $profileImageSize, $profileImageSize, $width-$srcX*2, $height-$srcY*2);
                                //                   ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
                                //0, 0, ($originalW - $width)/2, ($originalH - $height)/2, $width, $height, $w, $h);    $srcX - (($srcX - $width)/2), $srcY - (($srcY - $height)/2)
                                imagejpeg($newImage, $imagePath);
                                break;
                            case 'image/png':
                                $originalImage = imagecreatefrompng($imagePath);
                                imagecopyresampled($newImage, $originalImage, 0, 0, $srcX, $srcY, $profileImageSize, $profileImageSize, $width-$srcX*2, $height-$srcY*2);
                                imagepng($newImage, $imagePath);
                                break;
                            case 'image/gif':
                                $originalImage = imagecreatefromgif($imagePath);
                                imagecopyresampled($newImage, $originalImage, 0, 0, $srcX, $srcY, $profileImageSize, $profileImageSize, $width-$srcX*2, $height-$srcY*2);
                                imagegif($newImage, $imagePath);
                                break;
                            case 'image/bmp':
                                $originalImage = imagecreatefromwbmp($imagePath);
                                imagecopyresampled($newImage, $originalImage, 0, 0, $srcX, $srcY, $profileImageSize, $profileImageSize, $width-$srcX*2, $height-$srcY*2);
                                imagewbmp($newImage, $imagePath);
                                break;
                        }
                    } else {
                        if($newWidth > $profileImageSize){
                            $newWidth = $profileImageSize;
                            $newHeight = $profileImageSize*$height/$width;
                        }

                        if($newHeight > $profileImageSize){
                            $newHeight = $profileImageSize;
                            $newWidth = $profileImageSize*$width/$height;
                        }

                        $dstX = 0;
                        $dstY = 0;
                        if($newWidth != $profileImageSize) $dstX = ($profileImageSize - $newWidth) / 2;
                        if($newHeight != $profileImageSize) $dstY = ($profileImageSize - $newHeight) / 2;

                        $newImage = imagecreatetruecolor($profileImageSize, $profileImageSize);
                        $backgroundColor = imagecolorallocate($newImage, 255, 255, 255);
                        imagefill($newImage, 0, 0, $backgroundColor);



                        switch ($imageInfo['mime']){
                            case 'image/jpeg':
                                $originalImage = imagecreatefromjpeg($imagePath);
                                imagecopyresampled($newImage, $originalImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $width,$height);
                                imagejpeg($newImage, $imagePath);
                                break;
                            case 'image/png':
                                $originalImage = imagecreatefrompng($imagePath);
                                imagecopyresampled($newImage, $originalImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $width,$height);
                                imagepng($newImage, $imagePath);
                                break;
                            case 'image/gif':
                                $originalImage = imagecreatefromgif($imagePath);
                                imagecopyresampled($newImage, $originalImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $width,$height);
                                imagegif($newImage, $imagePath);
                                break;
                            case 'image/bmp':
                                $originalImage = imagecreatefromwbmp($imagePath);
                                imagecopyresampled($newImage, $originalImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $width,$height);
                                imagewbmp($newImage, $imagePath);
                                break;
                        }
                    }

                }
                // end of resize
            }



            switch($ext){
                case '.pdf':
                    $item['fileType'] = 'pdf';
                    $uploadFileType = 'pdf';
                    $uploadFileTypeId = File::FileType_Pdf;
                    break;
                case '.jpg':
                case '.jpeg':
                case '.gif':
                case '.png':
                case '.bmp':
                    $item['fileType'] = 'image';
                    $uploadFileType = substr($ext, 1);
                    $uploadFileTypeId = File::FileType_Image;
                    break;
                default:
                    $item['fileType'] = 'file';
                    $uploadFileType = substr($ext, 1);
                    $uploadFileTypeId = File::FileType_File;
                    break;

            }

            // 위에서 계산
            //$item['type']         = 'image';

            switch ($allowFileType){
                case 'image':
                    $allowFileTypeId = File::FileType_Image;
                    break;
                case 'pdf':
                    $allowFileTypeId = File::FileType_Pdf;
                    break;
                case 'file':
                default:
                    $allowFileTypeId = File::FileType_File;
                    break;
            }

            $item['uploaderType']    = $uploaderType;
            $item['uploaderId']      = $uploaderId;
            $item['uploaderToken']   = Util::generateRandomAlphanumeric(10);
            $item['allowFileTypeId'] = $allowFileTypeId;
            $item['external']        = 'N';
            $item['externalType']    = ''; // 외부링크 가져오는게 아니기 때문
            $item['externalLink']    = $url;
            $item['provider']        = $provider;
            $item['caption']         = $fileCaption;
            $item['fileTypeId']      = $uploadFileTypeId;
            $item['fileType']        = $uploadFileType;
            $item['fileName']        = $filename;
            $item['yearMonth']       = $uploadYearMonth;
            $item['randomName']      = $randomFilename;
            $item['size']            = $size;
            $item['ext']             = $ext;
            $item['download']        = 0;
            $item['regIp']           = CLIENT_IP;
            $item['regDate']         = Util::getDbNow();
            $item['regTimestamp']    = Util::getLocalTime();
            $item['status']          = File::Status_Active;

            $ret = $this->addNew($item);

            if(is_numeric($ret) == true && $ret >= 1){
                //echo "업로드 성공 : ".$set_file."<br>";
                chmod($path . $randomFilename . $ext , 0644);
                // DB 업로드성공.

                // thumbnail generate
                if(isset($values['thumbnail']) == true && $values['thumbnail'] == 'Y'){

                    if(isset($values['thumbnailType']) == true && $values['thumbnailType'] != ''){
                        self::brandNewImage($path, $randomFilename, $ext, $values['thumbnailType']);
                    }

                }


                $response                      = array();
                $response['error']             = 0;
                $response['status']            = 200;
                $response['id']                = $ret;
                $response['ext']               = $ext;
                $response['width']             = $imageWidth;
                $response['height']            = $imageHeight;
                $response['caption']           = $fileCaption;
                $response['provider']          = $provider;
                $response['itemCaption']       = $itemCaption;
                $response['type']              = $item['fileType'];  //??
                $response['uploadFileName']    = $filename;
                $response['uploadedFileName']  = $randomFilename;
                $response['randomName']        = $randomFilename;
                $response['uploaderToken']     = $item['uploaderToken'];
                $response['yearMonth']         = $item['yearMonth'];
                $response['yearMonthUrl']      = '/' . $uploadYearMonth . '/';
                $response['fullUrl']           = '/' . $uploadYearMonth . '/' . $randomFilename . $ext;

            } else {
                // DB입력 실패시 파일 삭제.
                exec("rm -rf $path$randomFilename$ext");
                $response            = array();
                $response['error']   = 1;
                $response['status']  = 200;
                $response['message'] = 'DB Update Fail';
            }


        } catch (Exception $e) {

            /*if($path != '' && $randomFilename != '' && $ext != ''){
                exec("rm -f $path$randomFilename$ext");
            }*/

            $response            = array();
            $response['error']   = 1;
            $response['message'] = $e->getMessage();
        }
        return $response;

    }


    // 썸테일 사이즈 설정해야한다.
    public static function getThumbnailSize($type = 'article'){
        /**
         * typeId 1 : article
         */
        $data =  array(
            // @fixme: Soft 용으로 만들어야한다.

            // Soft Web


            // Soft Mobile

            [500,0],


/*            // legacy


            [367,0],
            [110,74],

            // [60,60], - 인물이미지

            //[162,121], --
            [186,121],
            [70,46],
            [216,142],
            //[1024,0], v1에서 생성
            [57,70],
            [192,136],
            [200,140],

            /**
             * Econotimes v2 mobile
             * /


            [112,75],

            // [65,65], - 인물이미지

            //[500,0],
            //[112,75],
            //[70,46],
            [90,64],
            //[500,0],
            // [70,70], - 인물이미지
            //[500,0],
            [94,64],
            /**
             * Layout 1
             * /
            [625,0],
            [150,90],
            [105,63],

            [570,0],
            [570,308],*/
        );
        if(SITE_BRANCH_ID == 21){
            $data = array_merge($data, array(

                // 여시재
                [634, 326],
                [188, 119],
                [318, 200],
                [215, 136] // GNB 이벤트 리스트

            ));
        }

        return $data;
    }

    public function brandNewImage($path, $randomFilename, $ext, $type = 'article'){
        /**
         * 공통으로 이미지 썸네일 생성
         */

        if(strpos($randomFilename, '_th')){
            // source가 썸네일이면 패스
            return;
        }

        foreach(self::getThumbnailSize($type) as $var){
            self::generateImageThumbnail($path, $randomFilename.$ext, $randomFilename.'_th_' . $var[0] .'x' . $var[1] .$ext, $var[0], $var[1]);
        }
    }

    public static function generateImageThumbnail($path, $sourceImageName, $thumbnailImageName, $maxWidth, $maxHeight){

        if(self::isExist($path, $thumbnailImageName) == true){
            // 파일이 이미 있을경우 패스
            return true;
        }

        if(self::isExist($path, $sourceImageName) != true){
            // 기본 파일이 없을경우 패스
            return false;
        }

        $sourceGdImage = false;
        list($sourceImageWidth, $sourceImageHeight, $sourceImageType) = getimagesize($path.$sourceImageName);
        switch ($sourceImageType) {
            case IMAGETYPE_BMP:
                $sourceGdImage = self::imagecreatefrombmp($path.$sourceImageName);
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

    public static function generateCropImageThumbnail($path, $sourceImageName, $thumbnailImageName, $thumbnailWidth = 0, $thumbnailHeight = 0, $extractX = 0, $extractY = 0){

        if(self::isExist($path, $thumbnailImageName) == true){
            // 파일이 이미 있을경우 패스
            return true;
        }

        if(self::isExist($path, $sourceImageName) != true){
            // 기본 파일이 없을경우 패스
            return false;
        }

        $sourceGdImage = false;
        list($sourceImageWidth, $sourceImageHeight, $sourceImageType) = getimagesize($path.$sourceImageName);
        switch ($sourceImageType) {
            case IMAGETYPE_BMP:
                $sourceGdImage = self::imagecreatefrombmp($path.$sourceImageName);
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

        $thumbnailGdImage = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

        // 원본 이미지의 X, Y에서 시작해서 thumbnailWidth만큼의 너비와 thumbnailHeight만큼 잘라냄.
        // crop 썸네일은 새로 이미지를 만드므로 0, 0에서 시작해서 잘라낸 이미지 길이인 thumbnailWidth만큼의 너비와 thumbnailHeight만큼의 높이를 가짐.
        imagecopyresampled($thumbnailGdImage, $sourceGdImage, 0, 0,  $extractX, $extractY ,$thumbnailWidth, $thumbnailHeight, $thumbnailWidth, $thumbnailHeight);
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

    public function imagecreatefrombmp($p_sFile){
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



    /**
     * 파일이 있을때 true가 return 됨을 한번더 확인.
     * @param string $path
     * @param $fileName
     * @return bool
     */
    public static function isExist($path = '/var/www/uploads/' . SITE_BRANCH_CODE, $fileName){
        if(file_exists($path . $fileName)){
            return true;
        }
        return false;
    }



}

<?php
namespace PL\Models\Client\Point\Transaction;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Exchange\Token\Token as ExchangeToken;
use PL\Models\Exchange\Token\Container as ExchangeTokenContainer;

use PL\Models\Admin\Admin;
use PL\Models\Admin\Container as AdminContainer;
use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;
use PL\Models\Client\Point\Point as ClientPoint;
use PL\Models\Client\Point\Transaction\Log\Container as TransactionLogContainer;


class Transaction extends AbstractSingleton {


    const Decimal_EOS = 4;
    /**
     * senderLogId - ClientPointTransactionLog id
     * sendPointLogId - 실제 point + - logId
     */
    const Purpose_IO         = 1;
    const Purpose_NewsVoting = 2;
    const Purpose_Event      = 3;

    const Type_Withdrawal = 1;
    const Type_Receive    = 2;
    const Type_Internal   = 3;

    const UserType_Admin    = 1;
    const UserType_Client   = 2;
    const UserType_Bot      = 3;

    const TokenType_Internal = 1;
    const TokenType_External = 2;


    /**
     * 출금망별 PlatformId
     * NEWS-eos NEWS-luk
     */
    const Platform_Internal  = 1;
    const Platform_Publish   = 2;
    const Platform_Eos       = 3;
    const Platform_Luniverse = 4;
    const Platform_Ethereum  = 5;


    // 승인 프로세스 : 1 2 6 7 0
    // 자동 프로세스 : 6 7 0
    // 관리자 승인거절 : 1 3

    const Status_Complete     = 0;
    const Status_Pending      = 1; // 관리자 승인대기 출금대기
    const Status_Approved     = 2; // 관리자가 승인함
    const Status_Rejected     = 3;
    const Status_Error        = 4;
    const Status_Cancel       = 5;

    const Status_NodePending  = 6; // node 대기
    const Status_NodeProgress = 7; // node진행중
    const Status_NodeError    = 8; // node 처리중 에러 retry
    const Status_Initial      = 10;


    const TxStatus_Complete  = 0;
    const TxStatus_Rejected  = 3;
    const TxStatus_Error     = 4;
    const TxStatus_Initial   = 10;

    const tableName = 'ClientPointTransaction';


    protected $_senderInstance;
    protected $_receiverInstance;
    protected $_senderLogInstance;
    protected $_receiverLogInstance;
    protected $_approverInstance;

    protected $_sendTokenInstance;
    protected $_receiveTokenInstance;

    protected $_tokenInstance;

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

    public function getSenderNickname() {
        if($this->getSenderInstance() instanceof Client == true) {
            return $this->getSenderInstance()->getNickname();
        }
        return '';
    }
    public function getSenderInstance() {
        if (isset($this->_senderInstance) == false) {
            $this->_senderInstance = ClientContainer::isItem($this->getSenderId());
        }
        return $this->_senderInstance;
    }

    public function getReceiverInstance() {
        if (isset($this->_receiverInstance) == false) {
            $this->_receiverInstance = ClientContainer::isItem($this->getReceiverId());
        }
        return $this->_receiverInstance;
    }

    public function getSenderLogInstance() {
        if (isset($this->_senderLogInstance) == false) {
            $this->_senderLogInstance = TransactionLogContainer::isItem($this->getSenderLogId());
        }
        return $this->_senderLogInstance;
    }

    public function getReceiverLogInstance() {
        if (isset($this->_receiverLogInstance) == false) {
            $this->_receiverLogInstance = TransactionLogContainer::isItem($this->getReceiverLogId());
        }
        return $this->_receiverLogInstance;
    }


    public function getApproverInstance() {
        if (isset($this->_approverInstance) == false) {
            $this->_approverInstance = AdminContainer::isItem($this->getApproverId());
        }
        return $this->_approverInstance;
    }

    /**
     * ExchangeToken 을 불러온다.
     */
    public function getTokenInstance() {
        if (isset($this->_tokenInstance) == false) {
            $this->_tokenInstance = ExchangeToken::getInstance($this->getTokenId());
        }
        return $this->_tokenInstance;
    }

    public function getSendTokenInstance(){
        //self::TokenType_Internal
    }

    public function getSendTokenUnit(){
        if($this->getSendTokenTypeId() == self::TokenType_Internal){
            switch ($this->getSendTokenId()){
                case ClientPoint::Token_TPC:
                    return 'tpc';
                    break;
                case ClientPoint::Token_News:
                case ClientPoint::Token_NewsSatoshi:
                    return 'news';
                    break;
                case ClientPoint::Token_NewsKrw:
                    return 'nkrw';
                    break;
                default:
                    return 'Unit';
                    break;
            }
        } else {
            $tokenInstance = ExchangeTokenContainer::isItem($this->getSendTokenId());
            if($tokenInstance instanceof ExchangeToken == true) return $tokenInstance->getUnit();
        }
        return 'Unit';
    }

    public function getSendTokenTitle(){
        if($this->getSendTokenTypeId() == self::TokenType_Internal){
            switch ($this->getSendTokenId()){
                case ClientPoint::Token_TPC:
                    return 'TPC';
                    break;
                case ClientPoint::Token_News:
                case ClientPoint::Token_NewsSatoshi:
                    return 'NEWS';
                    break;
                case ClientPoint::Token_NewsKrw:
                    return 'NEWS KRW';
                    break;
                default:
                    return 'Unknown Token';
                    break;
            }
        } else {
            $tokenInstance = ExchangeTokenContainer::isItem($this->getSendTokenId());
            if($tokenInstance instanceof ExchangeToken == true) return $tokenInstance->getTitle();
        }
        return 'Unknown Token';
    }


    public function getReceiveTokenUnit(){
        if($this->getReceiveTokenTypeId() == self::TokenType_Internal){
            switch ($this->getReceiveTokenId()){
                case ClientPoint::Token_TPC:
                    return 'tpc';
                    break;
                case ClientPoint::Token_News:
                case ClientPoint::Token_NewsSatoshi:
                    return 'news';
                    break;
                case ClientPoint::Token_NewsKrw:
                    return 'nkrw';
                    break;
                default:
                    return 'Unit';
                    break;
            }
        } else {
            $tokenInstance = ExchangeTokenContainer::isItem($this->getReceiveTokenId());
            if($tokenInstance instanceof ExchangeToken == true) return $tokenInstance->getUnit();
        }
        return 'Unit';
    }

    public function getReceiveTokenTitle(){
        if($this->getReceiveTokenTypeId() == self::TokenType_Internal){
            switch ($this->getReceiveTokenId()){
                case ClientPoint::Token_TPC:
                    return 'TPC';
                    break;
                case ClientPoint::Token_News:
                case ClientPoint::Token_NewsSatoshi:
                    return 'NEWS';
                    break;
                case ClientPoint::Token_NewsKrw:
                    return 'NEWS KRW';
                    break;
                default:
                    return 'Unknown Token';
                    break;
            }
        } else {
            $tokenInstance = ExchangeTokenContainer::isItem($this->getReceiveTokenId());
            if($tokenInstance instanceof ExchangeToken == true) return $tokenInstance->getTitle();
        }
        return 'Unknown Token';
    }



    public function getId() {
        return $this->_info['id'];
    }

    public function getPurposeId() {
        return $this->_info['purposeId'];
    }

    public function setPurposeId($var) {
        $this->_info['purposeId'] = $var;
        $this->_changes['purposeId'] = $this->_info['purposeId'];
    }

    public function getTypeId() {
        return $this->_info['typeId'];
    }

    public function setTypeId($var) {
        $this->_info['typeId'] = $var;
        $this->_changes['typeId'] = $this->_info['typeId'];
    }


    public function getTypeName() {
        switch ($this->getTypeId()) {
            case self::Type_Withdrawal:
                return 'withdrawal';
                break;
            case self::Type_Receive:
                return 'receive';
                break;
            case self::Type_Internal:
                return 'internal';
                break;
            default:
                return '';
        }
    }


    public function getSenderId() {
        return $this->_info['senderId'];
    }

    public function setSenderId($var) {
        $this->_info['senderId'] = $var;
        $this->_changes['senderId'] = $this->_info['senderId'];
    }

    public function getSenderLogId() {
        return $this->_info['senderLogId'];
    }

    public function setSenderLogId($var) {
        $this->_info['senderLogId'] = $var;
        $this->_changes['senderLogId'] = $this->_info['senderLogId'];
    }

    public function getSenderAddress() {
        return $this->_info['senderAddress'];
    }

    public function setSenderAddress($var) {
        $this->_info['senderAddress'] = $var;
        $this->_changes['senderAddress'] = $this->_info['senderAddress'];
    }

    public function getReceiverId() {
        return $this->_info['receiverId'];
    }

    public function setReceiverId($var) {
        $this->_info['receiverId'] = $var;
        $this->_changes['receiverId'] = $this->_info['receiverId'];
    }

    public function getReceiverLogId() {
        return $this->_info['receiverLogId'];
    }

    public function setReceiverLogId($var) {
        $this->_info['receiverLogId'] = $var;
        $this->_changes['receiverLogId'] = $this->_info['receiverLogId'];
    }

    public function getReceiverAddress() {
        return $this->_info['receiverAddress'];
    }

    public function setReceiverAddress($var) {
        $this->_info['receiverAddress'] = $var;
        $this->_changes['receiverAddress'] = $this->_info['receiverAddress'];
    }

    public function getSendTokenTypeId() {
        return $this->_info['sendTokenTypeId'];
    }

    public function setSendTokenTypeId($var) {
        $this->_info['sendTokenTypeId'] = $var;
        $this->_changes['sendTokenTypeId'] = $this->_info['sendTokenTypeId'];
    }

    public function getSendTokenId() {
        return $this->_info['sendTokenId'];
    }

    public function setSendTokenId($var) {
        $this->_info['sendTokenId'] = $var;
        $this->_changes['sendTokenId'] = $this->_info['sendTokenId'];
    }

    public function getSendTokenPlatformId() {
        return $this->_info['sendTokenPlatformId'];
    }

    public function setSendTokenPlatformId($var) {
        $this->_info['sendTokenPlatformId'] = $var;
        $this->_changes['sendTokenPlatformId'] = $this->_info['sendTokenPlatformId'];
    }

    public function getSendQuantity() {
        return $this->_info['sendQuantity'];
    }

    public function setSendQuantity($var) {
        $this->_info['sendQuantity'] = $var;
        $this->_changes['sendQuantity'] = $this->_info['sendQuantity'];
    }

    public function getSendFee() {
        return $this->_info['sendFee'];
    }

    public function setSendFee($var) {
        $this->_info['sendFee'] = $var;
        $this->_changes['sendFee'] = $this->_info['sendFee'];
    }

    public function getSendTokenUsdRate() {
        return $this->_info['sendTokenUsdRate'];
    }

    public function setSendTokenUsdRate($var) {
        $this->_info['sendTokenUsdRate'] = $var;
        $this->_changes['sendTokenUsdRate'] = $this->_info['sendTokenUsdRate'];
    }

    public function getSendPointLogId() {
        return $this->_info['sendPointLogId'];
    }

    public function setSendPointLogId($var) {
        $this->_info['sendPointLogId'] = $var;
        $this->_changes['sendPointLogId'] = $this->_info['sendPointLogId'];
    }

    public function getSendTokenLogId() {
        return $this->_info['sendTokenLogId'];
    }

    public function setSendTokenLogId($var) {
        $this->_info['sendTokenLogId'] = $var;
        $this->_changes['sendTokenLogId'] = $this->_info['sendTokenLogId'];
    }

    public function getReceiveTokenTypeId() {
        return $this->_info['receiveTokenTypeId'];
    }

    public function setReceiveTokenTypeId($var) {
        $this->_info['receiveTokenTypeId'] = $var;
        $this->_changes['receiveTokenTypeId'] = $this->_info['receiveTokenTypeId'];
    }

    public function getReceiveTokenId() {
        return $this->_info['receiveTokenId'];
    }

    public function setReceiveTokenId($var) {
        $this->_info['receiveTokenId'] = $var;
        $this->_changes['receiveTokenId'] = $this->_info['receiveTokenId'];
    }

    public function getReceiveTokenPlatformId() {
        return $this->_info['receiveTokenPlatformId'];
    }

    public function setReceiveTokenPlatformId($var) {
        $this->_info['receiveTokenPlatformId'] = $var;
        $this->_changes['receiveTokenPlatformId'] = $this->_info['receiveTokenPlatformId'];
    }

    public function getReceiveQuantity() {
        return $this->_info['receiveQuantity'];
    }

    public function setReceiveQuantity($var) {
        $this->_info['receiveQuantity'] = $var;
        $this->_changes['receiveQuantity'] = $this->_info['receiveQuantity'];
    }

    public function getReceiveTokenUsdRate() {
        return $this->_info['receiveTokenUsdRate'];
    }

    public function setReceiveTokenUsdRate($var) {
        $this->_info['receiveTokenUsdRate'] = $var;
        $this->_changes['receiveTokenUsdRate'] = $this->_info['receiveTokenUsdRate'];
    }

    public function getReceivePointLogId() {
        return $this->_info['receivePointLogId'];
    }

    public function setReceivePointLogId($var) {
        $this->_info['receivePointLogId'] = $var;
        $this->_changes['receivePointLogId'] = $this->_info['receivePointLogId'];
    }

    public function getReceiveTokenLogId() {
        return $this->_info['receiveTokenLogId'];
    }

    public function setReceiveTokenLogId($var) {
        $this->_info['receiveTokenLogId'] = $var;
        $this->_changes['receiveTokenLogId'] = $this->_info['receiveTokenLogId'];
    }

    public function getTxUrl(){
        return Util::getTxUrl($this->getTxId());
    }

    public function getTxId($limit = 0) {
        if($limit >= 1){
            return substr($this->_info['txId'], 0, $limit);
        }
        return $this->_info['txId'];
    }

    public function setTxId($var) {
        $this->_info['txId'] = $var;
        $this->_changes['txId'] = $this->_info['txId'];
    }

    public function getTxRate() {
        return $this->_info['txRate'];
    }

    public function setTxRate($var) {
        $this->_info['txRate'] = $var;
        $this->_changes['txRate'] = $this->_info['txRate'];
    }

    public function getTxStatusName() {
        switch ($this->getTxStatus()) {
            case self::TxStatus_Complete:
                return 'complete';
                break;
            case self::TxStatus_Rejected:
                return 'rejected';
                break;
            case self::TxStatus_Error:
                return 'error';
                break;
            case self::TxStatus_Initial:
                return 'initial';
                break;


            default:
                return '';
        }
    }

    public function getTxStatus() {
        return $this->_info['txStatus'];
    }

    public function setTxStatus($var) {
        $this->_info['txStatus'] = $var;
        $this->_changes['txStatus'] = $this->_info['txStatus'];
    }

    public function getTxError() {
        return $this->_info['txError'];
    }

    public function setTxError($var) {
        $this->_info['txError'] = $var;
        $this->_changes['txError'] = $this->_info['txError'];
    }

    public function getTxDate($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['txDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setTxDate($var) {
        $this->_info['txDate'] = $var;
        $this->_changes['txDate'] = $this->_info['txDate'];
    }

    public function getTxTime($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['txTime']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setTxTime($var) {
        $this->_info['txTime'] = $var;
        $this->_changes['txTime'] = $this->_info['txTime'];
    }

    public function getTxMessage() {
        return $this->_info['txMessage'];
    }

    public function setTxMessage($var) {
        $this->_info['txMessage'] = $var;
        $this->_changes['txMessage'] = $this->_info['txMessage'];
    }

    public function getPlatform() {
        return $this->_info['platform'];
    }

    public function setPlatform($var) {
        $this->_info['platform'] = $var;
        $this->_changes['platform'] = $this->_info['platform'];
    }

    public function getOfferId() {
        return $this->_info['offerId'];
    }

    public function setOfferId($var) {
        $this->_info['offerId'] = $var;
        $this->_changes['offerId'] = $this->_info['offerId'];
    }

    public function getTransactionId() {
        return $this->_info['transactionId'];
    }

    public function setTransactionId($var) {
        $this->_info['transactionId'] = $var;
        $this->_changes['transactionId'] = $this->_info['transactionId'];
    }

    public function getApproverName() {
        $name = '';
        if($this->getApproverId() >= 1){
            if($this->getApproverInstance() instanceof Admin == true) $name = $this->getApproverInstance()->getName();
        }
        return $name;
    }
    public function getApproverId() {
        return $this->_info['approverId'];
    }

    public function setApproverId($var) {
        $this->_info['approverId'] = $var;
        $this->_changes['approverId'] = $this->_info['approverId'];
    }

    public function getApproverIp() {
        return $this->_info['approverIp'];
    }

    public function setApproverIp($var) {
        $this->_info['approverIp'] = $var;
        $this->_changes['approverIp'] = $this->_info['approverIp'];
    }

    public function getApproverMemo() {
        return $this->_info['approverMemo'];
    }

    public function setApproverMemo($var) {
        $this->_info['approverMemo'] = $var;
        $this->_changes['approverMemo'] = $this->_info['approverMemo'];
    }

    public function getApproveDate($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['approveDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setApproveDate($var) {
        $this->_info['approveDate'] = $var;
        $this->_changes['approveDate'] = $this->_info['approveDate'];
    }

    public function getResult() {
        return $this->_info['result'];
    }

    public function setResult($var) {
        $this->_info['result'] = $var;
        $this->_changes['result'] = $this->_info['result'];
    }

    public function getRegTypeName() {
        switch ($this->getRegTypeId()) {
            case self::UserType_Admin:
                return 'admin';
                break;
            case self::UserType_Client:
                return 'client';
                break;
            case self::UserType_Bot:
                return 'bot';
                break;
            default:
                return '';
                break;
        }
    }

    public function getRegTypeId() {
        return $this->_info['regTypeId'];
    }

    public function setRegTypeId($var) {
        $this->_info['regTypeId'] = $var;
        $this->_changes['regTypeId'] = $this->_info['regTypeId'];
    }

    public function getRegId() {
        return $this->_info['regId'];
    }

    public function setRegId($var) {
        $this->_info['regId'] = $var;
        $this->_changes['regId'] = $this->_info['regId'];
    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function setRegIp($var) {
        $this->_info['regIp'] = $var;
        $this->_changes['regIp'] = $this->_info['regIp'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var) {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getRegTime($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regTime']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegTime($var) {
        $this->_info['regTime'] = $var;
        $this->_changes['regTime'] = $this->_info['regTime'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($var) {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function getFrontStatus($mode = '') {
        $statusNameArray = array();
        switch ($this->getStatus()) {
            case self::Status_Complete:
                $statusNameArray['en'] = 'Complete';
                $statusNameArray['ko'] = '완료';
                break;
            case self::Status_Pending:
            case self::Status_Approved:
            case self::Status_NodePending:
            case self::Status_NodeProgress:
                $statusNameArray['en'] = 'InProgress';
                $statusNameArray['ko'] = '처리중';
                break;
            case self::Status_Rejected:
                $statusNameArray['en'] = 'Rejected';
                $statusNameArray['ko'] = '승인거절';
                break;
            case self::Status_Error:
            case self::Status_NodeError:
                $statusNameArray['en'] = 'Error';
                $statusNameArray['ko'] = '망에러';
                break;
            case self::Status_Cancel:
                $statusNameArray['en'] = 'Cancel';
                $statusNameArray['ko'] = '취소';
                break;
            case self::Status_Initial:
                $statusNameArray['en'] = 'Initial';
                $statusNameArray['ko'] = '거래 초기화';
                break;
            default:
                return '';
        }

        // 현재는 영어, 한국어만 지원
        $languageCode = SITE_LANGUAGE_CODE;

        switch ($languageCode) {
            case 'ko':
                break;
            case 'en':
            default:
                $languageCode = 'en';
                break;
        }
        $statusName = $statusNameArray[$languageCode];
        //$statusName = $statusNameArray[$languageCode][$this->getStatus()];

        if($mode == 'class'){
            return '<span class="status_' . strtolower($statusNameArray['en']) . '">' . $statusName . '</span>';
        } else {
            return $statusName;
        }


    }
    public function getStatusName() {
        switch ($this->getStatus()) {
            case self::Status_Complete:
                return 'complete';
                break;
            case self::Status_Pending:
                return 'approvePending';
                break;
            case self::Status_Approved:
                return 'approved';
                break;
            case self::Status_Rejected:
                return 'rejected';
                break;
            case self::Status_Error:
                return 'error';
                break;
            case self::Status_Cancel:
                return 'cancel';
                break;
            case self::Status_NodePending:
                return 'nodePending';
                break;
            case self::Status_NodeProgress:
                return 'nodeProgress';
                break;
            case self::Status_NodeError:
                return 'nodeError';
                break;
            case self::Status_Initial:
                return 'initial';
                break;


            default:
                return '';
        }
    }

    public function getReceiveQuantityWithNumberFormat($n) {
        return number_format($this->_info['receiveQuantity'], $n);
    }

}
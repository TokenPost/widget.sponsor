<?php
namespace PL\Models\Client\Payment;

use PL\Models\Client\Client;
use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Client\Payment\Payment;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

use PL\Models\Client\Payment\PaypalLog\PaypalLog;
use PL\Models\Client\Payment\Token\Token;

use PL\Models\Client\Group\Group;
use PL\Models\Client\Group\Item\Item as ClientGroupItem;
use PL\Models\Client\Group\Item\Container as ClientGroupItemContainer;

class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(Payment::tableName);
        $this->setTableName(Payment::tableName);
    }

    public static function getTableNameStatic(){
        return Payment::tableName;
    }

    public static function getObjectInstanceStatic($date) : Payment {
        return Payment::getInstance($date);
    }

    public function getObjectInstance($date) : Payment {
        return Payment::getInstance($date);
    }


    public function getClientId(){
        return $this->_clientId;
    }

    private function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance){
        $this->_clientInstance = $clientInstance;
        $this->setClientId($clientInstance->getId());
    }




    public static function isItem($id, $result = ''){
        $query = 'SELECT COUNT(*) FROM ClientPayment';
        if(is_numeric($id) == true){
            $query .= ' WHERE id = ' . $id;
        } elseif(is_string($id)){
            $query .= ' WHERE txnId = "' . $id . '"';
        } else {
            return false;
        }
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query)->fetch();

        if($data[0] == 1) {
            if($result == 'obj') return Payment::getInstance($id);
            return true;
        }
        return false;
    }

    public function getLastPayment($mode = 'all'){
        $filterContainer = new FilterContainer();
        $filterContainer->add(new Filter('clientId', '=', $this->getClientId()));
        $filterContainer->add(new Filter('status', '=', '0'));
        if($mode == 'all'){
            // 현재 적용중인것과무관하게 가져옴.

        } elseif($mode == 'news'){
            $filterContainer->add(new Filter('payment', 'IN', ' ("22.50" , "45.00" , "0.01")'));
        } elseif($mode == 'signal'){
            $filterContainer->add(new Filter('payment', '=', '"100.00"'));
        } elseif($mode == 'expert'){
            $filterContainer->add(new Filter('payment', '=', '"145.00"'));
        } elseif($mode == 'timeCheck'){
            $filterContainer->add(new Filter('endDate', '>', '"' . Util::getDbNow() . '"'));
        }

        $this->setListSize(1);
        $this->setFilterContainer($filterContainer);
        $ret = $this->getItems();

        if(sizeof($ret) != 0){
            return $ret[0];
        } else {
            return 0;
        }

    }

    public static function regPayment($client, $token, $paypalLog, $fp){
        try{
            fwrite($fp, "payment reg start. \n");
            $new = 'N';
            $signalNew = 'N';
            if($paypalLog->getItemName() == 'EconoTimes PRO Expert Subscription'){
                $mode = 'expert';
            } elseif ($paypalLog->getItemName() == 'EconoTimes PRO Signal Subscription'){
                $mode = 'signal';
            } else {
                $mode = 'news';
            }

            fwrite($fp, "mode : " . $mode . " \n");


            if($mode == 'expert' || $mode == 'news'){
                // 유료 구독 시작일 계산.
                if($client->getLoginType() != 'realTime'){
                    // 신규
                    $new = 'Y';
                    $item['startDate'] = Util::getDbNow();
                } else {
                    $new = 'N';
                    $item['startDate'] = $client->getDueDate();
                }
                $item['endDate'] = Util::getDate($item['startDate'], '+1 month');
                if($new == 'Y') $item['endDate'] = Util::getDate($item['endDate'], '+2 day');
            }

            if($mode == 'expert' || $mode == 'signal'){
                // Signal 유료 구독 시작일 계산.
                if($client->getSignalSubscribe() == 'Y'){
                    // 기존 등록 고객
                    $signalNew = 'N';
                    $item['signalStartDate'] = $client->getSignalDueDate();
                } else {
                    // 신규
                    $signalNew = 'Y';
                    $item['signalStartDate'] = Util::getDbNow();

                    $clientGroupItem = ClientGroupItemContainer::isItem($client->getEmail(),'obj', Group::Group_Signalmail);
                    if($clientGroupItem){
                        // 비활성일때만 활성화로 올린다.
                        if($clientGroupItem->getStatus() != ClientGroupItem::Status_Active){
                            $clientGroupItem->setStatus(ClientGroupItem::Status_Active);
                            $clientGroupItem->getGroupInstance()->addCount();
                        }
                    } else {
                        // 목록에 없음. 신규 생성
                        $newClientGroupItem['clientGroupId'] = Group::Group_Signalmail;
                        $newClientGroupItem['clientId']      = $client->getId();
                        $newClientGroupItem['clientEmail']   = $client->getEmail();
                        $newClientGroupItem['status']        = ClientGroupItem::Status_Active;

                        $clientGroupItemContainer = new ClientGroupItemContainer();
                        $newItemRet = $clientGroupItemContainer->addNew($newClientGroupItem);
                        if($newItemRet != false){
                            Group::getInstance(Group::Group_SIGNALMAIL)->addCount();
                        }
                    }
                }
                $item['signalEndDate'] = Util::getDate($item['signalStartDate'], '+1 month');
                if($signalNew == 'Y') $item['signalEndDate'] = Util::getDate($item['signalEndDate'], '+2 day');
            }

            // 결제 정보 확인 및 추후 버튼별 체크를 통하여 분기처리 필요함.


            /*
            if($paypalLog->getMcGross() == '45' || $paypalLog->getMcGross() == '45.00' || $paypalLog->getMcGross() == '0.01' || $paypalLog->getMcGross() == '22.5' || $paypalLog->getMcGross() == '22.50'){
                $item['period'] = 'monthly';
                $item['endDate'] = Util::getDate($item['startDate'], '+1 month');
                if($new == 'Y') $item['endDate'] = Util::getDate($item['endDate'], '+2 day');
            }elseif($paypalLog->getMcGross() == '450' || $paypalLog->getMcGross() == '225'){
                $item['period'] = 'yearly';
                $item['endDate'] = Util::getDate($item['startDate'], '+1 year');
            }
            */

            // 이제는 월별 결제만 작동한다.
            $item['period']         = 'monthly';
            $item['clientId']       = $client->getId();
            $item['clientTokenId']  = $token->getId();
            $item['subscrId']       = $paypalLog->getSubscrId();
            $item['txnId']          = $paypalLog->getTxnId();
            $item['mode']           = $mode;
            $item['currency']       = $paypalLog->getMcCurrency();
            $item['payment']        = $paypalLog->getMcGross();
            $item['paymentDate']    = $paypalLog->getPaymentDateGMT();
            //$item['cancelDate']     = $client->getDueDate();
            $item['regIp']          = getIP();
            $item['regDate']        = Util::getDbNow();
            $item['status']         = 0;

            $db     = DI::getDefault()->getShared('db');
            $result = $db->insert('ClientPayment', array_values($item), array_keys($item));

            fwrite($fp, "result : " . $result . " \n");

            if($result == false){
                // 등록실패
                return false;
            } else {
                // 성공

                $ret = $db->lastInsertId();
                fwrite($fp, "ret : " . $ret . " \n");

                if($mode == 'expert' || $mode == 'news'){
                    $client->setLoginType('realTime');
                    $client->setSubscribe('Y');
                    $client->setDueDate($item['endDate']);
                }

                if($mode == 'expert' || $mode == 'signal'){
                    $client->setSignal('Y');
                    $client->setSignalSubscribe('Y');
                    $client->setSignalDueDate($item['signalEndDate']);
                    // 메일??
                    //$client->setSignalMail('Y');
                }
                $client->saveChanges();
                fwrite($fp, "client saved \n");
                $paypalLog->setClientPaymentId($ret);
                $paypalLog->saveChanges();
                fwrite($fp, "paypal log saved \n");
                $token->setStatus(Token::Status_Success);
                $token->saveChanges();
                fwrite($fp, "token saved \n");

                return $ret;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }


    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }

}

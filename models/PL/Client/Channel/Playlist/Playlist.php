<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019-10-30
 * Time: 오후 3:26
 */

namespace PL\Models\Client\Channel\Playlist;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

class Playlist extends AbstractSingleton {
    const tableName = 'ClientChannelPlaylist';

    protected $_clientInstance;
    protected $_channelInstance;

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

    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getId());
            $this->_clientInstance = ClientContainer::isItem($this->getId());
        }
        return $this->_clientInstance;
    }

    public function getChannelInstance() {
        if (isset($this->_channelInstance) == false) {
            $this->_channelInstance = Channel::getInstance($this->getId());
            $this->_channelInstance = ChannelContainer::isItem($this->getId());
        }
        return $this->_channelInstance;
    }
}
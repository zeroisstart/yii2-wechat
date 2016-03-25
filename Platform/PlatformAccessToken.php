<?php

namespace niancode\wechat\Platform;

use Yii;
use yii\base\Event;
use yii\base\Component;
use niancode\wechat\Util\Http;
use niancode\wechat\Util\Cache;

class PlatformAccessToken extends Component
{
    /**
     * 公众号对象
     */
    protected $wechat;

    /**
     * Access Token
     */
    protected $access_token;

    /**
     * 缓存对象
     */
    protected $cache;

    /**
     * 构造方法
     */
    public function __construct($token, $wechat)
    {
        $this->access_token = $token;
        $this->wechat = $wechat;
        $this->cache = Yii::$app->dbCache;
    }

    /**
     * 获取 Wechat 对象
     */
    public function getWechat()
    {
        return $this->wechat;
    }

    /**
     * 获取 Cache 对象
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * 获取 AccessToken
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }
}

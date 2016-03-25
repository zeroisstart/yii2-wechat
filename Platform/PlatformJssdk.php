<?php

namespace niancode\wechat\Platform;

use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;
use niancode\wechat\Platform\Prpcrypt;
use niancode\wechat\Util\Http;
use niancode\wechat\Jssdk;
use niancode\wechat\Platform\PlatformAccessToken;

class PlatformJssdk extends Component
{
	/**
	 * 第三方平台公众号信息
	 *
	 * @var string
	 **/
	public $wechat;

	/**
	 * Access token
	 *
	 * @var string
	 **/
	public $auth_access_token;

	/**
     * 构造方法
     */
    public function __construct($token, $wechat)
    {
        $this->auth_access_token = $token;
        $this->wechat = $wechat;
    }

	/**
	 * 获取jssdk信息
	 *
	 * @return void
	 * @author
	 **/
	public function getJssdkInfo()
	{
		$token_model = new PlatformAccessToken($this->auth_access_token, $this->wechat);
		$jssdk = new Jssdk($token_model);
		$jssdk->addApi('onMenuShareAppMessage')
			->addApi('onMenuShareTimeline')
			->addApi('chooseWXPay');
		$jssdk->enableDebug();
		return $jssdk->getConfig(true);
	}
}
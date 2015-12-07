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

class PlatformAuth extends Component
{

	/**
	 * 微信的基础URL
	 *
	 * @var string
	 **/
	const WECHAT_BASE_URL = "https://api.weixin.qq.com/";

	/**
	 * 微信code
	 *
	 * @var string
	 **/
	var $appid;

	/**
	 * 微信code
	 *
	 * @var string
	 **/
	var $code;

	/**
	 * 微信component_appid
	 *
	 * @var string
	 **/
	var $component_appid;

	/**
	 * 微信component_access_token
	 *
	 * @var string
	 **/
	var $component_access_token;

	/**
	 * 微信scope
	 *
	 * @var string
	 **/
	var $scope = 'snsapi_userinfo';

     /**
     * 缓存对象
     *
     * @var string
     **/
    public $cache;

	/**
	 * 初始化方法
	 *
	 * @return void
	 * @author
	 **/
	public function __construct($data)
	{
        $this->cache = Yii::$app->cache;
		$this->appid = $data['appid'];
 		$this->code = $data['code'];
 		$this->component_appid = $data['component_appid'];
 		$this->component_access_token = $data['component_access_token'];

 		// Scope
 		if (isset($data['scope']) && $data['scope']) {
 			$this->scope = $data['scope'];
 		}
	}

	/**
     * 微信代发起授权，通过code获取AccessToken
     *
     * @return void
     * @author
     **/
    public function getAccessToken($show_token = true)
    {
    	$data = $this->cache->get("niancode/wechat/auth/getAccessToken/{$this->appid}");
    	if ($data === false) {
    	    $url = self::WECHAT_BASE_URL . "sns/oauth2/component/access_token?";
    	    $params = [
    	    	'grant_type' => "authorization_code",
	    	    'appid' => $this->appid,
	    	    'code' => $this->code,
    	        'component_appid' => $this->component_appid,
    	        'component_access_token' => $this->component_access_token,
    	    ];
            $url .= http_build_query($params);
    	    $request = Http::get($url);
    	    $response = $request->json();
    	    if(array_key_exists('access_token', $response)) {
    	        $res = $request->json(['object' => true]);
    	        $access_token = $res->access_token;
    	        if ($res->access_token) {
    	            $data = new \stdClass();
    	            $data->access_token = $access_token;
                    $data->refresh_token = $res->refresh_token;
    	            $data->openid = $res->openid;
    	            $this->cache->set("niancode/wechat/auth/getAccessToken/{$this->appid}", $data, 7000);
    	            return $show_token ? $access_token : $data;
    	        }
    	    }
    	} else {
    	    return $show_token ? $data->access_token : $data;
    	}
    }

    /**
     * 刷新FreshToken
     *
     * @return void
     * @author
     **/
    public function refreshAccessToken()
    {
    	$data = $this->cache->get("niancode/wechat/auth/refreshAccessToken/{$this->appid}");
    	if ($data === false) {
    	    $url = self::WECHAT_BASE_URL . "sns/oauth2/component/refresh_token/{$this->appid}";
    	    $access_token = $this->getAccessToken();
    	    $post = [
	    	    'appid' => $this->appid,
	    	    'refresh_token' => $access_token->refresh_token,
    	        'component_appid' => $this->component_appid,
    	        'component_access_token' => $this->component_access_token,
    	    ];
    	    $request = Http::get($url, $post);
    	    $response = $request->json();
    	    if(array_key_exists('access_token', $response)) {
    	        $res = $request->json(['object' => true]);
    	        $access_token = $res->access_token;
    	        if ($res->access_token) {
    	            $data = new \stdClass();
    	            $data->access_token = $access_token;
    	            $data->refresh_token = $res->refresh_token;
    	            $this->cache->set("niancode/wechat/auth/refreshAccessToken/{$this->appid}", $data, 7000);
    	            return $show_token ? $access_token : $data;
    	        }
    	    }
    	} else {
    	    return $show_token ? $data->access_token : $data;
    	}
    }


    /**
     * 获取SNS用户信息
     *
     * @return void
     * @author
     **/
    public function getSnsApiUserinfo()
    {
        $data = $this->cache->get("niancode/wechat/auth/getSnsApiUserinfo/{$this->appid}");
        if ($data === false) {
            $access_token = $this->getAccessToken(false);
            $url = self::WECHAT_BASE_URL . "sns/userinfo?";
            $params = [
                'lang' => 'zh_CN',
                'access_token' => $access_token->access_token,
                'openid' => $access_token->openid,
            ];
            $url .= http_build_query($params);
            $request = Http::get($url);
            $response = $request->json();
            if(array_key_exists('openid', $response)) {
                $res = $request->json(['object' => true]);
                if ($res->openid) {
                    $data = new \stdClass();
                    $data->openid = $res->openid;
                    $data->nickname = $res->nickname;
                    $data->sex = $res->sex;
                    $data->province = $res->province;
                    $data->city = $res->city;
                    $data->country = $res->country;
                    $data->headimgurl = $res->headimgurl;
                    $data->privilege = $res->privilege;
                    $data->unionid = isset($res->unionid) ? $res->unionid : '';
                    $this->cache->set("niancode/wechat/auth/getSnsApiUserinfo/{$this->appid}", $data, 7000);
                    return $data;
                }
            }
        } else {
            return $data;
        }
    }


}
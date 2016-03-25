<?php

namespace niancode\wechat;

use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;
use niancode\wechat\Platform\Platform;
use niancode\wechat\Platform\PlatformAuth;
use niancode\wechat\Platform\PlatformJssdk;
use niancode\wechat\Pay\WxJsApiPay;
use niancode\wechat\Pay\Lib\WxPayApi;
use niancode\wechat\Pay\Lib\WxPayUnifiedOrder;
use niancode\wechat\Pay\WxPayCallBack;

class Wechat extends Component
{

    /**
     * undocumented class variable
     *
     * @var string
     **/
    public static $instance;

    public static $pay_instance;

    public static $pay_order_instance;

    public static $pay_api_instance;

    public static $pay_callback_instance;

    /**
     * 第三方平台instance
     *
     * @return void
     * @author niancode
     **/
    public function getPfInstance($config)
    {
        if( is_null(self::$instance) ) {
            self::$instance = new Platform($config);
        }
        return self::$instance;
    }

    /**
     * 第三方平台代授权instance
     *
     * @return void
     * @author
     **/
    public function getPfAuthInstance($appid, $code, $component_appid, $component_access_token, $scope = 'snsapi_userinfo')
    {
        $config['appid'] = $appid;
        $config['code'] = $code;
        $config['component_appid'] = $component_appid;
        $config['component_access_token'] = $component_access_token;
        $config['scope'] = $scope;
        return new PlatformAuth($config);
    }

    /**
     * 第三方平台代JSSDK instance
     *
     * @return void
     * @author
     **/
    public function getPfJssdkInstance($appid, $auth_access_token)
    {
        $config['appid'] = $appid;
        $config['auth_access_token'] = $auth_access_token;
        return new PlatformJssdk($auth_access_token, $config);
    }

    /**
     * 微信支付接口
     *
     * @return void
     * @author
     **/
    public function getPayInstance()
    {
        if( is_null(self::$pay_instance) ) {
            self::$pay_instance = new WxJsApiPay();
        }
        return self::$pay_instance;
    }

    /**
     * undocumented function
     *
     * @return void
     * @author
     **/
    public function getWxOrderInstance()
    {
        if( is_null(self::$pay_order_instance) ) {
            self::$pay_order_instance = new WxPayUnifiedOrder();
        }
        return self::$pay_order_instance;
    }

    /**
     * undocumented function
     *
     * @return void
     * @author
     **/
    public function getWxPayApiInstance()
    {
        if( is_null(self::$pay_api_instance) ) {
            self::$pay_api_instance = new WxPayApi();
        }
        return self::$pay_api_instance;
    }

    /**
     * undocumented function
     *
     * @return void
     * @author
     **/
    public function getWxPayCallBackInstance()
    {
        if( is_null(self::$pay_callback_instance) ) {
            self::$pay_callback_instance = new WxPayCallBack();
        }
        return self::$pay_callback_instance;
    }
}

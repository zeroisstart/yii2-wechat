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

class Wechat extends Component
{

    /**
     * undocumented class variable
     *
     * @var string
     **/
    public static $instance;

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
}

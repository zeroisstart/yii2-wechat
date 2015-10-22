<?php

namespace niancode\wechat;

use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;
use niancode\wechat\Platform\Platform;

class Wechat extends Component
{

    /**
     * undocumented class variable
     *
     * @var string
     **/
    public static $instance;

    /**
     * undocumented function
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
}

<?php

namespace niancode\wechat\Pay\Lib;

use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;
use niancode\wechat\Util\Http;

/**
 *
 * 微信支付API异常类
 * @author widyhu
 *
 */
class WxPayException extends InvalidParamException {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}

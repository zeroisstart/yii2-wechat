<?php
namespace niancode\wechat\Pay;

use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;
use niancode\wechat\Util\Http;
use niancode\wechat\Pay\Lib\WxPayApi;
use niancode\wechat\Pay\Lib\WxPayConfig;
use niancode\wechat\Pay\Lib\WxPayNotify;
use niancode\wechat\Pay\Lib\WxPayOrderQuery;

class WxPayCallBack extends WxPayNotify
{
	public $order = null;

	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);

		// debug
        Yii::error("\n====================start payorder===================");
        Yii::error($result);
        Yii::error("\n====================end payorder===================");

		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			$this->order = $result;
			return true;
		}
		return false;
	}

	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		// debug
        Yii::error("\n====================start pay_call_back===================");
        Yii::error($data);
        Yii::error("\n====================end pay_call_back===================");

		$notfiyOutput = array();

		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author
	 **/
	public function doPayCallBack()
	{
		$this->Handle(false);
		return $this->order;
	}
}
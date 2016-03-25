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

class Platform extends Component
{
    /**
     * 微信接口基本地址
     */
    const WECHAT_BASE_URL = 'https://api.weixin.qq.com/';

    /**
     * 配置文件
     *
     * @var string
     **/
    public $config;

    /**
     * 缓存对象
     *
     * @var string
     **/
    public $cache;

    /**
     * Construct
     *
     * @return void
     * @author niancode
     **/
    public function __construct($config)
    {
        $this->config = $config;
        $this->cache = Yii::$app->redisCache;
    }

    /**
     * 清除缓存
     *
     * @return void
     * @author
     **/
    public function clearCache($name = null)
    {
        if ($name) {
            $this->cache->delete($name);
        } else {
            $this->cache->delete('niancode/wechat/getVerifyTicket');
            $this->cache->delete('niancode/wechat/getComponentAccessToken');
            $this->cache->delete('niancode/wechat/getPreAuthCode');
            $this->cache->delete('niancode/wechat/getAuthorizerAccessToken');
        }
    }

    /**
     * 获取authorizer_info
     *
     * @return void
     * @author
     **/
    public function getAuthorizerInfo($authinfo = true)
    {
        $accessToken = $this->getComponentAccessToken();
        $authorizer_info = $this->getAuthorizerAccessToken(null, false);
        $url = self::WECHAT_BASE_URL . "cgi-bin/component/api_get_authorizer_info?component_access_token={$accessToken}";
        $post = [
            'component_appid' => $this->config->appId,
            'authorizer_appid' => $authorizer_info->authorizer_appid,
        ];

        $request = Http::post($url, ['json' => $post]);
        $response = $request->json();
        if (array_key_exists('authorizer_info', $response)) {
            $res = $request->json(['object' => true]);
            return $authinfo ? $res->authorizer_info : $res;
        }
    }

    /**
     * 获取authorizer_access_token
     *
     * @return void
     * @author
     **/
    public function getAuthorizerAccessToken($authcode = null, $show_token = true, $authorizer = null)
    {
        $cache_key = "niancode/wechat/getAuthorizerAccessToken";
        $data = $this->cache->get($cache_key);
        if ($data === false) {
            $accessToken = $this->getComponentAccessToken();
            if ($authcode) {
                $url = self::WECHAT_BASE_URL. "cgi-bin/component/api_query_auth?component_access_token=$accessToken";
                $post = [
                    'component_appid' => $this->config->appId,
                    'authorization_code' => $authcode,
                ];
                $request = Http::post($url, ['json' => $post]);
                $response = $request->json();
                if (array_key_exists('authorization_info', $response)) {
                    $res = $request->json(['object' => true]);
                    $res = $res->authorization_info;
                    $authorizer_access_token = $res->authorizer_access_token;
                    if ($authorizer_access_token) {
                        $data = new \stdClass();
                        $data->authorizer_appid = $res->authorizer_appid;
                        $data->authorizer_refresh_token = $res->authorizer_refresh_token;
                        $data->authorizer_access_token = $authorizer_access_token;
                        $data->authorizer_all = $response;
                        $this->cache->set($cache_key, $data, 7000);
                        return $show_token ? $authorizer_access_token : $res;
                    }
                }
            } else {
                return $this->_getAuthorizerRefreshToken($cache_key, $authorizer, $show_token);
            }
        } else {
            return $show_token ? $data->authorizer_access_token : $data;
        }
    }

    /**
     * 获取刷新令牌
     *
     * @return void
     * @author
     **/
    private function _getAuthorizerRefreshToken($cache_key, $authorizer, $show_token = true)
    {
        $accessToken = $this->getComponentAccessToken();
        $url = self::WECHAT_BASE_URL. "cgi-bin/component/api_authorizer_token?component_access_token=$accessToken";
        $post = [
            'component_appid' => $this->config->appId,
            'authorizer_appid' => $authorizer->authorizer_appid,
            'authorizer_refresh_token' => $authorizer->authorizer_refresh_token,
        ];
        $request = Http::post($url, ['json' => $post]);
        $response = $request->json();
        if (array_key_exists('authorizer_access_token', $response)) {
            $res = $request->json(['object' => true]);
            $authorizer_access_token = $res->authorizer_access_token;
            if ($authorizer_access_token) {
                $data = new \stdClass();
                $data->component_appid = $this->config->appId;
                $data->authorizer_appid = $authorizer->authorizer_appid;
                $data->authorizer_refresh_token = $res->authorizer_refresh_token;
                $data->authorizer_access_token = $authorizer_access_token;
                $this->cache->set($cache_key, $data, 7000);
                return $show_token ? $authorizer_access_token : $data;
            }
        }
    }

    /**
     * 获取pre_auth_code
     *
     * @return void
     * @author
     **/
    public function getPreAuthCode()
    {
        $data = $this->cache->get("niancode/wechat/getPreAuthCode");
        if ($data === false) {
            $accessToken = $this->getComponentAccessToken();
            $url = self::WECHAT_BASE_URL . "cgi-bin/component/api_create_preauthcode?component_access_token?type=jsapi&component_access_token=$accessToken";
            $post = ['component_appid' => $this->config->appId];
            $request = Http::post($url, ['json' => $post]);
            $response = $request->json();
            if (array_key_exists('pre_auth_code', $response)) {
                $res = $request->json(['object' => true]);
                $pre_auth_code = $res->pre_auth_code;
                if ($pre_auth_code) {
                    $data = new \stdClass();
                    $data->pre_auth_code = $pre_auth_code;
                    $this->cache->set("niancode/wechat/getPreAuthCode", $data, 600);
                    return $pre_auth_code;
                }
            }
        } else {
           return $data->pre_auth_code;
        }
    }

    /**
     * 获取component_access_token
     *
     * @return void
     * @author
     **/
    public function getComponentAccessToken($text = null)
    {
        $data = $this->cache->get("niancode/wechat/getComponentAccessToken");
        if ($data === false) {
            $url = self::WECHAT_BASE_URL . "cgi-bin/component/api_component_token";
            $post = [
                'component_appid' => $this->config->appId,
                'component_appsecret' => $this->config->appSecret,
                'component_verify_ticket' => $this->getVerifyTicket($text, false),
            ];

            $request = Http::post($url, ['json' => $post]);
            $response = $request->json();
            if(array_key_exists('component_access_token', $response)) {
                $res = $request->json(['object' => true]);
                $access_token = $res->component_access_token;
                if ($access_token) {

                    // 写入日志
                    try {
                        $info['component_access_token'] = $access_token;
                        $info['verify_ticket'] = $text;
                        $new_text = sprintf("%s >>> %s\n", date('Y-m-d H:i:s'), var_export($info, true));
                        file_put_contents(Yii::getAlias('@runtime/logs/platform_access_token.log'), $new_text, FILE_APPEND);
                    } catch (ErrorException $e) {
                        Yii::warning("Token日志写入错误");
                    }

                    $data = new \stdClass();
                    $data->access_token = $access_token;
                    $this->cache->set("niancode/wechat/getComponentAccessToken", $data, 6000);
                    return $access_token;
                }
            }
        } else {
            return $data->access_token;
        }
    }

    /**
     * 解密XML
     *
     * @return void
     * @author
     **/
    public function getVerifyTicket($text = null, $cache = true)
    {
        if ($text) {
            $xml_tree = new \DOMDocument();
            $xml_tree->loadXML($text);
            $array_e = $xml_tree->getElementsByTagName('Encrypt');
            $encrypt = $array_e->item(0)->nodeValue;

            $pc = new Prpcrypt($this->config->encodingAesKey);
            $result = $pc->decrypt($encrypt, $this->config->appId);
            if ($result[0] != 0) {
                return $result[0];
            }
            $msg = $result[1];

            if ($msg) {
                $xml_tree = new \DOMDocument();
                $xml_tree->loadXML($msg);
                $ticket_e = $xml_tree->getElementsByTagName('ComponentVerifyTicket');
                $verify_ticket = $ticket_e->item(0)->nodeValue;
                $data = new \stdClass();
                $data->verify_ticket = $verify_ticket;
                // $this->cache->set("niancode/wechat/getVerifyTicket", $data, 500);
                return $verify_ticket;
            }
        }
        // $data = $this->cache->get("niancode/wechat/getVerifyTicket");
        // if ($data === false || $cache === false) {
        // } else {
        //     return $data->verify_ticket;
        // }
    }
}

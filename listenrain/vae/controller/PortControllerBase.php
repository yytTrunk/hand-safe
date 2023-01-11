<?php
// +----------------------------------------------------------------------
// | vaeThink [ Programming makes me happy ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.vaeThink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 听雨 < 389625819@qq.com >
// +----------------------------------------------------------------------
namespace vae\controller;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use app\port\model\User;
use think\Config;
use think\response\Json;
use vae\controller\ControllerBase;

class PortControllerBase extends ControllerBase
{
    const CODE_SUCCESS = 200,CODE_FAIL = 400;
    protected $rows;
    protected $page;
    protected $field;
    public $baseImg = 'https://safe.61kids.com.cn/';

    public function _initialize()
    {
        parent::_initialize();
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $this->rows = !empty($this->param('rows')) ? $this->param('rows') : 5;
        $this->page = !empty($this->param('page')) ? $this->param('page') : 1;
        $this->field = !empty($this->param('field')) ? json_decode($this->param('field'),true) : '*';
        $param = $this->param();
        vae_set_hook('port_begin',$param);
        //鉴权
//        $this->auth();
    }

    protected static function port($code=1, $msg="OK", $data=[], $url='', $httpCode=200, $header = [], $options = []){
        $port =  vae_assign($code, $msg, $data, $url, $httpCode, $header, $options);
        vae_set_hook('port_return',$port);
        return $port;
    }

    protected static function param($key=""){
        $param = vae_get_param();
        vae_set_hook('port_param',$param);
        if(!empty($key) and isset($param[$key])){
            $param = $param[$key]; 
        } else if(!empty($key) and !isset($param[$key])){
            $param = null;
        } else {
            $param = $param;
        }
        return $param;
    }

    protected static function setThumb($thumb="",$i=false){
        if($i){
            $thumb = explode(',',$thumb);
            foreach ($thumb as $k => $v) {
                $thumb[$k] = config('webconfig.domain').$v;
            }
        } else {
            $thumb = config('webconfig.domain').$thumb;
        }
        return $thumb;
    }

    /**
     * 获取参数
     * @return array|mixed|null
     */
    public function params()
    {
        return vae_get_param();
    }

    public function jsonSuccess($message = '',$data = []) : Json
    {
        return \json([
            'code'      => self::CODE_SUCCESS,
            'message'   => $message,
            'data'      => $data
        ]);
    }

    public function jsonFail($message = '',$data = []) : Json
    {
        return \json([
            'code'       => self::CODE_FAIL,
            'message'    => $message,
            'data'       => $data
        ]);
    }

    /**
     * 获取openId
     * @param $code
     * @return bool|mixed
     */
    protected function getOpenid($code)
    {
        $config = Config::get('wx');
        $appId = $config['appId'];
        $appSecret = $config['appSecret'];
        $url="https://api.weixin.qq.com/sns/jscode2session?appid=".$appId."&secret=".$appSecret."&js_code=".$code."&grant_type=authorization_code";
        $result = self::curl_https($url);
        $openid = $result['openid'] ?? null;
        if($openid){
            return $openid;
        }

        return false;
    }

    /**
     * 获取accessToken
     * @return bool|mixed
     */
    protected function getAccessToken()
    {
        $config = Config::get('wx');
        $appId = $config['appId'];
        $appSecret = $config['appSecret'];
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appSecret;
        $result = self::curl_https($url);
        $accessToken = $result['access_token']??null;
        if($accessToken){
            return $accessToken;
        }

        return false;
    }

    /**
     * curl请求
     * @param string $url
     * @return array|bool
     */
    protected function curl_https($url ='')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        if(empty($result)){
            return false;
        }
        return (array)json_decode($result);
    }

    /**
     * 验证码
     * @param $tel
     * @param $code
     * @return bool
     * @throws ClientException
     */
    public function sms($tel,$code)
    {
        $accessKeyId = 'LTAIeaTl3FESlOZk';
        $accessSecret = 'vwgqcBq9h2k6xPCxxAcEYCVE3Exo1v'; //
        $signName = '落水人员预警系统'; //配置签名
        $templateCode = 'SMS_132395440';//配置短信模板编号

        //TODO 短信模板变量替换JSON串,友情提示:如果JSON中需要带换行符,请参照标准的JSON协议。
        $jsonTemplateParam = json_encode(['code' => $code]);

        //return $jsonTemplateParam;
        AlibabaCloud::accessKeyClient($accessKeyId, $accessSecret)
            ->regionId('cn-hangzhou')
            ->asGlobalClient();
        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'RegionId' => 'cn-hangzhou',
                        'PhoneNumbers' => $tel,//目标手机号
                        'SignName' => $signName,
                        'TemplateCode' => $templateCode,
                        'TemplateParam' => $jsonTemplateParam,
                    ],
                ])
                ->request();

            $opRes = $result->toArray();
            if ($opRes && $opRes['Code'] == "OK"){
                return true;
            }

        } catch (ClientException $e) {
            return false;
        } catch (ServerException $e) {
            return false;
        }
    }

    //前台权限
    public function auth()
    {
        $user = User::find($this->params()['user_id']);
        if (!$user) {
            return $this->jsonFail('用户不存在');
        }
        if ($user->role->name == '') {

        }
    }


}

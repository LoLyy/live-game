<?php

namespace App\Services;


use Illuminate\Support\Facades\Log;

class SMSService
{
    private $node_url;
    private $params = [];
    private $is_mock = false;

    /**
     * SMSService constructor.
     */
    private function __construct()
    {
        $this->params['apikey'] = config('services.monyun.api_key');

    }

    /**
     * @return SMSService
     */
    public static function create()
    {
        return new self();
    }

    /**
     * mock
     */
    public function mock()
    {
        $this->is_mock = true;
        return $this;
    }

    /**
     * 短信类型
     * @param string $content
     * @return $this
     */
    public function sms(string $content)
    {
        $this->node_url = config('services.monyun.nodes.north_sms_url'); // 默认发送北方短信节点

        $this->params['content'] = (string)urlencode(iconv("UTF-8", "gbk//TRANSLIT", $content));

        return $this;
    }

    /**
     * 语音类型
     * @param string $content
     * @return $this
     */
    public function voice(string $content)
    {
        $this->node_url = config('services.monyun.nodes.south_voice_url');

        $this->params['msgtype'] = (string)1; // 类型是验证码
        $this->params['content'] = (string)$content;

        return $this;
    }

    /**
     * send
     * @param string $mobile
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $mobile = '')
    {
        $this->params['mobile'] = (string)$mobile;

        if (!isset($this->params['content'])) {
            throw new \Exception("没有设置短信内容,短信发送失败");
        }

        if ($this->is_mock) { // mock send success
            $res['result'] = 0;

        } else {// 调用第三方发送短信
            $res = HttpClientService::getInstance()->post($this->node_url, $this->params, 'json');

        }

        $log = "短信：{$mobile}  " . json_encode($this->params) . '  ' . json_encode($res);

        // 记录发送日志
        if (php_sapi_name() == 'cli') { // swoole 模式走协程
            go(function () use ($log) {
                Log::info('协程：' . $log);
            });
        } else {
            Log::info($log);
        }

        if (isset($res['result']) && $res['result'] == 0) {

            return true;
        }

        return false;
    }

}
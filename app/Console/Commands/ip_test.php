<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ip_test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ip_test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $path_dir = storage_path("app");
        $file = $path_dir . '/ip.txt';
        var_dump($file);

        if (file_exists($file)) {
            $content = file_get_contents($file); //将整个文件内容读入到一个字符串中
        } else {
            exit();
        }

        $ip_array = explode("\r\n", $content);

        $url = 'www.google.com';

        foreach ($ip_array as $ip_port) {
            var_dump($ip_port);
            $ip_port_object = explode(':', $ip_port);

            //
            $proxy = $ip_port_object[0] ?? "127.0.0.1";
            $proxy_port = $ip_port_object[1] ?? 80;
            var_dump($ip_port_object);

            $headers = array(
                'authority:www.amazon.com',
                'upgrade-insecure-requests:1',
                'user-agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3355.4 Safari/537.36',
                'accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'accept-encoding:gzip, deflate, br',
                'accept-language:zh-CN,zh;q=0.9,en;q=0.8',
            );
            //$url = 'https://api.apiopen.top/recommendPoetry';
            $url = 'https://trends.google.com/trends/api/explore';
            var_dump($this->curl_via_proxy($url,$ip_port,$headers));

        }


    }



    public function curl_via_proxy($url,$proxy_ip,$headers = [],$user_agent = 'curl',$method = 'GET')
    {
        $arr_ip = explode(':',$proxy_ip);

        $ch = curl_init($url); //创建CURL对象
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HEADER, 0); //返回头部
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回信息
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); //连接超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //读取超时时间
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_PROXY, $arr_ip[0]); //代理服务器地址
        curl_setopt($ch, CURLOPT_PROXYPORT, $arr_ip[1]); //代理服务器端口
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        //添加头部信息
        if(!empty($headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }


        $res = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        if ($curl_errno) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $res;
    }

}

<?php
namespace app\admin\controller;
use think\Config;
use think\Controller;

class Getauth extends Controller {
	//接口
	public function login() {
		//授权接口 http://47.94.23.112/CtripLeave/admin/Getauth/login
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . Config::get('wxappid') . "&redirect_uri=" . Config::get('wxredirect_url') . "&response_type=" . Config::get('response_type') . "&scope=" . Config::get('wxscope') . "&state=" . Config::get('wxstate') . "#wechat_redirect";
		$this->redirect($url);
	}
	//获取用户个人信息
	public function getnews() {
		//1、获取code
		if (!$_GET['code']) {
			return 1; //用户不予授权
		}
		//2、获取access_token和openid
		$url1 = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . Config::get('wxappid') . "&secret=" . Config::get('wxappsecret') . "&code=" . $_GET['code'] . "&grant_type=authorization_code";
		$res_arr1 = $this->curl_get_contents($url1);

		//3、获得微信用户信息
		$url2 = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $res_arr1['access_token'] . "&openid=" . $res_arr1['openid'] . "&lang=zh_CN";
		$res_arr2 = $this->curl_get_contents($url2);

		//插入所需要的数据到数据库
		if ($res_arr1 && $res_arr2) {
			$data['openid'] = $res_arr2['openid'];
			$data['nickname'] = $res_arr2['nickname'];
			$data['sex'] = $res_arr2['sex'];
			$data['headimgurl'] = $res_arr2['headimgurl'];

			//查看user是否已插入基本信息，存在则更新返回用户id，不存在则插入数据返回用户id
			$exist = db('user')->field('id')->where('openid', $res_arr2['openid'])->find();
			if ($exist) {
				//更新用户数据
				db('user')->where('openid', $data['openid'])->update($data);
				return json($exist);
			} else {
				//插入用户数据
				$no_exist['id'] = db('user')->insertGetId($data);
				if ($no_exist) {
					return json($no_exist);
				}
			}
		}
		return 1; //失败
	}

	//通过curl抓取数据，之所以不用file_get_contents，是因为curl更快更不易出错
	function curl_get_contents($url) {
		$ch = curl_init(); //初始化

		//设置url和响应选项
		curl_setopt($ch, CURLOPT_URL, $url); //设置url
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
		curl_setopt($ch, CURLOPT_HEADER, 0); //关闭：启用时会将头文件的信息作为数据流输出。
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); //在尝试连接时等待的秒数。设置为0，则无限等待。

		$res_data = curl_exec($ch); //抓取URL并把它传递给res_data

		if ($res_data === false) {
			$$res_data = 'cURL error:' . curl_error($ch);
			curl_close($ch);
			return false;
		}
		curl_close($ch);

		//将json格式转换为array
		$res_data = json_decode($res_data, true);

		return ($res_data);
	}
}

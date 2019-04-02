<?php
namespace app\admin\controller;
use think\Controller;

class Vocation extends Controller {

	//接口http://47.94.23.112/CtripLeave/admin/Vocation/add
	//添加vocation数据    同时需要接收一个uid作为用户信息查询
	public function add() {
		if (request()->isPost()) {
			$user_vocation = file_get_contents('php://input');
			$user_vocation = json_decode($user_vocation, true);
			//validate验证post过来的数据
			$validate = validate('vocation');
			if (!$validate->scene('add')->check($user_vocation)) {
				return 103; //非法输入
			}
			$all_industry_ranking = model('vocation')->get_ranking($user_vocation); //获得排名表
			return json($all_industry_ranking);
		} else {
			echo 104; //HTTP提交方式错误
		}
	}

	//接口http://47.94.23.112/CtripLeave/admin/Vocation/re_opt
	//获取用户之前填写的vocation	  需要参数uid	前端跳转到add接口，提交时覆盖用户vocation数据，修改ranking数据
	public function re_opt() {
		$user_id = file_get_contents('php://input');
		$user_id = json_decode($user_id, true);
		$user_vocation = db('user_vocation')->where('uid', $user_id['uid'])->find();
		return json($user_vocation);
	}

	//接口//http://47.94.23.112/CtripLeave/admin/Vocation/user_ranking
	//已经提交过vocation，直接获取ranking数据
	public function user_ranking() {
		if (request()->isPost()) {
			$user_id = file_get_contents('php://input');
			$user_id = json_decode($user_vocation, true);
			$user_vocation = db('user_vocation')->where('uid', $user_id['uid'])->find();
			$all_industry_ranking = $this->get_ranking($user_vocation);
			return json($all_industy_ranking);
		} else {
			echo 104; //HTTP提交方式错误
		}
	}
}

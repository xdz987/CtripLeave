<?php
namespace app\admin\controller;
use think\Controller;

class Vocation extends Controller {

	//添加vocation数据    同时需要接收一个Openid作为用户信息查询
	public function add() {
		try {
			if (request()->isPost()) {
				$data = input('post.');

				//validate验证post过来的数据
				$validate = validate('vocation');
				if (!$validate->scene('add')->check($data)) {
					return 103; //非法输入
				}

				$vocation['uid'] = $data['uid'];
				$vocation['industry'] = $data['industry'];
				$vocation['vo_year_day'] = $data['vo_year_day'];
				$vocation['vo_sum_day'] = $data['vo_sum_day'];

				//查看vocation是否已插入基本信息，不存在则插入数据返回uid。	之所以存在不更新数据是因为会对ranking表不匹配，返回结果也不匹配
				$exist = db('vocation')->field('uid')->where('uid', $data['uid'])->find();
				if (!$exist) {
					$save = db('vocation')->insert($vocation);
				}

				//排名运算,返回对应行业的整条数据，但不包括name，多了用户所在行业对应的字母
				$rank = $this->ranking($vocation, $exist);

				//查询用户姓名
				$name = db('user')->field('nickname')->where('id', $data['uid'])->find();

				//前端所需：总排行榜1、用户姓名1、用户对应行业1、用户所在行业排名1、所在行业总平均年假1、用户年假1，删除不需要数据
				$rank['nickname'] = $name['nickname'];
				$rank['vo_year_day'] = $vocation['vo_year_day'];
				unset($rank['id']);
				unset($rank['name']);
				unset($rank['mem_sum']);
				unset($rank['account_year_day']);
				unset($rank['account_sum_day']);

				return json($rank);
			} else {
				echo 104; //HTTP提交方式错误
			}
		} catch (Exception $e) {
			echo "添加失败,请返回重新提交!";
		}
	}

	//重选
	/*public function re_opt() {

	}*/

	//对POST过来的哪一行业进行筛选
	public function ranking($vocation, $exist) {
		//'A-J'对应'1-10'行业
		switch ($vocation['industry']) {
		case 'A':
			//传递第一个参数是行业，第二个参数是POST过来的值，第三个参数是用户是否插入过数据
			$rankRes = model('vocation')->calculate(1, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']); //转换排名格式对于“1=>字母”
			$rankRes['industry'] = 'A';
			return $rankRes;
			break;
		case 'B':
			$rankRes = model('vocation')->calculate(2, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'B';
			return $rankRes;
			break;
		case 'C':
			$rankRes = model('vocation')->calculate(3, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'C';
			return $rankRes;
			break;
		case 'D':
			$rankRes = model('vocation')->calculate(4, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'D';
			return $rankRes;
			break;
		case 'E':
			$rankRes = model('vocation')->calculate(5, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'E';
			return $rankRes;
			break;
		case 'F':
			$rankRes = model('vocation')->calculate(6, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'F';
			return $rankRes;
			break;
		case 'G':
			$rankRes = model('vocation')->calculate(7, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'G';
			return $rankRes;
			break;
		case 'H':
			$rankRes = model('vocation')->calculate(8, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'H';
			return $rankRes;
			break;
		case 'I':
			$rankRes = model('vocation')->calculate(9, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'I';
			return $rankRes;
			break;
		case 'J':
			$rankRes = model('vocation')->calculate(10, $vocation, $exist);
			$rankRes['all_rank'] = $this->transform($rankRes['all_rank']);
			$rankRes['industry'] = 'J';
			return $rankRes;
			break;
		default:
			return 105; //行业选择类型错误！
			break;
		}
	}

	//对全部排名 1=>'id' 换成1=>'A'格式
	public function transform($all_rank) {

		$letter = array(
			1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E',
			6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J',
		);
		foreach ($all_rank as $k => $v) {
			foreach ($letter as $k1 => $v1) {
				if ($v == $k1) {
					$all_rank[$k] = $v1;
				}
				continue;
			}
		}
		return $all_rank;
	}
}

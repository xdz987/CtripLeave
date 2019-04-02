<?php
namespace app\admin\model;
use think\Db;
use think\Model;

class Vocation extends Model {

	//排名计算，数据整理
	public function get_ranking($user_vocation) {

		//插入或更新user_vocation表
		$old_user_vocation = $this->user_vocation_operate($user_vocation);

		//数据表前缀
		$prefix = config('database.prefix');
		$industry_ranking_tableName = $prefix . 'industry_ranking';

		//增加用户输入数据
		db('industry_ranking')->where('industry', $user_vocation['industry'])->setInc('mem_sum', 1);
		db('industry_ranking')->where('industry', $user_vocation['industry'])->setInc('account_year_day', $user_vocation['vo_year_day']);
		db('industry_ranking')->where('industry', $user_vocation['industry'])->setInc('account_sum_day', $user_vocation['vo_sum_day']);

		if ($old_user_vocation) {
			//删除用户输入数据
			db('industry_ranking')->where('industry', $user_vocation['industry'])->setDec('mem_sum', 1);
			db('industry_ranking')->where('industry', $user_vocation['industry'])->setDec('account_year_day', $old_user_vocation['vo_year_day']);
			db('industry_ranking')->where('industry', $user_vocation['industry'])->setDec('account_sum_day', $old_user_vocation['vo_sum_day']);
		}

		//查询所有行业数据
		$industry_rankRes = Db::table($industry_ranking_tableName)->limit(10)->select();
		foreach ($industry_rankRes as $k => $v) {
			//计算每个行业的平均数
			if ($v['mem_sum']) {
				$industry_rankRes[$k]['average_sum_day'] = $v['account_year_day'] / $v['mem_sum'];
			} else {
				$industry_rankRes[$k]['average_sum_day'] = 0;
			}
		}

		//返回输入数组中某个单一列的值。
		$average_sum_days = array_column($industry_rankRes, 'average_sum_day');
		//对所有行业根据‘平均年总假’进行升序处理
		array_multisort($average_sum_days, SORT_ASC, $industry_rankRes);

		foreach ($industry_rankRes as $k => $v) {
			$user_vocation['all_industry_rank'][$k + 1] = $v['industry']; //所有行业排名  例如第一名：1->A
			if ($v['industry'] == $user_vocation['industry']) {
				$user_vocation['average_sum_day'] = $industry_rankRes[$k]['average_sum_day']; //平均年假赋值
				$user_vocation['user_industry_rank'] = $k + 1; //用户所选行业的的排名
			}
		}

		//整理数据
		unset($user_vocation['vo_sum_day']);
		$user_vocation['nickname'] = db('user')->where('id', $user_vocation['uid'])->value('nickname');

		return $user_vocation;
		//前端所需：全部行业排行榜	 1	用户姓名	1	所在行业	1	所在行业排名	1	所在行业平均年假	1	用户年假	1
	}

	//对user_vocation进行插入或者更新
	public function user_vocation_operate($user_vocation) {

		//查看user_vocation是否已插入基本信息，不存在则插入数据  存在则更新数据
		$old_user_vocation = db('user_vocation')->where('uid', $user_vocation['uid'])->find();
		if (!$old_user_vocation) {
			db('user_vocation')->insert($user_vocation);
		} else {
			db('user_vocation')->where('uid', $user_vocation['uid'])->update($user_vocation);
			return $old_user_vocation;
		}
	}
}
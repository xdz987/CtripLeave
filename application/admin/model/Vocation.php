<?php
namespace app\admin\model;
use think\Db;
use think\Model;

class Vocation extends Model {

	//判断哪一个行业并进行计算
	public function calculate($industry, $vocation, $exist) {
		try {
			//前缀
			$prefix = config('database.prefix');
			$tableName = $prefix . 'ranking';

			//当vocation中没有用户插入的数据时，出入用户数据更新ranking排行榜
			if (!$exist) {
				//查询出用户对应的哪个行业
				$rankRes = db('ranking')->find(['id' => $industry]);

				//以下进行排名ranking表插入前数据计算计算
				$rankRes['id'] = $industry;
				$rankRes['account_year_day'] = $rankRes['account_year_day'] + $vocation['vo_year_day']; //总人数的年假(暂时不需要，备用)
				$rankRes['account_sum_day'] = $rankRes['account_sum_day'] + $vocation['vo_sum_day']; //总人数的年总假期
				$rankRes['mem_sum'] = $rankRes['mem_sum'] + 1; //人数+1
				$rankRes['average_sum_day'] = $rankRes['account_sum_day'] / $rankRes['mem_sum']; //年总假平均
				db('ranking')->update($rankRes);
			}
			//查询所有的数据排名
			$in_rankRes = Db::table($tableName)->orderRaw('average_sum_day asc')->limit(10)->select();
			foreach ($in_rankRes as $k => $v) {
				$in_rankRes['all_rank'][$k + 1] = $v['id']; //所有行业排名 1-10
				if ($v['id'] == $industry) {
					$in_rank = $k + 1; //当前行业的排名
				}
			}
			//赋值排名以便返回数据
			$rankRes['in_rank'] = $in_rank; //当前行业排名
			$rankRes['all_rank'] = $in_rankRes['all_rank']; //所有行业排名

			//更新当前行业的排名，当十个行业都被查询过一次，数据表的in_rank排名字段就全部都有数据
			if (!$exist) {
				db('ranking')->where('id', $industry)->setField('in_rank', $in_rank);
			}
			return $rankRes;

		} catch (Exception $e) {
			echo "添加失败,请返回重新提交!";
		}
	}
}
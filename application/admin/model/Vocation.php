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

			//当传递的exist不存在，vocation中没有用户插入的数据时，更新ranking排行榜
			if (!$exist) {
				//查询出用户对应的哪个行业
				$rankRes = db('ranking')->find(['id' => $industry]);

				//以下进行排名ranking表插入前数据计算 增加数值+
				$rankRes['id'] = $industry;
				$rankRes['account_year_day'] = $rankRes['account_year_day'] + $vocation['vo_year_day']; //总人数的年假(暂时不需要，备用)
				$rankRes['account_sum_day'] = $rankRes['account_sum_day'] + $vocation['vo_sum_day']; //总人数的年总假期
				$rankRes['mem_sum'] = $rankRes['mem_sum'] + 1; //人数+1
				$rankRes['average_sum_day'] = $rankRes['account_sum_day'] / $rankRes['mem_sum']; //年总假平均
				db('ranking')->update($rankRes);
			} else {
				//当用户想重新选择行业时，更新完vocation表后，ranking原先选的进行减-运算，再对新选的进行加+运算
				//查询出用户对应的哪个行业
				$rankRes = db('ranking')->find(['id' => $industry]);
				//以下进行排名ranking表修改数据计算 增加数值+
				$rankRes['id'] = $industry;
				$rankRes['account_year_day'] = $rankRes['account_year_day'] + $vocation['vo_year_day']; //总人数的年假(暂时不需要，备用)
				$rankRes['account_sum_day'] = $rankRes['account_sum_day'] + $vocation['vo_sum_day']; //总人数的年总假期
				$rankRes['mem_sum'] = $rankRes['mem_sum'] + 1; //人数+1
				$rankRes['average_sum_day'] = $rankRes['account_sum_day'] / $rankRes['mem_sum']; //年总假平均
				db('ranking')->where('id', $industry)->update($rankRes);

				//查询出用户之前选的老行业
				$old_rankRes = db('ranking')->where('industry', $exist['industry'])->find();
				//以下进行排名ranking表修改数据计算 减去数值-   3.31在ranking表中增加industry字段

				$old_rankRes['account_year_day'] = $old_rankRes['account_year_day'] - $exist['vo_year_day']; //总人数的年假(暂时不需要，备用)
				$old_rankRes['account_sum_day'] = $old_rankRes['account_sum_day'] - $exist['vo_sum_day']; //总人数的年总假期
				$old_rankRes['mem_sum'] = $old_rankRes['mem_sum'] - 1; //人数+1
				if (!($old_rankRes['mem_sum'] - 1)) {
					$old_rankRes['average_sum_day'] = $old_rankRes['account_sum_day'] / $old_rankRes['mem_sum'];
				} else {
					$old_rankRes['average_sum_day'] = 0;
				}
				//年总假平均
				db('ranking')->where('industry', $exist['industry'])->update($old_rankRes);

			}

			//查询老行业id
			if ($exist) {
				$old_industry = db('ranking')->field('id')->where('industry', $exist['industry'])->find();
			}
			//查询所有的数据排名
			$in_rankRes = Db::table($tableName)->orderRaw('average_sum_day asc')->limit(10)->select();
			foreach ($in_rankRes as $k => $v) {
				$in_rankRes['all_rank'][$k + 1] = $v['id']; //所有行业排名 1-10
				if ($v['id'] == $industry) {
					$in_rank = $k + 1; //当前行业的排名
				}
				if ($exist) {
					//更新后的老行业排名
					if ($v['id'] == $old_industry['id']) {
						$old_in_rank = $k + 1;
					}
				}
			}
			//赋值排名以便返回数据
			$rankRes['in_rank'] = $in_rank; //当前行业排名
			$rankRes['all_rank'] = $in_rankRes['all_rank']; //所有行业排名

			//更新当前行业的排名，当十个行业都被查询过一次，数据表的in_rank排名字段就全部都有数据
			db('ranking')->where('id', $industry)->setField('in_rank', $in_rank); //更新新选择的行业排名
			if ($exist) {
				db('ranking')->where('id', $old_industry['id'])->setField('in_rank', $old_in_rank); //更新老行业排名
			}
			return $rankRes;

		} catch (Exception $e) {
			echo "添加失败,请返回重新提交!";
		}
	}
}
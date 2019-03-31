<?php

namespace app\admin\validate;
use think\validate;

class Vocation extends Validate {
	protected $rule = [
		'uid' => 'require|number|between:1,10000',
		'industry' => 'require|^[A-J]+$|length:1,1',
		'vo_year_day' => 'require|number|between:0,300',
		'vo_sum_day' => 'require|number|between:0,300',
	];

	//暂时不需要，除非将TP5弹窗模板替换掉(微信手机不支持原生弹窗)
	protected $message = [
		'uid.require' => '用户id不得为空！',
		'uid.unique' => '用户id必须是数字！',
		'industry.require' => '所属行业不得为空!',
		'vo_year_day.require' => '用户年假不得为空！',
		'vo_year_day.number' => '请填写数字!',
		'vo_year_day.between' => '用户年假最多300天!',
		'vo_year_day.require' => '用户总年假不得为空!',
		'vo_sum_day.number' => '请填写数字！',
		'vo_sum_day.between' => '用户年总假最多300天!',
	];

	protected $scene = [
		'add' => ['uid', 'industry', 'vo_year_day', 'vo_sum_day'],
	];
}
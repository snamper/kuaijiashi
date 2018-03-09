<?php

defined('IN_IA') or exit('Access Denied');

if (defined('IN_SYS')) {
    load()->admin('tpl');
} elseif (IS_APP) {
    load()->app('tpl');
} elseif (IS_DESKTOP) {
    load()->web('tpl');
}

function tpl_form_field_settime($name, $value = '', $placeholder = '时间选择')
{
    $html = '';
    if (!defined('TPL_INIT_SETTIMEPICKER')) {
        $html = <<<EOF
<script type="text/javascript">
require(['settimepicker'], function(){
	$(function(){
	$.fn.datetimepicker.dates['zh-CN'] = {
		days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
		daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
		daysMin:  ["日", "一", "二", "三", "四", "五", "六", "日"],
		months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
		monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "11月", "12月"],
		today: "今天",
		suffix: [],
		meridiem: ["上午", "下午"]
	};
		$(".settimepicker").each(function(){
			var option = {
				format: 'yyyy-mm-dd hh:ii:ss',
				language: 'zh-CN'
			}
			$(this).datetimepicker(option);
		});
	});
});
</script>
EOF;
        define('TPL_INIT_SETTIMEPICKER', true);
    }
    $value = $value ? date('Y-m-d H:i:s', $value) : '';
    $html .= <<<EOF
				<div class="has-feedback">
				  <input type="text" name="{$name}" value="{$value}" class="form-control settimepicker" placeholder="{$placeholder}" readonly>
				  <span class="form-control-feedback" aria-hidden="true"><i class="fa fa-calendar"></i></span>
				</div>
EOF;
    return $html;
}

function tpl_form_field_date($name, $value = '', $withtime = false)
{
    $s = '';
    $s = '
		<script type="text/javascript">
			require(["datetimepicker"], function(){
				$(function(){
						var option = {
							lang : "zh",
							step : 5,
							timepicker : ' . (!empty($withtime) ? "true" : "false") . ',
							closeOnDateSelect : true,
							format : "Y-m-d' . (!empty($withtime) ? ' H:i"' : '"') . '
						};
					$(".datetimepicker[name = \'' . $name . '\']").datetimepicker(option);
				});
			});
		</script>';
    $withtime = empty($withtime) ? false : true;
    if (!empty($value)) {
        $value = strexists($value, '-') ? strtotime($value) : $value;
    } else {
        $value = TIMESTAMP;
    }
    $value = ($withtime ? date('Y-m-d H:i:s', $value) : date('Y-m-d', $value));
    $s .= '<input type="text" name="' . $name . '"  value="' . $value . '" placeholder="请选择日期时间" readonly="readonly" class="datetimepicker form-control" style="padding-left:12px;" />';
    return $s;
}

function tpl_form_field_calendar($name, $values = array())
{
    $html = '';
    if (!defined('TPL_INIT_CALENDAR')) {
        $html .= <<<EOF
<script type="text/javascript">
	function handlerCalendar(elm) {
		require(["jquery","moment"], function($, moment){
			var tpl = $(elm).parent().parent();
			var year = tpl.find("select.tpl-year").val();
			var month = tpl.find("select.tpl-month").val();
			var day = tpl.find("select.tpl-day");
			day[0].options.length = 1;
			if(year && month) {
				var date = moment(year + "-" + month, "YYYY-M");
				var days = date.daysInMonth();
				for(var i = 1; i <= days; i++) {
					var opt = new Option(i, i);
					day[0].options.add(opt);
				}
				if(day.attr("data-value")!=""){
					day.val(day.attr("data-value"));
				} else {
					day[0].options[0].selected = "selected";
				}
			}
		});
	}
	require(["jquery"], function($){
		$(".tpl-calendar").each(function(){
			handlerCalendar($(this).find("select.tpl-year")[0]);
		});
	});
</script>
EOF;
        define('TPL_INIT_CALENDAR', true);
    }

    if (empty($values) || !is_array($values)) {
        $values = array(0, 0, 0);
    }
    $values['year']  = intval($values['year']);
    $values['month'] = intval($values['month']);
    $values['day']   = intval($values['day']);

    if (empty($values['year'])) {
        $values['year'] = '1980';
    }
    $year = array(date('Y'), '1914');
    $html .=
        '<div class="row row-fix tpl-calendar">
	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
		<select name="' . $name . '[year]" onchange="handlerCalendar(this)" class="form-control tpl-year">
			<option value="">年</option>';
    for ($i = $year[1]; $i <= $year[0]; $i++) {
        $html .=
            '			<option value="' . $i . '"' . ($i == $values['year'] ? ' selected="selected"' : '') . '>' . $i . '</option>';
    }
    $html .= '
		</select>
	</div>
	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
		<select name="' . $name . '[month]" onchange="handlerCalendar(this)" class="form-control tpl-month">
			<option value="">月</option>';
    for ($i = 1; $i <= 12; $i++) {
        $html .=
            '			<option value="' . $i . '"' . ($i == $values['month'] ? ' selected="selected"' : '') . '>' . $i . '</option>';
    }
    $html .= '
		</select>
	</div>
	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
		<select name="' . $name . '[day]" data-value="' . $values['day'] . '" class="form-control tpl-day">
			<option value="0">日</option>
		</select>
	</div>
</div>';
    return $html;
}

function tpl_form_field_district($name, $values = array(), $option = array('withTitle' => true, 'code' => false))
{
    $html = '';
    if (!defined('TPL_INIT_DISTRICT')) {
        $withTitle = empty($option['withTitle']) ? 'false' : 'true';
        $code      = empty($option['code']) ? 'false' : 'true';
        $html .= <<<EOF
<script type="text/javascript">
	require(["district"], function(dis){
		$(".tpl-district-container").each(function(){
			var elements = {};
			elements.province = $(this).find(".tpl-province")[0];
			elements.city = $(this).find(".tpl-city")[0];
			elements.district = $(this).find(".tpl-district")[0];
			var values = {};
			values.province = $(elements.province).data("value");
			values.city = $(elements.city).data("value");
			values.district = $(elements.district).data("value");
			dis.render(elements, values, {withTitle: {$withTitle}, code : {$code}});
		});
	});
</script>
EOF;
        define('TPL_INIT_DISTRICT', true);
    }
    if (empty($values) || !is_array($values)) {
        $values = array('province' => '', 'city' => '', 'district' => '');
    }
    if (empty($values['province'])) {
        $values['province'] = '';
    }
    if (empty($values['city'])) {
        $values['city'] = '';
    }
    if (empty($values['district'])) {
        $values['district'] = '';
    }
    $html .= '
<div class="row row-fix tpl-district-container">
	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
		<select name="' . $name . '[province]" data-value="' . $values['province'] . '" class="form-control tpl-province">
		</select>
	</div>
	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
		<select name="' . $name . '[city]" data-value="' . $values['city'] . '" class="form-control tpl-city">
		</select>
	</div>
	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
		<select name="' . $name . '[district]" data-value="' . $values['district'] . '" class="form-control tpl-district">
		</select>
	</div>
</div>';
    return $html;
}

function tpl_form_field_category_2level($name, $parents, $children, $parentid, $childid)
{
    $html = '
<script type="text/javascript">
	window._' . $name . ' = ' . json_encode($children) . ';
</script>';
    if (!defined('TPL_INIT_CATEGORY')) {
        $html .= '
<script type="text/javascript">
	function renderCategory(obj, name){
		var index = obj.options[obj.selectedIndex].value;
		$selectChild = $(\'#\'+name+\'_child\');
		var html = \'<option value="0">请选择二级分类</option>\';
		if (!window[\'_\'+name] || !window[\'_\'+name][index]) {
			$selectChild.html(html);
			return false;
		}
		for(var i=0; i< window[\'_\'+name][index].length; i++){
			html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';
		}
		$selectChild.html(html);
	}
</script>
';
        define('TPL_INIT_CATEGORY', true);
    }

    $html .=
        '<div class="row row-fix tpl-category-container">
	<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
		<select class="form-control tpl-category-parent" id="' . $name . '_parent" name="' . $name . '[parentid]" onchange="renderCategory(this,\'' . $name . '\')">
			<option value="0">请选择一级分类</option>';
    $ops = '';
    if (!empty($parents)) {
        foreach ($parents as $row) {
            $html .= '
				<option value="' . $row['id'] . '" ' . (($row['id'] == $parentid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
        }
    }

    $html .= '
		</select>
	</div>
	<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
		<select class="form-control tpl-category-child" id="' . $name . '_child" name="' . $name . '[childid]">
			<option value="0">请选择二级分类</option>';
    if (!empty($parentid) && !empty($children[$parentid])) {
        foreach ($children[$parentid] as $row) {
            $html .= '
			<option value="' . $row['id'] . '"' . (($row['id'] == $childid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
        }
    }
    $html .= '
		</select>
	</div>
</div>
';
    return $html;
}

function tpl_form_field_industry($name, $pvalue = '', $cvalue = '', $parentid = 'industry_1', $childid = 'industry_2')
{
    $html = '
<div class="row row-fix">
	<div class="col-sm-4">
		<select name="' . $name . '[parent]" id="' . $parentid . '" class="form-control" value="' . $pvalue . '"></select>
	</div>
	<div class="col-sm-4">
		<select name="' . $name . '[child]" id="' . $childid . '" class="form-control" value="' . $cvalue . '"></select>
	</div>
	<script type="text/javascript">
		require([\'industry\'], function(industry){
			industry.init("' . $parentid . '","' . $childid . '");
		});
	</script>
</div>';
    return $html;
}

function tpl_form_field_coordinate($field, $value = array(), $translate = 'false')
{
    $html = '';
    if (!defined('TPL_INIT_COORDINATE')) {
        $html .= '
        <script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=F51571495f717ff1194de02366bb8da9&s=1"></script>
<script type="text/javascript">
	function showCoordinate(elm) {
		var val = {};
		val.lng = parseFloat($(elm).parent().prev().prev().prev().find(":text").val());
		val.lat = parseFloat($(elm).parent().prev().prev().find(":text").val());
        val.translate = '.$translate.';
		util.map(val, function(r){
			$(elm).parent()
				.prev().find(":text").val(r.label).end()
				.prev().find(":text").val(r.lat).end()
				.prev().find(":text").val(r.lng);
		});
	}
</script>';
        define('TPL_INIT_COORDINATE', true);
    }
    $html .= '
<div class="row row-fix">
	<div class="col-xs-2 col-sm-2">
		<input type="text" name="' . $field . '[lng]" value="' . $value['lng'] . '" placeholder="地理经度"  class="form-control" />
	</div>
	<div class="col-xs-2 col-sm-2">
		<input type="text" name="' . $field . '[lat]" value="' . $value['lat'] . '" placeholder="地理纬度"  class="form-control" />
	</div>
	<div class="col-xs-6 col-sm-6">
		<input type="text" name="' . $field . '[label]" value="' . $value['label'] . '" placeholder="详细地址"  class="form-control" />
	</div>
	<div class="col-xs-2 col-sm-2">
		<button onclick="showCoordinate(this);" class="btn btn-default btn-block" type="button">选择坐标</button>
	</div>
</div>';
    return $html;
}

function tpl_fans_form($field, $value = '')
{

    switch ($field) {
        case 'avatar':
            global $_W;

            $url = tomedia($value);

            $html = '';
            if (!defined('TPL_INIT_AVATAR')) {
                $html = tpl_form_field_image($field, $value);

                define('TPL_INIT_AVATAR', true);

            }

            break;
        case 'birth':
        case 'birthyear':
        case 'birthmonth':
        case 'birthday':
            $html = tpl_form_field_calendar('birth', $value);
            break;
        case 'reside':
        case 'resideprovince':
        case 'residecity':
        case 'residedist':
            $html = tpl_form_field_district('reside', $value);
            break;
        case 'bio':
        case 'interest':
            $html = '<textarea name="' . $field . '" class="form-control">' . $value . '</textarea>';
            break;
        case 'gender':
            $html = '
<select name="gender" class="form-control">
	<option value="3" ' . ($value == 3 ? 'selected ' : '') . '>保密</option>
	<option value="1" ' . ($value == 1 ? 'selected ' : '') . '>男</option>
	<option value="2" ' . ($value == 2 ? 'selected ' : '') . '>女</option>
</select>';
            break;
        case 'role':
            $html = '
<select name="role" class="form-control">
	<option value="1" ' . ($value == 1 ? 'selected ' : '') . '>管理员</option>
	<option value="2" ' . ($value == 2 ? 'selected ' : '') . '>普通用户</option>
</select>';
            break;
        case 'education':
        case 'constellation':
        case 'zodiac':
        case 'bloodtype':
            if ($field == 'bloodtype') {
                $options = array('A', 'B', 'AB', 'O', '其它');
            } elseif ($field == 'zodiac') {
                $options = array('鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪');
            } elseif ($field == 'constellation') {
                $options = array('水瓶座', '双鱼座', '白羊座', '金牛座', '双子座', '巨蟹座', '狮子座', '处女座', '天秤座', '天蝎座', '射手座', '摩羯座');
            } elseif ($field == 'education') {
                $options = array('博士', '硕士', '本科', '专科', '中学', '小学', '其它');
            }
            $html = '<select name="' . $field . '" class="form-control">';
            foreach ($options as $item) {
                $html .= '<option value="' . $item . '" ' . ($value == $item ? 'selected ' : '') . '>' . $item . '</option>';
            }
            $html .= '</select>';
            break;
        case 'nickname':
        case 'realname':
        case 'address':
        case 'mobile':
        case 'qq':
        case 'msn':
        case 'email':
        case 'telephone':
        case 'taobao':
        case 'alipay':
        case 'studentid':
        case 'grade':
        case 'graduateschool':
        case 'idcard':
        case 'zipcode':
        case 'site':
        case 'affectivestatus':
        case 'lookingfor':
        case 'nationality':
        case 'height':
        case 'weight':
        case 'company':
        case 'occupation':
        case 'position':
        case 'revenue':
        default:
            $html = '<input type="text" class="form-control" name="' . $field . '" value="' . $value . '" />';
            break;
    }
    return $html;
}

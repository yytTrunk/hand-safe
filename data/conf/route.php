<?php
// +----------------------------------------------------------------------
// | vaeThink [ Programming makes me happy ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.vaeThink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 听雨 < 389625819@qq.com >
// +---------------------------------------------------------------------

// 应用路由

use think\Db;
use think\Cache;
use think\Route;

if (!vae_is_installed()) {
    return;
}
if(Cache::get('vae_route')) {
	$runtimeRoute = Cache::get('vae_route');
} else {
	$runtimeRoute = Db::name("route")->where(['status' => 1])->order('create_time asc')->column('url,full_url');
	Cache::set('vae_route',$runtimeRoute);
}

Route::rule($runtimeRoute);

Route::post('/distance','index/distance');
Route::post('/sms','index/sendSMS');
Route::post('/login','index/login');
Route::post('/search/detail','index/searchDetail');
Route::post('/search','index/search');
Route::post('/defaultLogin','index/defaultLogin');
Route::post('/modify/password','index/modifyPassword');
Route::post('/modifyInfo','index/modifyInfo');

Route::post('/record','main/record');
Route::post('/record/whether','main/whether');
Route::post('/record/clas','main/clas');
Route::post('/record/accept','main/accept');
Route::post('/record/reject','main/reject');
Route::post('/record_today','main/record_today');

Route::post('/event','main/event');
Route::post('/eventSubmit','main/eventSubmit');
Route::post('/eventDetail','main/eventDetail');
Route::post('/eventList','main/eventList');
Route::post('/cancelEvent','main/cancelEvent');

Route::post('/project','main/project');
Route::post('/projectList','main/projectList');

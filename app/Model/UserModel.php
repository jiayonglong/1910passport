<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
class UserModel extends Model
{
    protected $table = 'p_users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;
    public static function webLogin($uid,$user_name)
    {
        $token = UserModel::generateToken($uid);
        //服务器保存 token
        $token_key = 'h:login_info:'.$token;
        $login_info = [
            'token'         => $token,                      // 用户token
            'uid'           => $uid,                        // 用户主表 uid
            'user_name'     => $user_name,                  // 用户名
            'login_time'    => date('Y-m-d H:i:s'),    //登录时间
            'login_ip'      => $_SERVER['REMOTE_ADDR'],     //客户端登录IP
        ];
        Redis::hMset($token_key,$login_info);
        Redis::expire($token_key,7200);     // 登录有效期 2 个小时

        //将 uid 与 token写入 seesion    （session使用Redis存储）
        session(['uid'=>$uid,'user_name'=>$user_name]);

        return $token;
    }


    /**
     * 生成用户token
     */
    public static function generateToken($uid)
    {
        $str =  $uid . Str::random(5) . time() . mt_rand(1111,9999999);
        return strtoupper(substr(Str::random(5) . md5($str),1,20));
    }
}

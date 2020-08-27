<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Model\UserModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
class LoginController extends Controller
{

//    public function logins(Request $request)
//    {
//        $redirect_uri = $request->get('redirect','http://local.www.com');
//        $data = [
//            'redirect_uri' => $redirect_uri
//        ];
//        return view('web.login',$data);
//    }
//
//    public function login(Request $request)
//    {
//        $redirect_uri = $request->input('redirect');        //跳转
//        return redirect($redirect_uri);
//    }
//}
    public function logins(request $request){
        $redirect_uri = $request->get('redirect','http://local.www.com');
       $data = [
            'redirect_uri' => $redirect_uri
        ];
        return view('web.login',$data);
    }
    public function login(request $request){
        $redirect_uri = $request->input('redirect');

        $info = $request->post('user_name');
        $pass = $request->post('user_pass');
        //用户名 Email 登录
        $u = UserModel::where(['email'=>$info])->orWhere(['user_name'=>$info])->first();
//        dd($u);die;
//        echo 111;die;
        //用户不存在
        if(empty($u))
        {
            $data = [
                'redirect'  => '/web/login?redirect_uri='.$redirect_uri,
                'msg'       => "用户名或密码不正确，请重新登录"
            ];
            return view('web.aww',$data);
        }
      //验证密码
        if( password_verify($pass,$u->password) )
        {
            //执行登录
            $token = UserModel::webLogin($u->user_id,$u->user_name);
            Cookie::queue('token',$token,60*24*30,'/','www.com',false,true);      //120分钟
            $data = [
                'redirect'  => $redirect_uri,
                'msg'       => "正在登录---"
            ];

            return view('web.aww',$data);
        }else{
            $data = [
                'redirect'  => '/web/login?redirect_uri='.$redirect_uri,
                'msg'       => "用户名或密码不正确，请重新登录"
            ];
            return view('web.aww',$data);
        }
       return redirect($redirect_uri);
    }
    public function logout(Request $request)
    {
        $redirect_uri = $request->get('redirect',env('SHOP_DOMAIN'));
        $token_key = 'h:login_info:'.$_SERVER['token'];
        Redis::del($token_key);
        return redirect($redirect_uri);
    }

    /**
     * 验证webtoken
     */
    public function checkToken(Request $request)
    {
        $token = $request->get('token');
//        dd($token);
        if(empty($token)){          // 空token
            $response = [
                'errno' => 400003,
                'msg'   => '未授权'
            ];
            return $response;
        }

        $token_key = 'h:login_info:'.$token;
        $u = Redis::hGetAll($token_key);

        if($u)        // 登录有效
        {
            $response = [
                'errno' => 0,
                'msg'   => 'ok',
                'data'  => [
                    'u' => $u
                ]
            ];
        }else{
            $response = [
                'errno' => 400003,
                'msg'   => '未授权'
            ];
        }

        return $response;

    }
}

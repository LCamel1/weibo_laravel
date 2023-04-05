<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    /**
     *  登录页面（创建会话的页面）
     */
    public function create()
    {
        return view('sessions.create');
    }

    /**
     * 执行登录(新建会话)
     */
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        if(Auth::attempt($credentials)){
            //登录成功后
            session()->flash('success', '欢迎回来！');
            return  redirect()->route('users.show', [Auth::user()]);//跳转个人页面
        }else{
            //登录失败后
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();//返回登录页面
        }

        return;
    }

    /**
     * 退出
     */
    public function destory()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');//退出后重定向到登录页面

    }
}

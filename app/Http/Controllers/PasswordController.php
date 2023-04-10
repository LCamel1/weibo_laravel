<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class PasswordController extends Controller
{
    public function __construct()
    {
        //密码重置邮件限流 —— 10 分钟内只能尝试 3 次
        $this->middleware('throttle:3,10', [
            'only' => ['sendResetLinkEmail']
        ]);
    }
    /**
     * 密码重置提交邮箱页面
     */
    public function showLinkRequestForm()
    {
        return view('passwords.email');
    }

    /**
     * 发送密码重置邮件
     */
    public function sendResetLinkEmail(Request $request)
    {
        //1.验证邮箱
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        //2.根据邮箱获取用户信息
        $user = User::where("email", $email)->first();

        //3.如果不存在
        if (is_null($user)) {
            session()->flash('danger', '邮箱未注册');
            return  redirect()->back()->withInput();
        }

        //4.生成Token，将在视图emails.reset_link里拼接链接
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        //5.把重置信息保存于数据库， 使用 updateOrInsert 来保持email唯一
        DB::table('password_resets')->updateOrInsert(['email' => $email], [
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => new Carbon
        ]);

        //6.将Token链接发送给用户
        Mail::send('emails.reset_link', compact('token'), function ($message) use ($email) {
            $message->to($email)->subject("忘记密码");
        });

        session()->flash('success', '重置密码邮件已发送，请注意查收');
        return redirect()->back();
    }

    /**
     * 更新密码页面
     */
    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token');
        return view('passwords.reset', compact('token'));
    }

    /**
     * 执行密码修改
     */
    public function reset(Request $request)
    {
        //1.验证数据是否合规
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6'
        ]);
        $email= $request->email;
        $token = $request->token;

        //找回密码链接的有效时间
        $expires = 60 * 60;

        //2.根据邮箱获取用户信息并判断用户是否存在
         $user = User::where("email", $email)->first();
         if (is_null($user)) {
            session()->flash('danger', '邮箱未注册');
            return  redirect()->back()->withInput();
        }

        //3.获取发送邮件时记录的重置信息(包括时间)
        $record = (array) DB::table('password_resets')->where('email', $email)->first();
        if ($record) {
            //判断是否过期了
            if (Carbon::parse($record['created_at'])->addSeconds($expires)->isPast()) {
                session()->flash('danger', '链接已过期，请重新尝试！');
                return redirect()->back();
            }

            //判断token令牌是否正确
            if ( !Hash::check($token, $record['token'])) {
                session()->flash('danger', '令牌错误');
                return redirect()->back();
            }

            //执行更新
            if ($user->update(['password' => bcrypt($request->password)])) {
                session()->flash('success', '密码重置成功，请使用新密码登录');
                return redirect()->route('login');
            } else {
                session()->flash('danger', '密码重置失败！');
                return redirect()->back();
            }
        }
        session()->flash('danger', '未找到重置记录');
        return redirect()->back();
    }
}

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
    /**
     * 密码重置
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
}

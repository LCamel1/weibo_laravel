<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        //过滤未登录用户的 edit, update 动作
        $this->middleware('auth', [
            'except' => ['show','create','store','index', 'confirmEmail']
        ]);
        //只让未登录用户访问注册页面
        $this->middleware('guest', [
            'only'=>['create']
        ]);
    }
    /**
     * 用户列表
     */
    public function index()
    {
        $users = User::paginate(5);
        return view('users.index',compact('users'));
    }
    /**
     * 注册页面
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * 个人中心
     */
    public function show(User $user)
    {
        return view('users.show', compact('user')); #compact 方法将用户对象 $user转化为一个关联数组
    }

    /**
     * 用户创建(注册操作)
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        //用户注册后需要激活邮箱才能登录
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收！');
        return redirect('/');
    }
    /**
     * 邮件发送
     */
    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'LCamel@example.com';
        $name = 'Lcamel';
        $to = $user->email;
        $subject = "感谢注册 weibo 应用！请确认你的邮箱";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject){
            $message->from($from,$name)->to($to)->subject($subject);
        });
    }

     /**
     * 邮件激活
     */
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user); //激活后直接登录
        session()->flash('success', '恭喜你，激活成功！');

        return redirect()->route('users.show', [$user]); # 注册成功重定向跳转show页面，等同于redirect()->route('users.show', [$user->id])
    }

    /**
     * 编辑用户页面
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);//用户只能编辑自己的资料
        return view('users.edit', compact('user'));
    }

    /**
     * 执行编辑操作
     */
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);//用户只能编辑自己的资料
         $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $updateData = array();
        if ($request->name != $user->name) {
            //判断修改的用户名是否已存在
            if (User::where('name', '=', $request->name)->first()) {
                session()->flash('danger', '修改失败，该用户名已存在！');
                return redirect()->back()->withInput();//返回
            }
            $updateData['name'] = $request->name;
        }
        if ($request->password) {
            $updateData['password'] = bcrypt($request->password);
        }

        if (!empty($updateData)) {
            $user->update($updateData);
            session()->flash('success', '个人资料更新成功！');
            return redirect()->route('users.show', $user->id);
        } else {
            session()->flash('warning', '没有需要更新的数据，请先修改！');
            return redirect()->back()->withInput();//返回
        }
    }

    /**
     * 删除用户
     */
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);////只允许已登录的 管理员 进行删除操作
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

}

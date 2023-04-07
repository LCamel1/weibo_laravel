<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\User;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        //过滤未登录用户的 edit, update 动作
        $this->middleware('auth', [
            'except' => ['show','create','store','index']
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
        $users = User::paginate(2);
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
        Auth::login($user);//注册成功后自动登录
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程～');
        return redirect()->route('users.show', [$user]); # 注册成功重定向跳转show页面，等同于redire()->route('users.show', [$user->id])
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
}

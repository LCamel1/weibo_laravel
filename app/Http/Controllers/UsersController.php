<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\User;

class UsersController extends Controller
{
    //
    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user')); #compact 方法将用户对象 $user转化为一个关联数组
    }
}

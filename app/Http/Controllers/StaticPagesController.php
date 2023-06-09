<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaticPagesController extends Controller
{

    /**
     * 首页
     */
    public function home()
    {
        //获取当前用户的微博信息 Auth::check()判断是否已登录
        $feed_items = [];
        if (Auth::check()) {
            $feed_items = Auth::user()->feed()->paginate(15);
        }
        return view('static_pages/home', compact('feed_items'));
    }

    /**
     * 帮助页
     */
    public function help()
    {
        return view('static_pages/help');
    }

    /**
     * 关于
     */
    public function about()
    {
        return view('static_pages/about');
    }
}

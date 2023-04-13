<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function($user) {
            $user->activation_token = Str::random(30);
        });
    }

    /**
     * 用户头像
     */
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://cravatar.cn/avatar/$hash?s=$size";
    }

    /**
     * 一个用户拥有多条微博
     */
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }
    /**
     * 获取当前登录用户 及 其关注人 的所有的微博
     */
    public function feed()
    {
        $user_ids = $this->followings->pluck('id')->toArray();
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
                            ->with('user')
                            ->orderBy('created_at', 'desc');
    }

    /**
     * 获取粉丝关系列表
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }
    /**
     * 获取用户关注人列表
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * 关注
     */
    public function follow($user_ids)
    {
        if ( !is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);//attach 方法或 sync 方法在中间表上创建一个多对多记录
    }

    /**
     * 取消关注
     */
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);//detach 方法在中间表上移除一个记录
    }

    /**
     * 判断当前登录的用户A是否关注了用户B
     */
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }

}

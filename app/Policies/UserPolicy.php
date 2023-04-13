<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 修改权限：必须登录 且 只能修改自己的信息
     */
    public function update(User $currentUser, User $user)
    {
        return  $currentUser->id === $user->id;
    }

    /**
     * 删除权限：登录 ，是管理员 ，管理员不能删除自己
     */
    public function destroy(User $currentUser, User $user)
    {
        return $currentUser->is_admin && ($currentUser->id !== $user->id);
    }

    /**
     * 关注权限：登录 且 自己不能关注自己
     */
    public function follow(User $currentUser, User $user)
    {
        return $currentUser->id !== $user->id;
    }

}

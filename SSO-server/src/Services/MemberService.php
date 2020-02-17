<?php

namespace ComocoSsoDemo\SsoServer\Services;

use Illuminate\Database\Capsule\Manager as Capsule;

class MemberService
{
    /**
     * 驗證帳號密碼是否正確
     *
     * @param  string $account
     * @param  string $password
     * @return bool
     */
    public static function validateAccountPassword(string $username, string $password): bool
    {
        $account = Capsule::table('account')->where('username', '=', $username)->first();

        return !($account == null || !password_verify($password, $account->password));
    }
}

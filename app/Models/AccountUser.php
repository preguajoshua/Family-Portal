<?php

namespace App\Models;

use App\Models\Concerns\AppDbBase;
use App\Models\Membership\UserApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountUser extends AppDbBase
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "account_user";

    //
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    //
    public static function createAccountUserOnFamilyPortal(string $userId, string $accountId, object $request, $loginId)
    {
        $accountUser = self::query()
            ->where('user_id',  $userId)
            ->where('account_id',  $accountId)
            ->first();

        if ($accountUser) {
            return $accountUser;
        }

        $accountUser =  self::create(
            [
                "user_id" => $userId,
                "account_id" => $accountId,
            ],
        );

        // create application user on membership
        UserApplication::createUserApplicationOnMembership($request, $loginId, $userId);
        return $accountUser;
    }

    //
    public static function createAccountUserForExistingUser($oldUserAccountId, $newUser)
    {
        return self::create(
            [
                "user_id" => $newUser->id,
                "account_id" => $oldUserAccountId,
            ],
        );
    }

    //
    public static function fetchAccountUserWithUserIdAndAccountId($userId, $accountId)
    {
        return self::query()
            ->where('user_id', $userId)
            ->where('account_id', $accountId)
            ->first();
    }
}

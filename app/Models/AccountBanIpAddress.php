<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountBanIpAddress extends Model
{
    protected $fillable = [
        'account_ban_id',
        'ip_hash',
        'masked_ip',
    ];

    protected $hidden = [
        'ip_hash',
    ];

    public function accountBan()
    {
        return $this->belongsTo(AccountBan::class);
    }
}

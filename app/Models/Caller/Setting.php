<?php

namespace App\Models\Caller;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'caller_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    protected $fillable = [
        'key',
        'command_class',
        'label',
        'description',
        'default_template',
        'template',
    ];
}

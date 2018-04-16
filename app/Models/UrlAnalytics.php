<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlAnalytics extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url_id',
        'user_id',
        'request_time',
        'http_host',
        'http_referer',
        'http_user_agent',
        'remote_addr',
        'request_uri',
        'get_data',
        'custom_headers',
        'response_code',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'get_data' => 'array',
        'post_data' => 'array',
        'custom_headers' => 'array',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UrlAnalytics
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $url_id
 * @property string|null $request_time
 * @property string|null $http_host
 * @property string|null $http_referer
 * @property string|null $http_user_agent
 * @property string|null $remote_addr
 * @property string|null $request_uri
 * @property array $get_data
 * @property array $custom_headers
 * @property int|null $response_code
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereCustomHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereGetData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereHttpHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereHttpReferer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereHttpUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereRemoteAddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereRequestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereRequestUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereResponseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereUrlId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UrlAnalytics whereUserId($value)
 * @mixin \Eloquent
 */
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

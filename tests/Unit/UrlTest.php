<?php

namespace Tests\Unit;

use App\Models\EmailVerification;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function url_has_user()
    {
        $user = create(User::class);
        $url = create(Url::class, ['user_id' => $user->id]);
        $this->assertEquals($user->id, $url->user->id);
    }
}

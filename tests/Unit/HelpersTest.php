<?php

namespace Tests\Unit;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    /** @test */
    public function docker_secret_retrieves_a_secret_from_a_file()
    {
        $secretFile = tmpfile();
        $secretFilePath = stream_get_meta_data($secretFile)['uri'];
        fwrite($secretFile, 'my-secret-value');

        $secret = docker_secret(basename($secretFilePath), dirname($secretFilePath));

        $this->assertEquals('my-secret-value', $secret);
    }
}

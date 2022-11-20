<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateStaticHtml extends Command
{
    protected $signature = 'html:generate';
    protected $description = 'Generate static HTML pages.';

    public function handle()
    {
        $html = view('platform.errors.5xx')->render();
        file_put_contents(public_path().'/5xx.html', $html);
    }
}

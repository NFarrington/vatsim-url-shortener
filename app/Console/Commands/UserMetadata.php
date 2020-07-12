<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class UserMetadata extends Command
{
    protected $name = 'ide-helper:user-meta';
    protected $description = 'Generate metadata for PhpStorm';

    public function handle()
    {
        $userMetadata = [];

        $defaultGuard = config('auth.defaults.guard');
        $guards = config('auth.guards');
        foreach ($guards as $guardName => $guardSettings) {
            $provider = config("auth.guards.$guardName.provider");
            $model = config("auth.providers.$provider.model");
            if ($guardName === $defaultGuard) {
                $userMetadata[] = ['name' => '', 'entity' => $model];
            }
            $userMetadata[] = ['name' => $guardName, 'entity' => $model];
        }

        $content = app('view')->make('metadata/users', [
            'users' => $userMetadata,
        ])->render();

        $filename = $this->option('filename');
        $written = app('files')->put($filename, $content);

        if ($written !== false) {
            $this->info("A new meta file was written to $filename");
        } else {
            $this->error("The meta file could not be created at $filename");
        }
    }

    protected function getOptions()
    {
        $defaultFilename = '.phpstorm.meta.php/ide-helper.user-meta.php';

        return [
            ['filename', 'F', InputOption::VALUE_OPTIONAL, 'The path to the meta file', $defaultFilename],
        ];
    }
}

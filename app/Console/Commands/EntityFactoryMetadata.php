<?php

namespace App\Console\Commands;

use Barryvdh\LaravelIdeHelper\Factories;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class EntityFactoryMetadata extends Command
{
    protected $name = 'ide-helper:entity-meta';
    protected $description = 'Generate metadata for PhpStorm';

    public function handle()
    {
        $factories = Factories::all();

        $content = app('view')->make('metadata/entities', [
            'factories' => $factories,
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
        $defaultFilename = '.phpstorm.meta.php/ide-helper.entity-meta.php';

        return [
            ['filename', 'F', InputOption::VALUE_OPTIONAL, 'The path to the meta file', $defaultFilename],
        ];
    }
}

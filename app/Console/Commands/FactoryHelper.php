<?php

namespace App\Console\Commands;

use Barryvdh\LaravelIdeHelper\Factories;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class FactoryHelper extends Command
{
    protected $name = 'ide-helper:generate-factories';
    protected $description = 'Generate metadata for PhpStorm';

    public function handle()
    {
        $factories = Factories::all();

        $content = app('view')->make('metadata/entity-helper', [
            'factories' => $factories,
        ])->render();

        $filename = $this->option('filename');
        $written = app('files')->append($filename, $content);

        if ($written !== false) {
            $this->info("A new meta file was written to $filename");
        } else {
            $this->error("The meta file could not be created at $filename");
        }
    }

    protected function getOptions()
    {
        $defaultFilename = '_ide_helper.php';

        return [
            ['filename', 'F', InputOption::VALUE_OPTIONAL, 'The path to the meta file', $defaultFilename],
        ];
    }
}

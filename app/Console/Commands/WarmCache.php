<?php

namespace App\Console\Commands;

use App\Repositories\UrlRepository;
use App\Services\UrlService;
use Illuminate\Console\Command;

class WarmCache extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Warm the application cache.';
    protected UrlService $urlService;
    protected UrlRepository $urlRepository;

    public function __construct(UrlService $urlService, UrlRepository $urlRepository)
    {
        parent::__construct();

        $this->urlService = $urlService;
        $this->urlRepository = $urlRepository;
    }

    public function handle()
    {
        $page = 1;
        $morePages = true;
        while ($morePages) {
            $urls = $this->urlRepository->findAll('id', 'asc', 200, $page);
            $this->info("Caching URL batch {$urls->firstItem()} to {$urls->lastItem()}.");
            foreach ($urls as $url) {
                $this->urlService->addUrlToCache($url);
            }
            $morePages = $urls->hasMorePages();
            $page++;
        }
    }
}

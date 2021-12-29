<?php

namespace App\Console\Commands;

use App\Repositories\UrlRepository;
use Aws\Middleware;
use Aws\SimpleDb\SimpleDbClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;

class SyncSimpleDb extends Command
{
    protected $signature = 'simpledb:sync';
    protected $description = 'Sync URLs to SimpleDB.';
    protected SimpleDbClient $simpleDbClient;
    protected UrlRepository $urlRepository;

    public function handle()
    {
        $this->simpleDbClient = app(SimpleDbClient::class);
        $this->urlRepository = app(UrlRepository::class);

        $now = Carbon::now()->toIso8601ZuluString();
        $updateCutoff = Carbon::now()->subMinute()->toIso8601ZuluString();

        // sync all URLs
        $page = 1;
        $morePages = true;
        while ($morePages) {
            $urls = $this->urlRepository->findAll('id', 'asc', 25, $page);
            $this->info("Syncing URL batch {$urls->firstItem()} to {$urls->lastItem()}.");
            $items = [];
            foreach ($urls as $url) {
                $items[] = [
                    'Name' => Str::lower($url->getFullUrl()),
                    'Attributes' => [
                        ['Name' => 'RedirectUrl', 'Value' => $url->getRedirectUrl(), 'Replace' => true],
                        ['Name' => 'UpdatedAt', 'Value' => $now, 'Replace' => true],
                    ],
                ];
            }

            $batchPutAttributesCommand = $this->simpleDbClient->getCommand('batchPutAttributes', [
                'DomainName' => 'VatsimUrlShortenerUrls',
                'Items' => $items,
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            ]);

            $batchPutAttributesCommand->getHandlerList()->appendBuild(
                Middleware::mapRequest(function (RequestInterface $request) {
                    return $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                }),
                'add-header'
            );
            $this->simpleDbClient->execute($batchPutAttributesCommand);

            $morePages = $urls->hasMorePages();
            $page++;
        }

        // remove URLs that haven't been updated
        $iterator = $this->simpleDbClient->getIterator('Select', [
            'SelectExpression' => "select * from VatsimUrlShortenerUrls where UpdatedAt < '{$updateCutoff}'",
        ]);
        foreach ($iterator as $item) {
            $this->info("Deleting URL '{$item['Name']}'.");
            $this->simpleDbClient->deleteAttributes([
                'DomainName' => 'VatsimUrlShortenerUrls',
                'ItemName' => $item['Name'],
            ]);
        }
    }
}

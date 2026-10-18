<?php

namespace App\Console\Commands;

use App\Repositories\UrlRepository;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SyncDynamoDb extends Command
{
    protected $signature = 'dynamodb:sync';
    protected $description = 'Sync URLs to DynamoDB.';
    protected DynamoDbClient $dynamoDbClient;
    protected UrlRepository $urlRepository;
    protected string $tableName;

    public function handle()
    {
        $this->dynamoDbClient = app(DynamoDbClient::class);
        $this->urlRepository = app(UrlRepository::class);
        $this->tableName = config('services.dynamodb.urls_table');

        $now = Carbon::now()->toIso8601ZuluString();
        $updateCutoff = Carbon::now()->subMinute()->toIso8601ZuluString();

        // sync all URLs
        $page = 1;
        $morePages = true;
        while ($morePages) {
            $urls = $this->urlRepository->findAllOrdered('id', 'asc', 25, $page);
            $this->info("Syncing URL batch {$urls->firstItem()} to {$urls->lastItem()}.");
            $requests = [];
            foreach ($urls as $url) {
                $requests[] = [
                    'PutRequest' => [
                        'Item' => [
                            'Url' => ['S' => Str::lower($url->getFullUrl())],
                            'RedirectUrl' => ['S' => $url->getRedirectUrl()],
                            'UpdatedAt' => ['S' => $now],
                        ],
                    ],
                ];
            }
            $this->batchWrite($requests);

            $morePages = $urls->hasMorePages();
            $page++;
        }

        // remove URLs that haven't been updated
        $paginator = $this->dynamoDbClient->getPaginator('Scan', [
            'TableName' => $this->tableName,
            'ProjectionExpression' => '#url',
            'FilterExpression' => '#updatedAt < :updateCutoff',
            'ExpressionAttributeNames' => ['#url' => 'Url', '#updatedAt' => 'UpdatedAt'],
            'ExpressionAttributeValues' => [':updateCutoff' => ['S' => $updateCutoff]],
            'ConsistentRead' => true,
        ]);
        foreach ($paginator->search('Items') as $item) {
            $this->info("Deleting URL '{$item['Url']['S']}'.");
            $this->dynamoDbClient->deleteItem([
                'TableName' => $this->tableName,
                'Key' => ['Url' => $item['Url']],
            ]);
        }
    }

    protected function batchWrite(array $requests)
    {
        $attempt = 0;
        while (! empty($requests)) {
            if ($attempt > 0) {
                usleep(min(2 ** $attempt * 50000, 2000000));
            }

            $result = $this->dynamoDbClient->batchWriteItem([
                'RequestItems' => [$this->tableName => $requests],
            ]);

            $requests = $result['UnprocessedItems'][$this->tableName] ?? [];
            $attempt++;
        }
    }
}

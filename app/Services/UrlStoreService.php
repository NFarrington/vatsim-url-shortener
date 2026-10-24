<?php

namespace App\Services;

use App\Entities\Url;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Keeps the key-value copy of the URLs in DynamoDB, read by the redirector Lambda, up to date.
 */
class UrlStoreService
{
    protected DynamoDbClient $dynamoDbClient;

    public function __construct(DynamoDbClient $dynamoDbClient)
    {
        $this->dynamoDbClient = $dynamoDbClient;
    }

    public function createOrUpdate(Url $url): void
    {
        $this->dynamoDbClient->putItem([
            'TableName' => config('services.dynamodb.urls_table'),
            'Item' => [
                'Url' => ['S' => Str::lower($url->getFullUrl())],
                'RedirectUrl' => ['S' => $url->getRedirectUrl()],
                'UpdatedAt' => ['S' => Carbon::now()->toIso8601ZuluString()],
            ],
        ]);
    }

    public function delete(Url $url): void
    {
        $this->dynamoDbClient->deleteItem([
            'TableName' => config('services.dynamodb.urls_table'),
            'Key' => [
                'Url' => ['S' => Str::lower($url->getFullUrl())],
            ],
        ]);
    }
}

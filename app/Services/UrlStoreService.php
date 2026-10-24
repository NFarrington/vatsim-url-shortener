<?php

namespace App\Services;

use App\Entities\Url;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Middleware;
use Aws\SimpleDb\SimpleDbClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;

/**
 * Keeps the key-value copy of the URLs in SimpleDB and DynamoDB, read by the redirector Lambda, up to date.
 */
class UrlStoreService
{
    const simpleDbDomainName = 'VatsimUrlShortenerUrls';

    protected SimpleDbClient $simpleDbClient;
    protected DynamoDbClient $dynamoDbClient;

    public function __construct(SimpleDbClient $simpleDbClient, DynamoDbClient $dynamoDbClient)
    {
        $this->simpleDbClient = $simpleDbClient;
        $this->dynamoDbClient = $dynamoDbClient;
    }

    public function createOrUpdate(Url $url): void
    {
        $this->createOrUpdateUrlInSimpleDb($url);
        $this->createOrUpdateUrlInDynamoDb($url);
    }

    public function delete(Url $url): void
    {
        $this->simpleDbClient->deleteAttributes([
            'DomainName' => self::simpleDbDomainName,
            'ItemName' => Str::lower($url->getFullUrl()),
        ]);
        $this->dynamoDbClient->deleteItem([
            'TableName' => config('services.dynamodb.urls_table'),
            'Key' => [
                'Url' => ['S' => Str::lower($url->getFullUrl())],
            ],
        ]);
    }

    protected function createOrUpdateUrlInSimpleDb(Url $url): void
    {
        $putAttributesCommand = $this->simpleDbClient->getCommand('putAttributes', [
            'DomainName' => self::simpleDbDomainName,
            'ItemName' => Str::lower($url->getFullUrl()),
            'Attributes' => [
                ['Name' => 'RedirectUrl', 'Value' => $url->getRedirectUrl(), 'Replace' => true],
                ['Name' => 'UpdatedAt', 'Value' => Carbon::now()->toIso8601ZuluString(), 'Replace' => true],
            ],
        ]);

        $putAttributesCommand->getHandlerList()->appendBuild(
            Middleware::mapRequest(function (RequestInterface $request) {
                return $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            }),
            'add-header'
        );

        $this->simpleDbClient->execute($putAttributesCommand);
    }

    protected function createOrUpdateUrlInDynamoDb(Url $url): void
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
}

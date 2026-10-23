<?php

namespace App\Rules;

use App\Entities\Domain;
use App\Entities\Url;
use App\Repositories\DomainRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\UrlRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * A short URL that isn't reserved and that no existing URL has, ignoring case.
 *
 * URLs are stored in SimpleDB and DynamoDB by their lowercased full URL, so this compares
 * the full URL (domain, prefix and URL) rather than relying on the database's collation
 * or on which organization owns a prefix. A URL without a prefix also can't equal an
 * organization's prefix.
 */
class AvailableShortUrl implements ValidationRule
{
    public function __construct(private mixed $domainId, private mixed $prefix)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        if (in_array(Str::lower($value), Url::RESERVED_PATHS, true)) {
            $fail('The :attribute is reserved.');

            return;
        }

        // Other rules report a missing or invalid domain or prefix.
        if (!is_numeric($this->domainId) || ($this->prefix !== null && !is_string($this->prefix))) {
            return;
        }

        /** @var Domain|null $domain */
        $domain = app(DomainRepository::class)->find((int) $this->domainId);
        if (!$domain) {
            return;
        }

        // A root URL that equals an organization's prefix would look like it belongs to that organization.
        if (!$this->prefix && app(OrganizationRepository::class)->findOneByPrefixIgnoringCase($value)) {
            $fail('The :attribute is already used as an organization prefix.');

            return;
        }

        if (app(UrlRepository::class)->existsIgnoringCase($domain->getUrl(), $value, $this->prefix ?: null)) {
            $fail('The :attribute has already been taken.');
        }
    }
}

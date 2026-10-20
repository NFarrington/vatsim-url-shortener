<?php

namespace App\Rules;

use App\Entities\Url;
use App\Repositories\OrganizationRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * An organization prefix that isn't reserved and that no other organization has, ignoring case.
 *
 * Short URLs are stored in SimpleDB and DynamoDB by their lowercased full URL, so two
 * organizations sharing a prefix could overwrite each other's URLs there.
 */
class AvailablePrefix implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        if (in_array(Str::lower($value), Url::RESERVED_PATHS, true)) {
            $fail('The :attribute is reserved.');

            return;
        }

        if (app(OrganizationRepository::class)->findOneByPrefixIgnoringCase($value)) {
            $fail('The :attribute is already used by another organization.');
        }
    }
}

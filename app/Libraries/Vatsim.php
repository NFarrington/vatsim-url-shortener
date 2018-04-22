<?php

namespace App\Libraries;

use App\Exceptions\Cert\InvalidResponseException;
use SimpleXMLElement;

class Vatsim
{
    /**
     * Create a new user from their Cert data.
     *
     * @param int $id
     * @return array
     * @throws \App\Exceptions\Cert\InvalidResponseException
     */
    public function getUser(int $id)
    {
        $client = app('guzzle');
        $url = "https://cert.vatsim.net/vatsimnet/idstatusint.php?cid={$id}";
        $res = $client->get($url);

        $data = json_decode(json_encode(new SimpleXMLElement($res->getBody())), true);
        $user = $data['user'];
        $user['id'] = $user['@attributes']['cid'];
        unset($user['@attributes']);

        if ($user['id'] != $id) {
            throw new InvalidResponseException("User ID {$user['id']} does not match expected {$id}");
        }

        $missingKeys = array_diff(['id', 'name_first', 'name_last'], array_keys($user));
        if (collect($missingKeys)->isNotEmpty()) {
            $missingKeys = implode(',', $missingKeys);
            throw new InvalidResponseException("Missing keys from {$url}: {$missingKeys}");
        }

        return $user;
    }
}

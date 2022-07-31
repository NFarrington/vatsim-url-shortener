<?php

return [
    'base_url' => env('VATSIM_CONNECT_BASE_URL', 'https://auth.vatsim.net'),
    'client_id' => env('VATSIM_CONNECT_CLIENT_ID'),
    'client_secret' => docker_secret(env('VATSIM_CONNECT_CLIENT_SECRET_SECRET')) ?: env('VATSIM_CONNECT_CLIENT_SECRET'),
    'scopes' => explode(',', env('VATSIM_CONNECT_SCOPES', 'full_name,email,vatsim_details')),
];

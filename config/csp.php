<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enforce the Content Security Policy
    |--------------------------------------------------------------------------
    |
    | When false, the policy is sent as Content-Security-Policy-Report-Only:
    | browsers log violations to the console but block nothing.
    |
    */

    'enforce' => (bool) env('CSP_ENFORCE', false),

];

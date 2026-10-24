<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enforce the Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Enforced by default. Set CSP_ENFORCE=false to send the policy as
    | Content-Security-Policy-Report-Only instead: browsers log violations
    | to the console but block nothing.
    |
    */

    'enforce' => (bool) env('CSP_ENFORCE', true),

];

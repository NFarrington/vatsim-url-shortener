<script type="text/javascript" nonce="{{ $cspNonce ?? '' }}">
    window.appEnv = {
        environment: '{{ config('app.env') }}',
        version: '{{ config('app.version.name') }}',
    };
</script>

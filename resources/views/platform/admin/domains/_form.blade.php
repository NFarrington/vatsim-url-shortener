<div class="form-group row">
    <label for="inputUrl" class="col-sm-2 col-form-label">URL</label>
    <div class="col-sm-10">
        <input type="text" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}"
               id="inputUrl" name="url" value="{{ old('url') ?: $domain->url }}"
               placeholder="https://domain.tld/" required maxlength="250" autofocus>
        @if ($errors->has('url'))
            <div class="invalid-feedback">
                {{ $errors->first('url') }}
            </div>
        @endif
    </div>
</div>

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

<div class="form-group row">
    <div class="offset-sm-2 col-sm-10">
        <div class="custom-control custom-checkbox">
            <input type="hidden" name="public" value="0">
            <input class="custom-control-input{{ $errors->has('public') ? ' is-invalid' : '' }}" type="checkbox"
                   id="inputPublic" name="public"
                   value="1" {{ (old('public') ?: $domain->public) ? ' checked' : '' }}>
            <label class="custom-control-label" for="inputPublic">Public</label>
            @if ($errors->has('public'))
                <div class="invalid-feedback">
                    {{ $errors->first('public') }}
                </div>
            @endif
        </div>
    </div>
</div>

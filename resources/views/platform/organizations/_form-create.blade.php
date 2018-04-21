<div class="form-group row">
    <label for="inputName" class="col-sm-2 col-form-label">Name</label>
    <div class="col-sm-10">
        <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
               id="inputName" name="name" value="{{ old('name') ?: $organization->name }}"
               placeholder="Name" required maxlength="50" autofocus>
        @if ($errors->has('name'))
            <div class="invalid-feedback">
                {{ $errors->first('name') }}
            </div>
        @endif
    </div>
</div>

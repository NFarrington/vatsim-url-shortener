<div class="row mb-3">
    <label for="inputTitle" class="col-sm-2 col-form-label">Title</label>
    <div class="col-sm-10">
        <input type="text" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
               id="inputTitle" name="title" value="{{ old('title') ?: $news->getTitle() }}"
               placeholder="Title" required maxlength="250" autofocus>
        @if ($errors->has('title'))
            <div class="invalid-feedback">
                {{ $errors->first('title') }}
            </div>
        @endif
    </div>
</div>

<div class="row mb-3">
    <label for="inputContent" class="col-sm-2 col-form-label">Content</label>
    <div class="col-sm-10">
        <textarea type="text" class="form-control{{ $errors->has('content') ? ' is-invalid' : '' }}"
                  id="inputContent" name="content" placeholder="Content" required
                  maxlength="10000" rows="10">{{ old('content') ?: $news->getContent() }}</textarea>
        @if ($errors->has('content'))
            <div class="invalid-feedback">
                {{ $errors->first('content') }}
            </div>
        @endif
        <div class="form-text">
            Markdown-formatted content.
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="offset-sm-2 col-sm-10">
        <div class="form-check">
            <input class="form-check-input{{ $errors->has('published') ? ' is-invalid' : '' }}" type="checkbox"
                   id="inputPublished" name="published"
                   value="1" {{ (old('published') ?: $news->isPublished()) ? ' checked' : '' }}>
            <label class="form-check-label" for="inputPublished">Published</label>
            @if ($errors->has('published'))
                <div class="invalid-feedback">
                    {{ $errors->first('published') }}
                </div>
            @endif
        </div>
    </div>
</div>

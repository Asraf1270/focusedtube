<div class="card p-6 space-y-4">
    <div>
        <label for="title" class="label">Title</label>
        <input id="title" name="title" type="text" required
               value="{{ old('title', $playlist->title) }}" class="input">
        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="slug" class="label">Slug</label>
        <input id="slug" name="slug" type="text"
               value="{{ old('slug', $playlist->slug) }}"
               placeholder="auto from title" class="input">
        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="label">Description</label>
        <textarea id="description" name="description" rows="4" class="input">{{ old('description', $playlist->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="thumbnail_url" class="label">Thumbnail URL</label>
        <input id="thumbnail_url" name="thumbnail_url" type="url"
               value="{{ old('thumbnail_url', $playlist->thumbnail_url) }}" class="input">
        @error('thumbnail_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status" class="label">Status</label>
        <select id="status" name="status" class="input" required>
            <option value="draft"     @selected(old('status', $playlist->status) === 'draft')>Draft</option>
            <option value="published" @selected(old('status', $playlist->status) === 'published')>Published</option>
            @if ($playlist->status === 'archived')
                <option value="archived" @selected(old('status', $playlist->status) === 'archived')>Archived</option>
            @endif
        </select>
    </div>
</div>
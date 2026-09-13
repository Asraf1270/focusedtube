<div class="card p-6 space-y-4">
    <div>
        <label for="name" class="label">Name</label>
        <input id="name" name="name" type="text" required
               value="{{ old('name', $category->name) }}" class="input">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="slug" class="label">Slug</label>
        <input id="slug" name="slug" type="text"
               value="{{ old('slug', $category->slug) }}"
               placeholder="auto from name"
               class="input">
        <p class="mt-1 text-xs text-slate-500">Lowercase letters, numbers and dashes. Leave blank to auto-generate.</p>
        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="label">Description</label>
        <textarea id="description" name="description" rows="3" class="input">{{ old('description', $category->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="icon" class="label">Icon</label>
            <input id="icon" name="icon" type="text" maxlength="32"
                   placeholder="e.g. 💻"
                   value="{{ old('icon', $category->icon) }}" class="input">
            <p class="mt-1 text-xs text-slate-500">Emoji or short text.</p>
        </div>

        <div>
            <label for="sort_order" class="label">Sort order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="100000"
                   value="{{ old('sort_order', $category->sort_order) }}" class="input">
        </div>

        <div>
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input" required>
                <option value="active"   @selected(old('status', $category->status) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $category->status) === 'inactive')>Inactive</option>
            </select>
        </div>
    </div>

    <div>
        <label for="image" class="label">Cover image URL</label>
        <input id="image" name="image" type="url"
               placeholder="https://…"
               value="{{ old('image', $category->image) }}" class="input">
        @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
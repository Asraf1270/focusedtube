<?php

namespace App\Services\Category;

use App\Events\CategoryCreated;
use App\Events\CategoryDeleted;
use App\Events\CategoryUpdated;
use App\Models\Category;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly AnalyticsService $analytics,
    ) {
    }

    /**
     * @param  array{name:string, slug?:string|null, description?:string|null, icon?:string|null, image?:string|null, status?:string, sort_order?:int}  $data
     */
    public function create(array $data, User $admin): Category
    {
        $category = DB::transaction(fn () => Category::create([
            'name'        => $data['name'],
            'slug'        => $data['slug'] ?: null,
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? null,
            'image'       => $data['image'] ?? null,
            'status'      => $data['status'] ?? Category::STATUS_ACTIVE,
            'sort_order'  => $data['sort_order'] ?? 0,
        ]));

        $this->audit->record(
            action: 'category.created',
            description: "Created category \"{$category->name}\"",
            subject: $category,
        );

        Log::info('category.created', ['category_id' => $category->id, 'admin_id' => $admin->id]);

        event(new CategoryCreated($category, $admin));

        return $category;
    }

    public function update(Category $category, array $data, User $admin): Category
    {
        // Regenerate slug if name changed AND slug wasn't explicitly set.
        if (
            ! empty($data['name'])
            && $data['name'] !== $category->name
            && empty($data['slug'])
        ) {
            $data['slug'] = Category::query()
                ->where('slug', \Illuminate\Support\Str::slug($data['name']))
                ->where('id', '!=', $category->id)
                ->exists()
                    ? \Illuminate\Support\Str::slug($data['name']).'-'.now()->timestamp
                    : \Illuminate\Support\Str::slug($data['name']);
        }

        DB::transaction(fn () => $category->fill($data)->save());

        $changes = collect($category->getChanges())
            ->except(['updated_at'])
            ->all();

        if (! empty($changes)) {
            $this->audit->record(
                action: 'category.updated',
                description: "Updated category \"{$category->name}\" (".implode(', ', array_keys($changes)).')',
                subject: $category,
            );

            event(new CategoryUpdated($category, $admin, $changes));
        }

        return $category->refresh();
    }

    public function delete(Category $category, User $admin): void
    {
        // Videos keep their row but category_id becomes null via FK nullOnDelete.
        $name = $category->name;

        DB::transaction(fn () => $category->delete());

        $this->audit->record(
            action: 'category.deleted',
            description: "Deleted category \"{$name}\"",
        );

        // Reuse the ghost to fire the event.
        $ghost = new Category(['name' => $name, 'slug' => $category->slug]);
        $ghost->id = $category->id;

        event(new CategoryDeleted($ghost, $admin));
    }
}
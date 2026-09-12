@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.stat-card label="Total users"   :value="number_format($stats['total_users'])"      hint="All accounts" />
        <x-admin.stat-card label="Active users"  :value="number_format($stats['active_users'])"     hint="Not suspended" tone="success" />
        <x-admin.stat-card label="Total videos"  :value="number_format($stats['total_videos'])"     hint="All statuses" />
        <x-admin.stat-card label="Published"     :value="number_format($stats['published_videos'])" hint="Visible to users" tone="success" />
        <x-admin.stat-card label="Drafts"        :value="number_format($stats['draft_videos'])"     hint="Not yet published" tone="warning" />
        <x-admin.stat-card label="Total views"   :value="number_format($stats['total_views'])"      hint="Sum across videos" />
        <x-admin.stat-card label="Completed"     :value="number_format($stats['total_completed'])"  hint="Watch completions" />
        <x-admin.stat-card label="Watch time"    :value="gmdate('H:i:s', $stats['total_watch_time'])" hint="Approximate" />
    </div>

    <div class="mt-8">
        <x-admin.empty-state
            title="Detailed analytics coming soon"
            description="Charts for views over time, popular categories, and completion rates arrive in Step 15."
        />
    </div>
@endsection
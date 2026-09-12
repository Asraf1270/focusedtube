@extends('layouts.admin')

@section('title', $title)
@section('heading', $title)

@section('content')
    <x-admin.empty-state
        title="{{ $title }} — coming in a later step"
        description="This page is scaffolded. Functionality is added in the following build steps."
    />
@endsection
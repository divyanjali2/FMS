@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
    <h1 class="text-2xl font-bold mb-4">Your Profile</h1>

    @include('profile.partials.update-profile-information-form')
    <br>

    @include('profile.partials.delete-user-form')
</div>
@endsection
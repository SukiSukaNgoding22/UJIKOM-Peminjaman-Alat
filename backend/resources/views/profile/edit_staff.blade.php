@extends('layouts.app')

@section('title', 'Edit Profil')
@section('header-title', 'Edit Profil')

@section('content')
    <div class="max-w-2xl">
        @include('profile._form')
    </div>
@endsection

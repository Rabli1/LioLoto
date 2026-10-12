@php($pageTitle = 'Tous les jeux')
@extends('layouts.app')
@section('content')
@php($catalog = require resource_path('games.php'))
<main id="main-content" class="page-width">@include('shared.game-grid')</main>
@endsection

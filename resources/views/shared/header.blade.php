<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0c101b">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="description" content="LioLoto : dix jeux avec des points virtuels.">
    <title>{{ $pageTitle ?? 'L’arcade' }} · LioLoto</title>
    @vite(['resources/js/app.jsx', 'resources/css/app.css'])
</head>
<body>
<a class="skip-link" href="#main-content">Aller au contenu</a>

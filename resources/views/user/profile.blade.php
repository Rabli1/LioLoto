@include('shared.header')
@include('shared.navbar')
@php
    $users = json_decode(file_get_contents(base_path('database/json/users.json')), true);
    $id = request()->query('id');
    $user = collect($users)->firstWhere('id', intval($id));
@endphp
<h1 style="text-align: center; margin-top: 20px;">Carte du joueur</h1>
<body style="min-height: 100vh;">
    <div class="container py-5">
        <div class="card mx-auto shadow" style="max-width: 400px;">
            <div class="card-body text-center" style="background: {{ $user['couleurProfil'] ?? '#fff' }};">
                <i class="{{ $user['imageProfil'] }} fa-5x mb-3"></i>
                <h2 class="fw-bold">{{ $user['nom'] }}</h2>
                <p>{{ $user['bio'] }}</p>
                <hr>
                <ul class="list-group list-group-flush text-start mb-3">
                    <li class="list-group-item bg-transparent"><strong>Points :</strong> {{ $user['points'] }}</li>
                    <li class="list-group-item bg-transparent">
                        <img src="{{ asset('img/gold-medal.png') }}" alt="Or" style="width:24px;vertical-align:middle;" />
                        <strong>Or :</strong> {{ $user['or'] }}
                    </li>
                   <li class="list-group-item bg-transparent">
                        <img src="{{ asset('img/silver-medal.png') }}" alt="Argent" style="width:24px;vertical-align:middle;" />
                        <strong>Argent :</strong> {{ $user['argent'] }}
                    </li>
                    <li class="list-group-item bg-transparent">
                        <img src="{{ asset('img/bronze-medal.png') }}" alt="Bronze" style="width:24px;vertical-align:middle;" />
                        <strong>Bronze :</strong> {{ $user['bronze'] }}
                    </li>
                    <li class="list-group-item bg-transparent"><strong>Points perdus :</strong> {{ $user['pointPerdu'] }}</li>
                </ul>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</body>
</html>
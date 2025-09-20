<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lio Loto</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      background-color: #000;
      color: #fff;
    }

    .leaderboard-card {
      background: #111;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, .5);
    }

    .list-group-item {
      border: none;
    }

    .carousel-inner img {
      object-fit: cover;
      height: 400px;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  @include('shared.header')
  @include('shared.navbar')

  @include('shared.carousel')

  <!-- Profile + Leaderboard -->

  <div class="container mt-5">
    <div class="row">
      <!-- Player Profile -->
      <div class="col-lg-4 mb-4">
        <div class="leaderboard-card text-center">
          @if (session()->has('user'))

            <img src="https://via.placeholder.com/100/ffffff/000000?text=User" class="rounded-circle mb-3" alt="Profile">
            <h4 class="fw-bold">
              {{ session('user.nom') }}
              <p>Balance: <span class="fw-bold text-danger">{{ session('user.points') }}</span></p>

            </h4>
          @else
            <h4>Veuillez vous connecter</h4>
            <a href="/user/connection" class="btn btn-danger btn-md">Connexion</a>
          @endif
        </div>
      </div>

      <!-- Leaderboard -->
      @php
        // Charger les utilisateurs depuis le JSON
        $users = json_decode(file_get_contents(base_path('database/json/users.json')), true);
        // Trier par points décroissants et prendre les 3 premiers
        $topUsers = collect($users)->sortByDesc('points')->take(3);
      @endphp

      <div class="col-lg-8">
        <div class="leaderboard-card">
          <h4 style="text-align: center;" class="fw-bold mb-3">Top 3</h4>
          <ol class="list-group list-group-numbered">
            @foreach($topUsers as $user)
              <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
                <a href="/user/profile?id={{ $user['id'] }}" class="fw-bold text-white text-decoration-underline">
                  {{ $user['name'] }}
                </a>
                <span class="badge bg-success">{{ $user['points'] }} pts</span>
              </li>
            @endforeach
          </ol>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
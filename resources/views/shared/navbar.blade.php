@php
  $userConnected = session()->has("user");
  $currentPage = request()->path();
  $users = [];

  $userArray = null;
  if ($userConnected) {
    $u = session('user');

    if ($u->banned) {
      session()->forget('user');
      return redirect('user/connection?message=Votre compte a été banni');
    }

    $userArray = [
      'id' => $u->id,
      'name' => $u->name,
      'admin' => $u->admin,
      'profileImage' => $u->profileImage,
      'profileColor' => $u->profileColor,
      'points' => $u->points,
    ];
  }

  $jsonPath = base_path('database/json/users.json');
  if (file_exists($jsonPath)) {
    $allUsers = json_decode(file_get_contents($jsonPath), true);

    $users = collect($allUsers)
      ->reject(fn($user) => $user['banned'] ?? false)
      ->map(fn($user) => [
        'id' => $user['id'],
        'name' => $user['name'],
        'profileImage' => $user['profileImage'] ?? 'fa-user',
        'profileColor' => $user['profileColor'] ?? 'black',
      ])
      ->sortBy('name')
      ->values()
      ->toArray();
  }
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container d-flex align-items-center justify-content-start"> <a class="navbar-brand fw-bold"
      href="/">Lotoli</a>
    <div id="mainNav" style="flex-grow: 1;">
      <div id="react-navbar" class="navbar-nav" style="margin-left: 62%; margin-bottom: 4%; width: 50%;"
        data-user='@json($userArray)' data-curresnt-page="{{ $currentPage }}" data-users='@json($users)'>
      </div>
    </div>
</nav>
@viteReactRefresh
@vite(['resources/js/app.jsx'])
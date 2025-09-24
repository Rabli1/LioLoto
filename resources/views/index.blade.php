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
    height: 450px !important;
    object-fit: contain;
    background: #000;
  }

  </style>
</head>

<body>

  <!-- Navbar -->
  @include('shared.header')
  @include('shared.navbar')



@if(session()->has('user'))
<div id="profile" class="position-absolute" 
     style="z-index: 10; width: 220px; top: 100px; left: calc(100% - 240px); cursor: grab;">
    <div class="leaderboard-card text-center p-3">
        <i class="fa-solid {{ session()->get('user')->profileImage }} pfp-{{ session()->get('user')->profileColor }} fa-3x mb-2"></i>
        <h5 class="fw-bold mb-1">{{ session()->get('user')->name }}</h5>
        <p class="mb-1">Balance: <span class="fw-bold text-danger">{{ session()->get('user')->points }}</span></p>
    </div>
</div>
@endif

     <h1 style="text-align: center; margin-top: 70px;">Jeux en vedette      <svg data-ds-icon="Trending" width="30" height="30" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" class="inline-block shrink-0" style="color: rgb(177, 186, 211) !important;"><!--[!--><!--]--><path fill="currentColor" d="M12 1C5.92 1 1 5.92 1 12s4.92 11 11 11 11-4.92 11-11S18.08 1 12 1m7 12.23c-.55 0-1-.45-1-1V9.98l-5.88 5.88a.996.996 0 0 1-1.41 0l-2.29-2.29-2.71 2.71c-.2.2-.45.29-.71.29s-.51-.1-.71-.29a.996.996 0 0 1 0-1.41l3.42-3.42a.996.996 0 0 1 1.41 0l2.29 2.29 5.17-5.17h-2.25c-.55 0-1-.45-1-1s.45-1 1-1H19c.55 0 1 .45 1 1v4.67c0 .55-.45 1-1 1z"></path></svg><div class="container mt-4"></h1>
    @include('shared.carousel')

    @php
        $topUsers = collect($users)->sortByDesc('points')->take(3);
    @endphp

    <div class="row justify-content-center" style="margin-top: 50px;">
        <div class="col-lg-6">
            <div class="leaderboard-card">
                <h4 class="fw-bold text-center mb-3">Top 3 <svg data-ds-icon="Trophy" width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" class="inline-block shrink-0"><!----><path fill="currentColor" d="M21.08 4H19c0-1.1-.9-2-2-2H7c-1.1 0-2 .9-2 2H2.92C1.8 4 .9 4.91.92 6.03c.04 2.48.69 6.41 4.35 6.86A6.98 6.98 0 0 0 11 17.9v1.08h-1c-2.21 0-4 1.79-4 4h12c0-2.21-1.79-4-4-4h-1V17.9c2.76-.4 4.99-2.39 5.73-5.02 3.65-.46 4.31-4.38 4.35-6.86.02-1.12-.88-2.03-2-2.03zM4 10.11c-.57-.68-1.04-1.9-1.08-4.1H4zM16.11 9l-1.45 1.04.57 1.71c.34 1.03-.83 1.89-1.71 1.26l-1.51-1.08-1.51 1.08c-.88.63-2.05-.24-1.71-1.26l.57-1.71L7.91 9c-.89-.63-.44-2.03.65-2.03h1.82l.58-1.75c.34-1.02 1.79-1.02 2.13 0l.58 1.75h1.82c1.09 0 1.54 1.4.65 2.03zM20 10.1V6h1.08c-.04 2.21-.51 3.43-1.08 4.1"></path></svg></h4>
                <ol class="list-group list-group-numbered">
                    @foreach($topUsers as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
                            <a href="/user/profile?id={{ $user['id'] }}" class="fw-bold text-white" style="text-decoration: none;">
                                {{ $user['name'] }}
                            </a>
                            <span class="badge bg-success">{{ $user['points'] }} pts</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>

<div class="container mt-5">
    <h1 style="text-align: center; margin-top: 20px;">Jeux disponibles</h1>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img src='{{ asset("img/blackjack.jpg") }}' alt="BlackJack"></div>
                    <div class="row"><h3>BlackJack</h3></div>
                    <div class="row"><a href="/game/blackjack" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img src='{{ asset("img/plinko.webp") }}' alt="Plinko"></div>
                    <div class="row"><h3>Plinko</h3></div>
                    <div class="row"><a href="/game/plinko" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img src='{{ asset("img/mines.webp") }}' alt="Mines"></div>
                    <div class="row"><h3>Mines</h3></div>
                    <div class="row"><a href="/game/mines" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img src='{{ asset("img/gaben.jpg") }}' alt="Mines"></div>
                    <div class="row"><h3>Gaben.exe</h3></div>
                    <div class="row"><a href="/game/mines" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('shared.footer')


<script>
const profile = document.getElementById('profile')
let startX = 0, startY = 0, offsetX = 0, offsetY = 0

profile.addEventListener('mousedown', e => {
    startX = e.clientX
    startY = e.clientY

    const mouseMove = e => {
        const dx = e.clientX - startX
        const dy = e.clientY - startY

        offsetX += dx
        offsetY += dy

        profile.style.top = (profile.offsetTop + dy) + 'px'
        profile.style.left = (profile.offsetLeft + dx) + 'px'

        startX = e.clientX
        startY = e.clientY
    }

    const mouseUp = () => {
        document.removeEventListener('mousemove', mouseMove)
        document.removeEventListener('mouseup', mouseUp)
    }

    document.addEventListener('mousemove', mouseMove)
    document.addEventListener('mouseup', mouseUp)
})
</script>
<?php
 $userConnected = session()->has("user");
 $currentPage = request() -> path();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container">
      <a class="navbar-brand fw-bold" href="/">Lio Loto</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
              aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link @if($currentPage == "game") active @endif" href="/game">Jeux</a></li>
          <li class="nav-item"><a class="nav-link @if($currentPage == "leaderboard") active @endif" href="/leaderboard">Classement</a></li>
          @if($userConnected)
            <li class="nav-item"></li><a class="nav-link @if($currentPage == "user/profile") active @endif" href="/user/profile?id={{ session()->get('user')->id }}">Profil</a></li>
            <li class="nav-item"><a class="nav-link @if($currentPage == "user/deconnection") active @endif" href="/user/deconnection">Déconnexion</a></li>
          @else
            <li class="nav-item"><a class="nav-link @if($currentPage == "user/connection") active @endif" href='/user/connection?message='>Connexion</a></li>
            <li class="nav-item"><a class="nav-link @if($currentPage == "user/signIn") active @endif" href="/user/signIn">Inscription</a></li>
          @endif
          <li class="nav-item"><a class="nav-link @if($currentPage == "support") active @endif" href="/support">Support</a></li>

        </ul>
      </div>
    </div>
  </nav>
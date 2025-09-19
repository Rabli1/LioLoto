<?php
 $userConnected = session()->has("user")
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
          <li class="nav-item"><a class="nav-link" href="/game">Jeux</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Classement</a></li>
          @if($userConnected)
            <li class="nav-item"><a class="nav-link" href="/user/profile?id=3">Profil</a></li>
            <li class="nav-item"><a class="nav-link" href="/user/deconnection">Déconnexion</a></li>
          @else
            <li class="nav-item"><a class="nav-link" href='/user/connection?message='>Connexion</a></li>
            <li class="nav-item"><a class="nav-link" href="/user/signIn">Inscription</a></li>
          @endif
        </ul>
      </div>
    </div>
  </nav>
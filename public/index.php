<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LioLoto</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    body {
      background-color: #000; /* Black background */
      color: #fff;
    }
    .leaderboard-card {
      background: #111;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
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
  <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">Lio Loto</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Games</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Leaderboard</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Profile</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Carousel -->
  <div id="gameCarousel" class="carousel slide mt-4 container" data-bs-ride="carousel">
    <div class="carousel-inner rounded-4 shadow-lg">
      <div class="carousel-item active">
        <img src="https://via.placeholder.com/1200x400/000000/e63946?text=Blackjack" class="d-block w-100" alt="Blackjack">
      </div>
      <div class="carousel-item">
        <img src="https://via.placeholder.com/1200x400/000000/e63946?text=Mines" class="d-block w-100" alt="Mines">
      </div>
      <div class="carousel-item">
        <img src="https://via.placeholder.com/1200x400/000000/e63946?text=Roulette" class="d-block w-100" alt="Roulette">
      </div>
      <div class="carousel-item">
        <img src="https://via.placeholder.com/1200x400/000000/e63946?text=Coinflip" class="d-block w-100" alt="Coinflip">
      </div>
      <div class="carousel-item">
        <img src="https://via.placeholder.com/1200x400/000000/e63946?text=Plinko" class="d-block w-100" alt="Plinko">
      </div>
    </div>
    <button class="carousel-control-prev" type="button"data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>

  <!-- Profile + Leaderboard -->
  <div class="container mt-5">
    <div class="row">
      <!-- Player Profile -->
      <div class="col-lg-4 mb-4">
        <div class="leaderboard-card text-center">
          <img src="https://via.placeholder.com/100/ffffff/000000?text=User" class="rounded-circle mb-3" alt="Profile">
          <h4 class="fw-bold">Player Name</h4>
          <p>Balance: <span class="fw-bold text-danger">$250.00</span></p>
          <button class="btn btn-danger w-100">Deposit</button>
        </div>
      </div>

      <!-- Leaderboard -->
      <div class="col-lg-8">
        <div class="leaderboard-card">
          <h4 class="fw-bold mb-3">Top 10 Leaderboard</h4>
          <ol class="list-group list-group-numbered">
            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
              Player1 <span class="badge bg-success">$1500</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
              Player2 <span class="badge bg-success">$1400</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
              Player3 <span class="badge bg-success">$1300</span>
            </li>
            <!-- More players -->
          </ol>
        </div>
      </div>
    </div>
  </div>

</body>
</html>

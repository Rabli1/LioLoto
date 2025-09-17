  <div id="gameCarousel" class="carousel slide mt-4 container" data-bs-ride="carousel">
    <div class="carousel-inner rounded-4 shadow-lg">
      <div class="carousel-item active">
        <img src="{{ asset('img/gaben.jpg') }}" class="d-block w-100" alt="Blackjack">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('img/silver-medal.png') }}" class="d-block w-100" alt="Mines">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('img/gaben.jpg') }}" class="d-block w-100" alt="Roulette">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('img/gaben.jpg') }}" class="d-block w-100" alt="Coinflip">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('img/gaben.jpg') }}" class="d-block w-100" alt="Plinko">
      </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#gameCarousel" data-bs-slide="prev" aria-label="Previous">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#gameCarousel" data-bs-slide="next" aria-label="Next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
  </div>
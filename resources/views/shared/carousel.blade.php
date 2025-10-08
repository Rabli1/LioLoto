  <div id="gameCarousel" class="carousel slide mt-4 container" data-bs-ride="carousel">
    <div class="carousel-inner rounded-4 shadow-lg">
      <div class="carousel-item active">
        <a href="/game/blackjack">
            <img src="{{ asset('img/blackjack.jpg') }}" class="d-block w-100" alt="Blackjack">
        </a>
      </div>
      <div class="carousel-item">
        <a href="/game/plinko">
            <img src="{{ asset('img/plinko.webp') }}" class="d-block w-100" alt="Plinko">
        </a>
      </div>
      <div class="carousel-item">
        <a href="/game/mines">
            <img src="{{ asset('img/mines.webp') }}" class="d-block w-100" alt="Mines">
        </a>
      </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#gameCarousel" data-bs-slide="prev" aria-label="Previous">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#gameCarousel" data-bs-slide="next" aria-label="Next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
  </div>

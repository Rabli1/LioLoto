@include('shared.header')
@include('shared.navbar')

@viteReactRefresh
@vite(['resources/js/app.jsx'])

<style>
    .about-main { color: #e9ecef; }
    .member-img { max-width: 260px; border-radius: .5rem; object-fit: cover; transition: transform .6s ease; }
    .member-name { color: #fff; font-weight: 600; }
    .member-role { color: #cbd5e1; }
    .member-info { transition: transform .6s ease; color: #e9ecef; }

    @media(min-width: 768px) {
        .img-side { padding-left: 3rem; padding-right: 3rem; }
        .row-member { align-items: center; }
    }
</style>

<main class="container-fluid about-main my-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="h3 text-light">À propos de l'équipe</h1>
        </div>

        <div class="row row-member left mb-4">
            <div class="col-md-4 img-side d-flex justify-content-start">
                <img src="{{ asset('img/chicken.png') }}" alt="Gabriel Massé" class="img-fluid member-img">
            </div>
            <div class="col-md-8">
                <div class="member-info member-card">
                    <div class="member-name mb-1">Gabriel Massé</div>
                    <div class="member-role mb-2">Classement, Crash, Poker</div>
                </div>
            </div>
        </div>

        <div class="row row-member right mb-4">
            <div class="col-md-8 order-2 order-md-1">
                <div class="member-info member-card text-end">
                    <div class="member-name mb-1">Thomas Lahaie</div>
                    <div class="member-role mb-2">Blackjack, Roulette</div>
                </div>
            </div>
            <div class="col-md-4 img-side d-flex justify-content-end order-1 order-md-2">
                <img src="{{ asset('img/chicken.png') }}" alt="Thomas Lahaie" class="img-fluid member-img">
            </div>
        </div>

        <div class="row row-member left mb-4">
            <div class="col-md-4 img-side d-flex justify-content-start">
                <img src="{{ asset('img/chicken.png') }}" alt="Justin Lessard" class="img-fluid member-img">
            </div>
            <div class="col-md-8">
                <div class="member-info member-card">
                    <div class="member-name mb-1">Justin Lessard</div>
                    <div class="member-role mb-2">Page d'accueil, Plinko, Liotodle</div>
                </div>
            </div>
        </div>

        <div class="row row-member right mb-4">
            <div class="col-md-8 order-2 order-md-1">
                <div class="member-info member-card text-end">
                    <div class="member-name mb-1">Émil Tittley</div>
                    <div class="member-role mb-2">Mines, Coinflip, Chicken Road, Slot Machine</div>
                </div>
            </div>
            <div class="col-md-4 img-side d-flex justify-content-end order-1 order-md-2">
                <img src="{{ asset('img/chicken.png') }}" alt="Émil Tittley" class="img-fluid member-img">
            </div>
        </div>
    </div>
</main>

@include('shared.footer')
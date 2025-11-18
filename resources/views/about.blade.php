@include('shared.header')
@include('shared.navbar')

@viteReactRefresh
@vite(['resources/js/app.jsx'])

<style>
    .member-img {
        border-radius: .5rem;
    }

    @media(min-width: 768px) {
        .img-side {
            padding-left: 3rem;
            padding-right: 3rem;
        }

        .row-member {
            align-items: center;
        }
    }
</style>

<main class="container-fluid text-light my-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="h3 text-light">À propos de l'équipe</h1>
        </div>

        <div class="row row-member left mb-4">
            <div class="col-md-4 img-side d-flex justify-content-start">
                <img src="{{ asset('img/members/gab.png') }}" alt="Gabriel Massé" class="img-fluid member-img">
            </div>
            <div class="col-md-8">
                <div class="member-info member-card">
                    <div class="text-light fw-bold mb-1">Gabriel Massé</div>
                    <div class="text-light mb-2">Classement, Crash, Poker</div>
                </div>
            </div>
        </div>

        <div class="row row-member right mb-4">
            <div class="col-md-8 order-2 order-md-1">
                <div class="member-info member-card text-end">
                    <div class="text-light fw-bold mb-1">Thomas Lahaie</div>
                    <div class="text-light mb-2">Blackjack, Roulette</div>
                </div>
            </div>
            <div class="col-md-4 img-side d-flex justify-content-end order-1 order-md-2">
                <img src="{{ asset('img/members/thomas.png') }}" alt="Thomas Lahaie" class="img-fluid member-img">
            </div>
        </div>

        <div class="row row-member left mb-4">
            <div class="col-md-4 img-side d-flex justify-content-start">
                <img src="{{ asset('img/members/justin.jpg') }}" alt="Justin Lessard" class="img-fluid member-img">
            </div>
            <div class="col-md-8">
                <div class="member-info member-card">
                    <div class="text-light fw-bold mb-1">Justin Lessard</div>
                    <div class="text-light mb-2">Page d'accueil, Plinko, Liotodle</div>
                </div>
            </div>
        </div>

        <div class="row row-member right mb-4">
            <div class="col-md-8 order-2 order-md-1">
                <div class="member-info member-card text-end">
                    <div class="text-light fw-bold mb-1">Émil Tittley</div>
                    <div class="text-light mb-2">Mines, Coinflip, Chicken Road, Slot Machine</div>
                </div>
            </div>
            <div class="col-md-4 img-side d-flex justify-content-end order-1 order-md-2">
                <img src="{{ asset('img/members/emil.jpg') }}" alt="Émil Tittley" class="img-fluid member-img">
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center">
        <div class="ratio ratio-16x9" style="max-width: 900px; width: 100%;">
            <video controls class="w-100 h-100">
                <source src="{{ asset('video/lotolio.mp4') }}" type="video/mp4">
                Votre navigateur ne supporte pas la vidéo.
            </video>
        </div>
    </div>
</main>

@include('shared.footer')
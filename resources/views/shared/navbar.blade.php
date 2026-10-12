@php($connectedUser = session('user'))
<header class="site-header">
    <nav class="site-nav page-width" aria-label="Navigation principale">
        <a class="brand" href="{{ url('/') }}" aria-label="LioLoto, accueil"><span class="brand-mark" aria-hidden="true">L<span>✦</span></span>Lio<span>Loto</span><span class="brand-dot">.</span></a>
        <div class="nav-links">
            <a href="{{ url('/game') }}" @class(['is-current' => request()->is('game*')])>Jeux</a>
            <a href="{{ url('/leaderboard') }}" @class(['is-current' => request()->is('leaderboard')])>Classement</a>
            <a href="{{ url('/user/support') }}" @class(['is-current' => request()->is('user/support')])>Comment jouer</a>
        </div>
        <div class="nav-account">
            @if($connectedUser)
                <span class="wallet"><span aria-hidden="true">◈</span> <strong data-balance>{{ number_format($connectedUser->points ?? 0, 0, ',', ' ') }}</strong><small>pts</small></span>
                <details class="account-menu"><summary><span class="avatar-mark">{{ mb_substr($connectedUser->name, 0, 1) }}</span><span class="account-name">{{ $connectedUser->name }}</span><span aria-hidden="true">⌄</span></summary>
                    <div class="account-dropdown">
                        <a href="{{ url('/user/profile') }}?id={{ $connectedUser->id }}">Mon profil</a>
                        @if($connectedUser->admin ?? false)<a href="{{ url('/admin/dashboard') }}">Administration</a>@endif
                        <form method="post" action="{{ url('/user/deconnection') }}">@csrf<button type="submit">Déconnexion</button></form>
                    </div>
                </details>
            @else
                <a class="nav-login" href="{{ url('/user/connection') }}">Connexion</a>
                <a class="button button-small" href="{{ url('/user/signIn') }}">S’inscrire <span aria-hidden="true">↗</span></a>
            @endif
        </div>
    </nav>
</header>

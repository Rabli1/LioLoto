@php
$userConnected = session()->has("user");
$currentPage = request()->path();
$admin = false;
if($userConnected){
  $admin = session('user')->admin;
  if(session('user')->banned){
    session()->forget('user');
    return redirect('user/connection?message=Votre compte a été banni');
  }
}
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">Lotoli</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
      aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link @if($currentPage == 'game') active @endif" href="/game">Jeux</a>
        </li>
        <li class="nav-item">
          <a class="nav-link @if($currentPage == 'leaderboard') active @endif" href="/leaderboard">Classement</a>
        </li>

        @if($userConnected)
          <li class="nav-item">
            <a class="nav-link @if($currentPage == 'user/profile') active @endif"
               href="/user/profile?id={{ session()->get('user')->id }}">Profil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link @if($currentPage == 'user/deconnection') active @endif"
               href="/user/deconnection">Déconnexion</a>
          </li>
        @else
          <li class="nav-item">
            <a class="nav-link @if($currentPage == 'user/connection') active @endif"
               href="/user/connection?message=">Connexion</a>
          </li>
          <li class="nav-item">
            <a class="nav-link @if($currentPage == 'user/signIn') active @endif" href="/user/signIn">Inscription</a>
          </li>
        @endif
        @if($admin)
          <li class="nav-item">
            <a class="nav-link @if($currentPage == 'admin/dashboard') active @endif" href="/admin/dashboard">Admin</a>
          </li>
        @endif
        <li class="nav-item">
          <a class="nav-link @if($currentPage == 'support') active @endif" href="/user/support">Support</a>
        </li>

        <li class="nav-item d-flex align-items-center ms-2 position-relative">
          <button id="searchToggle" class="btn btn-link p-0 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
              <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 
                       16 11.11 16 9.5 16 5.91 13.09 3 
                       9.5 3S3 5.91 3 9.5 5.91 16 
                       9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 
                       4.99L20.49 19l-4.99-5zm-6 
                       0C7.01 14 5 11.99 5 9.5S7.01 5 
                       9.5 5 14 7.01 14 9.5 11.99 14 
                       9.5 14z"/>
            </svg>
          </button>

          <div id="searchBox"
               class="d-none position-absolute end-0 top-100 mt-2 bg-white rounded shadow p-2"
               style="width: 250px; z-index: 1000;">
            <input a id="searchInput" type="text" class="form-control border-0 shadow-none mb-1" placeholder="Rechercher utilisateur">
            <div id="searchResults" class="w-100" style="max-height: 250px; overflow-y: auto;"></div>
            <button id="searchClose" type="button" class="btn btn-link p-0 position-absolute" style="top: 10px; right: 5px;">
              <svg width="16" height="16" viewBox="0 0 14 14" fill="black" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z"/>
              </svg>
            </button>
          </div>
        </li>

      </ul>
    </div>
  </div>
</nav>

<script>

const toggle = document.getElementById("searchToggle");
const searchBox = document.getElementById("searchBox");
const closeBtn = document.getElementById("searchClose");
const searchInput = document.getElementById("searchInput");
const searchResults = document.getElementById("searchResults");


const users = @json($users);

toggle.addEventListener("click", () => {
    searchBox.classList.toggle("d-none");
    searchInput.focus();
});

closeBtn.addEventListener("click", () => {
    searchBox.classList.add("d-none");
    searchInput.value = '';
    searchResults.innerHTML = '';
});

function updateSearchResults() {
    const query = searchInput.value.toLowerCase();
    searchResults.innerHTML = '';

    if(query === '') return;

    const filtered = users.filter(u => u.name.toLowerCase().startsWith(query)).slice(0, 5);

    filtered.forEach(user => {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center p-1 border-bottom';
        div.innerHTML = `
            <i class="fa-solid ${user.profileImage} pfp-${user.profileColor} me-2"></i>
            <a href="/user/profile?id=${user.id}" class="text-dark text-decoration-none">${user.name}</a>
        `;
        searchResults.appendChild(div);
    });
}

searchInput.addEventListener('input', updateSearchResults);
</script>

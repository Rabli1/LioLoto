@php
  $user = session('user') ?? null;
  $userConnected = !is_null($user);
  $currentPage = request()->path();
  $admin = $user->admin ?? false;
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container d-flex align-items-center justify-content-start">
    <!-- Logo / marque -->
    <a class="navbar-brand fw-bold" href="/">Lotoli</a>

    <div id="mainNav" style="flex-grow: 1;"> 
      <div id="react-navbar"
           class="navbar-nav"
           style="margin-left: 62%; margin-bottom: 4%; width: 50%;"
           data-user='@json(session("user"))' 
           data-current-page="{{ $currentPage }}" >
      </div> 
    </div>
  </div> 
</nav>
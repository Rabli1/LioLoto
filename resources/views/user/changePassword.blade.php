@include('shared.header')
@include('shared.navbar')

@php
    $connectedUserId = session()->has('user') ? session('user')->id : null;
@endphp

@if($connectedUserId)
    <div class="form-container mt-5">
        <h3 class="text-center mb-4">Changer votre mot de passe</h3>

        @if(session('success'))
            <div class="text-center text-success mb-3 fw-bold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="text-center text-danger mb-3">
                <ul class="list-unstyled mb-0">
                    @foreach($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.updatePassword') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="current_password" class="form-label">Mot de passe actuel</label>
                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Mot de passe actuel" required>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Nouveau mot de passe" required>
            </div>

            <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" placeholder="Confirmez le mot de passe" required>
            </div>

            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-danger mt-4 w-50">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
@else
    <script>
        window.location.href = "/user/connection?message=Vous devez être connecté pour accéder à cette page.";
    </script>
@endif

@include('shared.footer')

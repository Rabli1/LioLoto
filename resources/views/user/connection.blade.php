@include('shared.header')
@include('shared.navbar')
<div class="form-container mt-5">
    <h3 class="text-center mb-4">Connexion</h3>
    <span class="text-center text-danger"><?=$message?></span>
    <form action="{{ url('/connection') }}" method="post">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Nom d'utilisateur">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe">
        </div>

        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-danger mt-4 w-50" id="submit" disabled>
                S'inscrire
            </button>
        </div>
    </form>
</div>
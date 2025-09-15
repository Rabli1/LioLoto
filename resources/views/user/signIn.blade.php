@include('shared.header')
@include('shared.navbar')
<div class="form-container mt-5">
    <h3 class="text-center mb-4">Inscryption</h3>
    <form>
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" placeholder="Nom d'utilisateur">
            <span class="text-danger"></span>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" placeholder="Email">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" placeholder="Mot de passe">
        </div>
        <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
            <input type="password" class="form-control" id="confirmPassword" placeholder="Confirmer le mot de passe">
        </div>
        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-danger mt-4 w-50" id="submit">S'inscrire</button>
        </div>
    </form>
</div>
<script>
    $(document).ready(function () {
        let validInput = [false, false, false, false]
        let errors = $("span");
        $("#username").on("change", function () {
            validInput[0] = false
            var username = $("#username").val();
            try {
                let response = await fetch('users.json');
                let users = await response.json();
                let userExists = users.find(u => u.username === username);
                if (username.length < 4) {
                    errors.eq(0).text("nom d'utilisateur trop court. Au moins 4 caractères")
                }
                else {
                    if (userExists) {
                        errors.eq(0).text("Nom d'utilisateur déjà utilisé");
                    } else {
                        errors.eq(0).text("");
                        validInput[0] = true
                    }
                }
            } catch (error) {
                errors.eq(0).text("Erreur lors de la vérification du nom d'utilisateur");
            }
            if(validInput = [true, true, true, true]){
                $('#submit').prop('disabled', false);
            }
            else{
                $('#submit').prop('disabled', true);
            }
        });
    });
</script>
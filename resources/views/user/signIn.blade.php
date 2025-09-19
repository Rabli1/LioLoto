@include('shared.header')
@include('shared.navbar')
<div class="form-container mt-5">
    <h3 class="text-center mb-4">Inscription</h3>
    <form action="{{ url('/user/addUser') }}" method="post">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Nom d'utilisateur">
            <span class="text-danger"></span>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Email">
            <span class="text-danger"></span>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe">
            <span class="text-danger"></span>
        </div>

        <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                placeholder="Confirmer le mot de passe">
            <span class="text-danger"></span>
        </div>

        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-danger mt-4 w-50" id="submit" disabled>
                S'inscrire
            </button>
        </div>
    </form>
</div>
<script>
    $(document).ready(function () {
        let validInput = [false, false, false, false]
        let errors = $("form span");
        $("#username").on("change", async function () {
            validInput[0] = false
            var username = $("#username").val();
            try {
                if (username.length < 4) {
                    errors.eq(0).text("nom d'utilisateur trop court. Au moins 4 caractères")
                }
                else {
                    const response = await fetch(`/check-username?username=${encodeURIComponent(username)}`);
                    const data = await response.json();
                    const userExists = data.exists;
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
            if (validInput.every(v => v === true)) {
                $('#submit').prop('disabled', false);
            }
            else {
                $('#submit').prop('disabled', true);
            }
        });
        $("#email").on("change", async function () {
            validInput[1] = false
            var email = $("#email").val();
            try {
                var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!re.test(email)) {
                    errors.eq(1).text("email non valide")
                }
                else {
                    const response = await fetch(`/check-email?email=${encodeURIComponent(email)}`);
                    const data = await response.json();
                    const emailExists = data.exists;
                    if (emailExists) {
                        errors.eq(1).text("Adresse courriel déjà utilisé");
                    } else {
                        errors.eq(1).text("");
                        validInput[1] = true
                    }
                }
            } catch (error) {
                errors.eq(1).text("Erreur lors de la vérification du courriel");
            }
            if (validInput.every(v => v === true)) {
                $('#submit').prop('disabled', false);
            }
            else {
                $('#submit').prop('disabled', true);
            }
        });
        $("#password").on("change", function () {
            validInput[2] = false
            var password = $("#password").val();
            if (password.length < 8) {
                errors.eq(2).html("le mot de passe doit contenir au moins 8 caractères")
            }
            else {
                var uppercase = /[A-Z]/;
                var lowercase = /[a-z]/;
                var number = /\d/;
                var special = /[-+_!@#$%^&*.,?]/;
                var message = "";
                if (!uppercase.test(password)) {
                    message += "-une lettre minuscule<br>"
                }
                if (!lowercase.test(password)) {
                    message += "-une lettre majuscule<br>"
                }
                if (!number.test(password)) {
                    message += "-un chiffre<br>"
                }
                if (!special.test(password)) {
                    message += "-un caractère spécial"
                }
                if (message == "") {
                    errors.eq(2).html("")
                    validInput[2] = true
                }
                else {
                    message = "votre mot de passe doit contenir : <br>" + message
                    errors.eq(2).html(message)
                }
            }
            if (validInput.every(v => v === true)) {
                $('#submit').prop('disabled', false);
            }
            else {
                $('#submit').prop('disabled', true);
            }
        });
        $("#confirmPassword").on("change", function () {
            validInput[3] = false
            var confirmPassword = $("#confirmPassword").val();
            var password = $("#password").val();
            if (password == confirmPassword) {
                errors.eq(3).text("");
                validInput[3] = true
            }
            else {
                errors.eq(3).text("Les mots de passes ne concordent pas");
            }
            if (validInput.every(v => v === true)) {
                $('#submit').prop('disabled', false);
            }
            else {
                $('#submit').prop('disabled', true);
            }
        });
    });
</script>
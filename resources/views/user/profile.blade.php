@include('shared.header')
@include('shared.navbar')

@php
    $textColorChanges = $user['profileColor'] == "white" || $user['profileColor'] == "yellow" ? "force-dark" : "text-white";
    $connectedUserId = -1;
    if (session()->has('user')) {
        $connectedUserId = session('user')->id;
    }
@endphp

<h1 style="text-align: center; margin-top: 20px;">Carte du joueur</h1>

<body style="min-height: 100vh;">
    <div class="container py-5">
        <div class="mx-auto" style="max-width: 400px;">
            @php
                $textColors = [
                    'yellow' => 'text-dark',
                    'white' => 'text-dark',
                    'pink' => 'text-dark',
                ];
                $textColor = $textColors[$user['profileColor']] ?? 'text-white';
            @endphp

            <div class="bg-{{ $user['profileColor'] }} {{ $textColor }} p-3 rounded text-center">
                <div>
                    <i class="fa-solid {{ $user['profileImage'] }} pfp-{{ $user['profileColor'] }} fa-5x mb-3 pe-2"></i>
                    @if($connectedUserId == $user['id'])
                        <i id="edit" class="fa-solid fa-pen position-absolute mt-5" style="cursor: pointer;"></i>
                    @endif
                </div>
                <h2 class="fw-bold">{{ $user['name'] }}</h2>
                <div>
                    <span class="{{ $textColor }} me-3">niveau {{ $user['lvl'] }}</span>
                    <div class="progress">
                        <div class="progress-bar bg-danger" role="progressbar"
                            style="width: {{ ($user['exp'] / ($user['lvl'] * 1000)) * 100 }}%;"
                            aria-valuenow="{{ $user['exp'] }}" aria-valuemin="0"
                            aria-valuemax="{{ $user['lvl'] * 1000 }}">
                        </div>
                    </div>
                </div>

                <p>{{ $user['bio'] }}</p>
                <hr class="border-light">
                <ul class="list-group list-group-flush text-start">
                    <li class="list-group-item bg-transparent {{ $textColor }}">
                        <strong>Points :</strong> {{ $user['points'] }}
                    </li>
                    <li class="list-group-item bg-transparent {{ $textColor }}">
                        <img src="{{ asset('img/gold-medal.png') }}" alt="Or"
                            style="width:24px;vertical-align:middle;" />
                        <strong>Or :</strong> {{ $user['gold'] }}
                    </li>
                    <li class="list-group-item bg-transparent {{ $textColor }}">
                        <img src="{{ asset('img/silver-medal.png') }}" alt="Argent"
                            style="width:24px;vertical-align:middle;" />
                        <strong>Argent :</strong> {{ $user['silver'] }}
                    </li>
                    <li class="list-group-item bg-transparent {{ $textColor }}">
                        <img src="{{ asset('img/bronze-medal.png') }}" alt="Bronze"
                            style="width:24px;vertical-align:middle;" />
                        <strong>Bronze :</strong> {{ $user['bronze'] }}
                    </li>
                    <li class="list-group-item bg-transparent {{ $textColor }}">
                        <strong>Points perdus :</strong> {{ $user['pointsLost'] }}
                    </li>
                </ul>
            </div>
        </div>

        <div id="editForm" class="mx-auto mt-4 p-4 rounded shadow bg-dark text-dark hidden" style="max-width: 1000px;">
            <form method="post" action="/user/updateAvatar">
                @csrf
                <input type="hidden" name="userLvl" value="{{ $user['lvl'] }}" id="userLvl">
                <div id="imageChoices" class="d-flex flex-wrap gap-3 justify-content-center">
                    @php
                        $images = [
                            'fa-user' => 0,
                            'fa-user-injured' => 0,
                            'fa-user-secret' => 2,
                            'fa-user-tie' => 3,
                            'fa-user-graduate' => 4,
                            'fa-user-astronaut' => 5,
                            'fa-user-ninja' => 7,
                            'fa-skull' => 10,
                            'fa-fish' => 15,
                            'fa-frog' => 20,
                            'fa-hippo' => 25,
                            'fa-poo' => 50,
                        ];
                    @endphp

                    @foreach($images as $icon => $requiredLvl)
                        <div class="position-relative d-inline-block m-1">
                            <input type="radio" class="btn-check" name="image" id="option-{{ $icon }}" autocomplete="off"
                                value="{{ $icon }}" @if($user['lvl'] < $requiredLvl) disabled @endif>
                            <label class="btn btn-secondary fs-2 @if($user['lvl'] < $requiredLvl) disabled @endif"
                                for="option-{{ $icon }}">
                                <i class="fa-solid {{ $icon }}"></i>
                            </label>

                            @if($user['lvl'] < $requiredLvl)
                                <div class="position-absolute bg-dark text-light p-2 rounded lock-icon">
                                    <i class="fa-solid fa-lock" title="Niveau {{ $requiredLvl }} requis"></i>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="d-flex flex-wrap gap-3 justify-content-center mt-3">
                    @php
                        $colors = [
                            'black' => 0,
                            'red' => 0,
                            'blue' => 0,
                            'green' => 0,
                            'yellow' => 0,
                            'orange' => 5,
                            'pink' => 6,
                            'brown' => 8,
                            'gray' => 10,
                            'purple' => 15,
                            'white' => 25,
                        ];
                    @endphp

                    @foreach($colors as $color => $requiredLvl)
                        <div class="position-relative d-inline-block m-1">
                            <input type="radio" class="btn-check" name="color" id="color-{{ $color }}" value="{{ $color }}"
                                autocomplete="off" @if($user['lvl'] < $requiredLvl) disabled @endif>
                            <label class="btn btn-secondary fs-2 @if($user['lvl'] < $requiredLvl) disabled @endif"
                                for="color-{{ $color }}">
                                <i class="fas fa-circle pfp-{{ $color }}"></i>
                            </label>

                            @if($user['lvl'] < $requiredLvl)
                                <div class="position-absolute text-light bg-dark p-2 rounded lock-icon">
                                    <i class="fa-solid fa-lock" title="Niveau {{ $requiredLvl }} requis"></i>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center">
                    <div id="avatarPreview" class="fs-1 p-2 rounded">
                        <i class="fas fa-user text-black"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-danger mt-3">Modifier votre image de profil</button>
                </div>
            </form>

            <h3 class="text-center text-light mb-3 mt-5">Modifier bio</h3>
            <form method="post" action="/user/updateBio">
                @csrf
                <div class="mb-3" style="width: 700px; margin-left: auto; margin-right: auto;">
                    <textarea maxlength="99" id="bio" name="bio" class="form-control" rows="4"
                        placeholder="bio">{{ $user['bio'] }}</textarea>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-danger">Modifier votre bio</button>
                </div>
            </form>
            <h3 class="text-center text-light mb-3 mt-5">Modifier mot de passe</h3>
            <div class="d-flex justify-content-center">
                <a href="{{ url('/user/changePassword') }}" class="btn btn-warning text-dark">
                    Modifier votre mot de passe
                </a>
            </div>
        </div>
    </div>

    <script>
        const blockedImage = {
            "fa-user": 0,
            "fa-user-injured": 0,
            "fa-user-secret": 2,
            "fa-user-tie": 3,
            "fa-user-graduate": 4,
            "fa-user-astronaut": 5,
            "fa-user-ninja": 7,
            "fa-skull": 10,
            "fa-fish": 15,
            "fa-frog": 20,
            "fa-hippo": 25,
            "fa-poo": 50
        };

        const blockedColor = {
            "black": 0,
            "red": 0,
            "blue": 0,
            "green": 0,
            "yellow": 0,
            "orange": 5,
            "pink": 6,
            "brown": 8,
            "gray": 10,
            "purple": 15,
            "white": 25
        };

        let avatarPreview = $("#avatarPreview i");
        $("#edit").on("click", function () {
            $("#editForm").toggleClass("hidden");
        });

        $(document).on("click", "input[name='image']", function (e) {
            const chosenImage = $(this).val();
            if (blockedImage[chosenImage] > $("#userLvl").val()) {
                e.preventDefault();
                $(this).prop('checked', false);
            }
            else {
                avatarPreview.removeClass().addClass(`fa-solid ${chosenImage} pfp-${$("input[name='color']:checked").val() || 'black'}`);
            }
        });

        $(document).on("click", "input[name='color']", function (e) {
            const chosenColor = $(this).val();
            if (blockedColor[chosenColor] > $("#userLvl").val()) {
                e.preventDefault();
                $(this).prop('checked', false);
            }
            else {
                avatarPreview.removeClass().addClass(`fa-solid ${$("input[name='image']:checked").val() || 'fa-user'} pfp-${chosenColor}`);
            }
        });
    </script>
</body>
@include('shared.footer')

</html>
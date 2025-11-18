<!DOCTYPE html>
<html lang="en">

<head>
    @include('shared.header')

    @include('shared.navbar')

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <script src="{{ asset('js/liotodle.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/liotodle.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body>
    <div id="container">
        <div id="game">
            <header style="margin-top: 7%;">
                <div class="d-flex justify-content-center align-items-end gap-2">
                    <h1 class="mb-2">Liotodle</h1>
                    <div class="info-container">
                        <button id="infoBtn" class="btn btn-secondary p-2">
                            <i class="fa-solid fa-info"></i>
                        </button>

                        <div class="info-popup" id="infoBox" style="z-index: 999;">
                            <p class="mb-0">
                                Devinez le mot en un nombre limité d'essais. Les couleurs indiquent la justesse des
                                lettres.
                                plus d'information <a href="/user/support?game=liotodle">ici</a>
                            </p>
                        </div>
                    </div>
                </div>
                <script>
                    const btn = document.getElementById("infoBtn");
                    const box = document.getElementById("infoBox");

                    btn.addEventListener("click", () => {
                        box.style.display = (box.style.display === "block") ? "none" : "block";
                    });
                </script>

            </header>

            <div id="dailyLockMessage" class="daily-lock-message"></div>

            <div id="board-container">
                <div id="board"></div>
            </div>

            <div id="keyboard-container">
                <div class="keyboard-row">
                    <button data-key="q">q</button>
                    <button data-key="w">w</button>
                    <button data-key="e">e</button>
                    <button data-key="r">r</button>
                    <button data-key="t">t</button>
                    <button data-key="y">y</button>
                    <button data-key="u">u</button>
                    <button data-key="i">i</button>
                    <button data-key="o">o</button>
                    <button data-key="p">p</button>
                </div>
                <div class="keyboard-row">
                    <div class="spacer-half"></div>
                    <button data-key="a">a</button>
                    <button data-key="s">s</button>
                    <button data-key="d">d</button>
                    <button data-key="f">f</button>
                    <button data-key="g">g</button>
                    <button data-key="h">h</button>
                    <button data-key="j">j</button>
                    <button data-key="k">k</button>
                    <button data-key="l">l</button>
                    <div class="spacer-half"></div>
                </div>
                <div class="keyboard-row">
                    <button data-key="enter" class="wide-button">Enter</button>
                    <button data-key="z">z</button>
                    <button data-key="x">x</button>
                    <button data-key="c">c</button>
                    <button data-key="v">v</button>
                    <button data-key="b">b</button>
                    <button data-key="n">n</button>
                    <button data-key="m">m</button>
                    <button data-key="del" class="wide-button">Del</button>
                </div>
            </div>
        </div>

        <div id="resultModal" class="result-modal">
            <div id="resultPoints" class="result-points"></div>
        </div>
    </div>
</body>
<script>
    window.gameSession = {
        userId: {{ session('user')->id ?? 'null' }},
        balance: {{ (int) $playerBalance }},
        dailyAvailable: @json(optional(session('user'))->daily ?? true),
        endpoints: { saveBalance: '{{ url('game/balance') }}' },
        csrfToken: '{{ csrf_token() }}'
    };
</script>
@include('shared.footer')

</html>

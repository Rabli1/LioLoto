@include('shared.header')
@include('shared.navbar')
<style>
    body {
        background-color: #121212;
        color: #e0e0e0;
    }

    .card {
        background-color: #1e1e1e;
        border: 1px solid #2c2c2c;
    }

    .accordion-button {
        background-color: #1e1e1e;
        color: #e0e0e0;
    }

    .accordion-button:not(.collapsed) {
        background-color: #2a2a2a;
        color: #c50000ff;
    }

    .accordion-body {
        background-color: #1b1b1b;
        color: #d0d0d0;
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(255, 71, 71, 0.45);
    }

    .accordion-item {
        border: none;
    }
</style>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-light">
                    <h2 class="text-center mb-3 section-title">Support</h2>
                    <p class="text-center">Règlements Et Explications Des Jeux</p>
                </div>
            </div>

            <div class="accordion" id="gamesAccordion">

                <!--Reset-->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingReset">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseReset" aria-expanded="false" aria-controls="collapseReset">
                            Pourquoi mon solde est à 1000 maintenant?
                        </button>
                    </h2>
                    <div id="collapseReset" class="accordion-collapse collapse" aria-labelledby="headingReset"
                        data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Le but</h5>
                            <p>Offrir les mêmes chances à tout les joueurs et garantir plus de temps de jeu.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>À 00:00 heure de l'est, tout les joueurs voient leur solde se remettre à 1000.</li>
                                <li>Les joueurs faisant partie du top 3 recevront une médail d'or d'argent et de bronze et seront visible sur leur profil.</li>
                            </ul>

                            <h5>informations</h5>
                            <ul>
                                <li>Le classement quotidient est disponible en tout temps <a href="/leaderboard">ici</a></li>
                                <li>Vous pourrez alors consulter votre position pour la journée en cours</li>
                            </ul>

                        </div>
                    </div>
                </div>

                <!-- Blackjack -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingBlackjack">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseBlackjack" aria-expanded="false" aria-controls="collapseBlackjack">
                            Quelles-sont les règlements du Blackjack?
                        </button>
                    </h2>
                    <div id="collapseBlackjack" class="accordion-collapse collapse" aria-labelledby="headingBlackjack"
                        data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Obtenir une main dont la valeur est la plus proche possible de 21 sans la dépasser.
                                Battre la main du croupier pour gagner.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Les cartes de 2 à 10 valent leur valeur faciale. Valet, Dame et Roi valent 10. L’As
                                    vaut 1 ou 11 (automatiquement optimisé pour éviter le bust).</li>
                                <li>Chaque joueur reçoit deux cartes. Le croupier reçoit une carte face visible et une
                                    face cachée.</li>
                                <li>Actions disponibles : <strong>Hit</strong> (tirer une carte), <strong>Stand</strong>
                                    (rester), <strong>Double</strong> (doubler la mise et recevoir une seule carte),
                                    <strong>Split</strong> (séparer une paire en deux mains) quand applicable.
                                </li>
                                <li>Le croupier doit tirer jusqu’à atteindre 17.</li>
                            </ul>

                            <h5>Paiements</h5>
                            <ul>
                                <li>Victoire simple : 1:1 (pari doublé)</li>
                                <li>Blackjack (21 sur les deux premières cartes): 3:2</li>
                            </ul>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Doublez lorsque vous avez 10 ou 11 et que le croupier montre une carte plus
                                        faible (ex. 5 ou 6).</li>
                                    <li>Splitez toujours les As et les 8 ; n’optez pas pour le split des 10.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Plinko -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingPlinko">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapsePlinko" aria-expanded="false" aria-controls="collapsePlinko">
                            Quelles-sont les règlements du Plinko?
                        </button>
                    </h2>
                    <div id="collapsePlinko" class="accordion-collapse collapse" aria-labelledby="headingPlinko"
                        data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Faire tomber une bille sur un plateau avec obstacles pour atteindre une case finale
                                associée à un multiplicateur de gain.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Le joueur place une mise puis lance la bille depuis sa position initiale.</li>
                                <li>La bille rebondit aléatoirement entre des chevilles et finit dans une des cases du
                                    bas, chacune avec un multiplicateur.</li>
                                <li>Le gain = mise × multiplicateur de la case d’arrivée.</li>
                            </ul>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Plinko est principalement un jeu de hasard.</li>
                                    <li>Gérez votre bankroll : privilégiez des mises petites si vous cherchez à jouer
                                        longtemps.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Crash -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingCrash">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseCrash" aria-expanded="false" aria-controls="collapseCrash">
                            Quelles-sont les règlements de Crash?
                        </button>
                    </h2>
                    <div id="collapseCrash" class="accordion-collapse collapse" aria-labelledby="headingCrash"
                        data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Parier et retirer vos gains avant que le multiplicateur s’effondre. Plus vous attendez,
                                plus le multiplicateur augmente, mais le risque augmente aussi.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Les joueurs parient avant le début du round.</li>
                                <li>Le multiplicateur augmente en continu (ex. 1.00 → 1.02 → 1.5 → 2.0 → ...).</li>
                                <li>Le joueur doit cliquer sur "retirer" avant que le round ne crash. Si le round crash
                                    avant votre cashout, vous perdez la mise.</li>
                            </ul>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Stratégie prudente : encaisser tôt (petits multiplicateurs) pour sécuriser des
                                        gains fréquents mais modestes.</li>
                                    <li>Stratégie agressive : viser des multiplicateurs élevés mais accepter un taux
                                        d’échec élevé. Ceci est essentiellement du hasard avec gestion du risque.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roulette -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingRoulette">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseRoulette" aria-expanded="false" aria-controls="collapseRoulette">
                            Quelles-sont les règlements de la roulette?
                        </button>
                    </h2>
                    <div id="collapseRoulette" class="accordion-collapse collapse" aria-labelledby="headingRoulette"
                        data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Prédire où la bille s’arrêtera sur la roue et la table de roulette. Différents types de
                                paris offrent des paiements différents.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Placez vos paris sur la table (numéro simple, paire/impair, couleur, colonnes,
                                    douzaines, etc.).</li>
                                <li>Une balle est lancé sur la roue.</li>
                                <li>La bille s’arrête sur un numéro ; les paris gagnants sont payés selon les cotes.
                                </li>
                            </ul>

                            <h5>Paiements courants</h5>
                            <ul>
                                <li>Numéro : 35:1</li>
                                <li>Tiers (3 numéros) : 11:1 ou 8:1 selon le type</li>
                                <!-- sup babygirl met le bon chiffre -->
                                <li>Colonnes / Douzaines : 2:1</li>
                                <li>Pair/Impair, Rouge/Noir : 1:1</li>
                            </ul>

                            <h5>Exemple</h5>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Les systèmes de pari (Martingale, Fibonacci, etc.) ne changent pas l’avantage à
                                        long terme ; ils modulent la variance.</li>
                                    <li>Privilégiez les paris extérieurs (pair/impair, rouge/noir) pour des gains plus
                                        réguliers et moins volatils.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Mines -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingMines">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseMines" aria-expanded="false" aria-controls="collapseMines">
                            Quelles-sont les règlements de Mines?
                        </button>
                    </h2>
                    <div id="collapseMines" class="accordion-collapse collapse" aria-labelledby="headingMines"
                        data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Cliquer sur des cases sûres d’une grille sans découvrir de mines. Plus vous découvrez de
                                cases sûres, plus le multiplicateur augmente.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Avant le round, le joueur choisit la mise et le nombre de mines.</li>
                                <li>La grille contient plusieurs cases, dont un certain nombre de mines placées
                                    aléatoirement.</li>
                                <li>Chaque clic sur une case sûre augmente le multiplicateur potentiel. Si vous cliquez
                                    sur une mine, vous perdez la mise.</li>
                            </ul>

                            <h5>Exemple</h5>
                            <div class="example">
                                Grille 5×5, 5 mines. Vous choisissez de découvrir 4 cases sûres : la progression des
                                multiplicateurs peut être (selon le site) x1.1, x1.6, x2.8, x5.0. Si vous stoppez après
                                3 cases gagnantes, vous encaissez le multiplicateur correspondant à ce palier.
                            </div>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Réduire le nombre de mines proposée diminue le risque et augmente la probabilité
                                        de découvrir des cases sûres.</li>
                                    <li>Stratégie conservatrice : prendre des gains après quelques découvertes pour
                                        sécuriser un profit.</li>
                                    <li>Stratégie audacieuse : viser de nombreuses découvertes pour des multiplicateurs
                                        élevés, mais accepter un risque important.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@include('shared.footer')
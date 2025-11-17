@include('shared.header')

@include('shared.navbar')

@viteReactRefresh
@vite(['resources/js/app.jsx'])
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
                        <button class="accordion-button {{ $game === 'reset' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseReset" aria-expanded="false"
                            aria-controls="collapseReset">
                            Pourquoi mon solde est à 1000 maintenant?
                        </button>
                    </h2>
                    <div id="collapseReset" class="accordion-collapse {{ $game === 'reset' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingReset" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Le but</h5>
                            <p>Offrir les mêmes chances à tout les joueurs et garantir plus de temps de jeu.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>À 00:00 heure de l'est, tout les joueurs voient leur solde se remettre à 1000.</li>
                                <li>Les joueurs faisant partie du top 3 recevront une médail d'or d'argent et de bronze
                                    et seront visible sur leur profil.</li>
                            </ul>

                            <h5>informations</h5>
                            <ul>
                                <li>Le classement quotidient est disponible en tout temps <a href="/leaderboard">ici</a>
                                </li>
                                <li>Vous pourrez alors consulter votre position pour la journée en cours</li>
                            </ul>

                        </div>
                    </div>
                </div>

                <!-- Blackjack -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingBlackjack">
                        <button class="accordion-button {{ $game === 'blackjack' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseBlackjack" aria-expanded="false"
                            aria-controls="collapseBlackjack">
                            Quelles-sont les règlements du Blackjack?
                        </button>
                    </h2>
                    <div id="collapseBlackjack"
                        class="accordion-collapse {{ $game === 'blackjack' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingBlackjack" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Obtenir une main dont la valeur est la plus proche possible de 21 sans la dépasser.
                                Battre la main du croupier pour gagner.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Les cartes de 2 à 10 valent leur valeur faciale. Valet, Dame et Roi valent 10. L'As
                                    vaut 1 ou 11 (automatiquement optimisé pour éviter le bust).</li>
                                <li>Chaque joueur reçoit deux cartes. Le croupier reçoit une carte face visible et une
                                    face cachée.</li>
                                <li>Actions disponibles : <strong>Hit</strong> (tirer une carte), <strong>Stand</strong>
                                    (rester), <strong>Double</strong> (doubler la mise et recevoir une seule carte),
                                    <strong>Split</strong> (séparer une paire en deux mains) quand applicable.
                                </li>
                                <li>Le croupier doit tirer jusqu'à atteindre 17.</li>
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
                                    <li>Splitez toujours les As et les 8 ; n'optez pas pour le split des 10.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Plinko -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingPlinko">
                        <button class="accordion-button {{ $game === 'plinko' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapsePlinko" aria-expanded="false"
                            aria-controls="collapsePlinko">
                            Quelles-sont les règlements du Plinko?
                        </button>
                    </h2>
                    <div id="collapsePlinko" class="accordion-collapse {{ $game === 'plinko' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingPlinko" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Faire tomber une bille sur un plateau avec obstacles pour atteindre une case finale
                                associée à un multiplicateur de gain.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Le joueur place une mise puis lance la bille depuis sa position initiale.</li>
                                <li>La bille rebondit aléatoirement entre des chevilles et finit dans une des cases du
                                    bas, chacune avec un multiplicateur.</li>
                                <li>Le gain = mise × multiplicateur de la case d'arrivée.</li>
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
                        <button class="accordion-button {{ $game === 'crash' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseCrash" aria-expanded="false"
                            aria-controls="collapseCrash">
                            Quelles-sont les règlements de Crash?
                        </button>
                    </h2>
                    <div id="collapseCrash" class="accordion-collapse {{ $game === 'crash' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingCrash" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Parier et retirer vos gains avant que le multiplicateur s'effondre. Plus vous attendez,
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
                                        d'échec élevé. Ceci est essentiellement du hasard avec gestion du risque.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roulette -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingRoulette">
                        <button class="accordion-button {{ $game === 'roulette' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseRoulette" aria-expanded="false"
                            aria-controls="collapseRoulette">
                            Quelles-sont les règlements de la roulette?
                        </button>
                    </h2>
                    <div id="collapseRoulette"
                        class="accordion-collapse {{ $game === 'roulette' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingRoulette" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Prédire où la bille s'arrêtera sur la roue et la table de roulette. Différents types de
                                paris offrent des paiements différents.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Placez vos paris sur la table (numéro simple, paire/impair, couleur, colonnes,
                                    douzaines, etc.).</li>
                                <li>Une balle est lancé sur la roue.</li>
                                <li>La bille s'arrête sur un numéro ; les paris gagnants sont payés selon les cotes.
                                </li>
                            </ul>

                            <h5>Paiements courants</h5>
                            <ul>
                                <li>Numéro : 35:1</li>
                                <li>Tiers (3 numéros) : 11:1</li>
                                <li>Colonnes / Douzaines : 2:1</li>
                                <li>Pair/Impair, Rouge/Noir : 1:1</li>
                            </ul>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Les systèmes de pari (Martingale, Fibonacci, etc.) ne changent pas l'avantage à
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
                        <button class="accordion-button {{ $game === 'mines' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseMines" aria-expanded="false"
                            aria-controls="collapseMines">
                            Quelles-sont les règlements de Mines?
                        </button>
                    </h2>
                    <div id="collapseMines" class="accordion-collapse {{ $game === 'mines' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingMines" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">
                            <h5>Objectif</h5>
                            <p>Cliquer sur des cases sûres d'une grille sans découvrir de mines. Plus vous découvrez de
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

                <!-- Coinflip -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingCoinflip">
                        <button class="accordion-button {{ $game === 'coinflip' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseCoinflip" aria-expanded="false"
                            aria-controls="collapseCoinflip">
                            Quelles sont les règlements du Coinflip?
                        </button>
                    </h2>
                    <div id="collapseCoinflip"
                        class="accordion-collapse {{ $game === 'coinflip' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingCoinflip" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">

                            <h5>Objectif</h5>
                            <p>Prédire un nombre entre 1 et 100 et espérer que le résultat généré aléatoirement soit en
                                haut ou en bas de la prédiction.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Le joueur sélectionne un nombre de 1 à 100.</li>
                                <li>Le système génère un nombre aléatoire dans la même plage.</li>
                                <li>Le gain dépend si le nombre est au dessus ou en dessous et du niveau de risque.
                                </li>
                            </ul>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Il s'agit d'un jeu 100% aléatoire : chaque nombre a la même probabilité.</li>
                                    <li>Ne pas chercher des « patterns » : chaque tirage est indépendant.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Poker -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingPoker">
                        <button class="accordion-button {{ $game === 'poker' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapsePoker" aria-expanded="false"
                            aria-controls="collapsePoker">
                            Quelles sont les règlements du Poker?
                        </button>
                    </h2>
                    <div id="collapsePoker" class="accordion-collapse {{ $game === 'poker' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingPoker" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">

                            <h5>Objectif</h5>
                            <p>Former la meilleure main de cinq cartes possible ou pousser les autres joueurs à se
                                coucher.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Chaque joueur reçoit 2 cartes.</li>
                                <li>Des tours de mise permettent de se coucher(fold), continuer(check), miser la mise
                                    requise pour le tour(call) ou relancer en misant plus(raise).</li>
                                <li>Au showdown, la meilleure main selon le classement officiel remporte le pot.</li>
                            </ul>
                            <h5>Classement des mains</h5>
                            <p>Voici les mains de poker classées de la plus forte à la plus faible :</p>

                            <ul>
                                <li>
                                    <strong>Flush Royale</strong> : A-K-Q-J-10 de la même couleur.
                                </li>
                                <li>
                                    <strong>Quinte Flush</strong> : cinq cartes consécutives de la même couleur.
                                </li>
                                <li>
                                    <strong>Carré</strong> : quatre cartes identiques (ex. 7-7-7-7).
                                </li>
                                <li>
                                    <strong>Full</strong> : un brelan + une paire.
                                </li>
                                <li>
                                    <strong>Flush</strong> : cinq cartes de la même couleur, non consécutives.
                                </li>
                                <li>
                                    <strong>Quinte</strong> : cinq cartes qui se suivent, couleurs
                                    différentes.
                                </li>
                                <li>
                                    <strong>Brelan</strong> : trois cartes identiques.
                                </li>
                                <li>
                                    <strong>Deux Paires</strong> : deux paires différentes.
                                </li>
                                <li>
                                    <strong>Une Paire</strong> : deux cartes identiques.
                                </li>
                                <li>
                                    <strong>Hauteur</strong> : aucune combinaison ; la valeur la plus haute l'emporte.
                                </li>
                            </ul>

                            <h5>Règles Importantes</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Mémoriser l'ordre des mains est essentiel pour jouer efficacement.</li>
                                    <li>En cas d'égalité de combinaison, la valeur des cartes (« kicker ») détermine le
                                        gagnant.</li>
                                    <li>Si la valeur des cartes sont identiques, le gain est partagé entre les joueurs
                                    </li>
                                </ul>
                            </div>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Jouez serré-agressif : peu de mains, mais jouées fortement.</li>
                                    <li>Surveillez les habitudes des adversaires pour mieux les exploiter.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Chicken Road -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingChickenRoad">
                        <button class="accordion-button {{ $game === 'chicken_road' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseChickenRoad" aria-expanded="false"
                            aria-controls="collapseChickenRoad">
                            Quelles sont les règlements du Chicken Road?
                        </button>
                    </h2>
                    <div id="collapseChickenRoad"
                        class="accordion-collapse {{ $game === 'chicken_road' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingChickenRoad" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">

                            <h5>Objectif</h5>
                            <p>Faire avancer votre poulet le plus loin possible sur la route afin d'atteindre des
                                multiplicateurs de plus en plus élevés.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Le joueur place sa mise, choisie son niveau de risque puis lance la partie.</li>
                                <li>Le poulet avance case par case, chaque case ayant un multiplicateur potentiel.</li>
                                <li>Le joueur peut choisir de cashout avant qu'un obstacle ne fasse perdre la mise.</li>
                                <li>Si le poulet frappe un obstacle, la mise est perdue.</li>
                            </ul>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Retirer tôt permet de réduire les risques : les multiplicateurs élevés sont
                                        rares.</li>
                                    <li>Évitez de jouer agressivement plusieurs fois d'affilée.</li>
                                    <li>Fixez un objectif de multiplicateur raisonnable avant la partie.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- slot machine -->
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingSlot">
                        <button class="accordion-button {{ $game === 'slot' ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseSlot" aria-expanded="false"
                            aria-controls="collapseSlot">
                            Quelles sont les règlements de la Machine à Sous ?
                        </button>
                    </h2>
                    <div id="collapseSlot" class="accordion-collapse {{ $game === 'slot' ? 'show' : 'collapse' }}"
                        aria-labelledby="headingSlot" data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">

                            <h5>Objectif</h5>
                            <p>Aligner 2 ou 3 symboles identiques pour remporter un gain selon la table de paiement.</p>

                            <h5>Probabilités et Gains</h5>
                            <p>Chaque symbole possède un poids déterminant sa probabilité d'apparition. Les trois
                                rouleaux sont indépendants.</p>

                            <ul>
                                <li><strong>Diamant</strong> — probabilité : 3%
                                    <ul>
                                        <li>3 Diamants : gain ×100 — probabilité 0.03³ = <strong>0,000027
                                                (0,0027%)</strong></li>
                                    </ul>
                                </li>

                                <li><strong>Sept</strong> — probabilité : 7%
                                    <ul>
                                        <li>3 Sept : gain ×45 — probabilité 0.07³ = <strong>0,000343 (0,0343%)</strong>
                                        </li>
                                    </ul>
                                </li>

                                <li><strong>Bar</strong> — probabilité : 15%
                                    <ul>
                                        <li>3 Bar : gain ×25 — probabilité 0.15³ = <strong>0,003375 (0,3375%)</strong>
                                        </li>
                                        <li>2 Bar : gain ×4 — probabilité 3 × 0.15² × 0.85 = <strong>0,057375
                                                (5,7375%)</strong></li>
                                    </ul>
                                </li>

                                <li><strong>Cerise</strong> — probabilité : 25%
                                    <ul>
                                        <li>3 Cerises : gain ×15 — probabilité 0.25³ = <strong>0,015625
                                                (1,5625%)</strong></li>
                                        <li>2 Cerises : gain ×2 — probabilité 3 × 0.25² × 0.75 = <strong>0,140625
                                                (14,0625%)</strong></li>
                                    </ul>
                                </li>

                                <li><strong>Orange</strong> — probabilité : 20%
                                    <ul>
                                        <li>3 Oranges : gain ×12 — probabilité 0.20³ = <strong>0,008 (0,8%)</strong>
                                        </li>
                                    </ul>
                                </li>

                                <li><strong>Citron</strong> — probabilité : 30%
                                    <ul>
                                        <li>3 Citrons : gain ×10 — probabilité 0.30³ = <strong>0,027 (2,7%)</strong>
                                        </li>
                                    </ul>
                                </li>
                            </ul>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Le joueur place sa mise et lance les trois rouleaux.</li>
                                <li>Chaque rouleau affiche un symbole en fonction de sa probabilité.</li>
                                <li>Un gain est attribué si 2 ou 3 symboles identiques s'alignent.</li>
                            </ul>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Les machines à sous sont purement basées sur le hasard.</li>
                                    <li>Les symboles rares (Diamant, Sept) offrent les gains les plus élevés.</li>
                                    <li>Pour jouer longtemps, privilégiez des mises faibles.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="headingLiotodle">
                        <button class="accordion-button {{ $game === 'liotodle' ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseLiotodle" aria-expanded="false" aria-controls="collapseLiotodle">
                            Quelles sont les règlements du Liotodle ?
                        </button>
                    </h2>
                    <div id="collapseLiotodle" class="accordion-collapse {{ $game === 'liotodle' ? 'show' : 'collapse' }}" aria-labelledby="headingLiotodle"
                        data-bs-parent="#gamesAccordion">
                        <div class="accordion-body">

                            <h5>Objectif</h5>
                            <p>Trouver le mot secret du jour en un nombre limité d'essais.</p>

                            <h5>Déroulement</h5>
                            <ul>
                                <li>Le jeu propose un mot secret de 5 lettre que le joueur doit deviner.
                                </li>
                                <li>Le joueur a un nombre limité de 6 essais pour trouver le mot.</li>
                                <li>Après chaque proposition, chaque lettre reçoit un retour visuel :</li>
                                <ul>
                                    <li><strong>Vert</strong> — la lettre est à la bonne position.</li>
                                    <li><strong>Jaune</strong> — la lettre existe dans le mot mais à une autre position.
                                    </li>
                                    <li><strong>Gris</strong> — la lettre n'existe pas dans le mot.</li>
                                </ul>
                                <li>Les lettres répétées sont évaluées selon leur occurrence dans le mot secret (ex. si
                                    le mot contient une seule "A", une seule des "A" proposées pourra être marquée).
                                </li>
                                <li>Si le joueur trouve le mot avant la fin des essais, la partie se termine et le
                                    joueur gagne. Sinon, le mot secret est dévoilé après le dernier essai.</li>
                            </ul>

                            <h5>Indications techniques</h5>
                            <ul>
                                <li>Chaque joueur aura un mot différent. Pas de triche.</li>
                                <li>La liste des mots valides (dictionnaire) détermine quelles propositions sont
                                    acceptées.</li>
                                <li>Conserver l'historique des lettres déjà testées aide à minimiser les erreurs
                                    futures.</li>
                            </ul>

                            <h5>Stratégies & Conseils</h5>
                            <div class="strategy">
                                <ul>
                                    <li>Commencer par un mot qui contient des voyelles et consonnes fréquentes pour
                                        maximiser l'information (ex. mots contenant A, E, R, S, T).</li>
                                    <li>Utiliser les retours verts pour verrouiller les positions et les jaunes pour
                                        tester d'autres positions de la même lettre.</li>
                                    <li>Éviter de gaspiller des essais avec des mots qui répètent uniquement des lettres
                                        déjà prouvées inexistantes (gris).</li>
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
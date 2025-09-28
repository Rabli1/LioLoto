(function () {
    function toInteger(value) {
        const n = Number(value);
        if (!Number.isFinite(n)) return 0;
        return Math.floor(Math.max(0, n));
    }

    function formatAmount(value) {
        return toInteger(value).toLocaleString('fr-FR');
    }

    const Balance = {
        _state: {
            balance: 0,
            userId: null,
            saveUrl: '/game/balance',
            csrfToken: null,
            displays: [],
        },

        init(config = {}) {
            const session = config.session || window.gameSession || window.blackjackSession || window.plinkoSession || {};

            this._state.userId = config.userId ?? session.userId ?? null;
            this._state.balance = toInteger(config.balance ?? session.balance ?? this._state.balance);
            this._state.saveUrl = config.saveUrl || session.endpoints?.saveBalance || this._state.saveUrl;
            this._state.csrfToken = config.csrfToken || session.csrfToken || this._findCsrfToken();

            const selectors = config.displaySelectors || ['[data-balance]', '#blackjack-balance', '#plinko-balance'];
            this._state.displays = this._collectDisplays(selectors);
            this._render();
        },

        get() {
            return this._state.balance;
        },

        canMise(amount) {
            return this._state.balance >= toInteger(amount);
        },

        miser(amount, options = {}) {
            const value = toInteger(amount);
            if (value === 0) return true;
            if (!this.canMise(value)) return false;
            this._state.balance -= value;
            this._render();
            if (options.persist !== false) {
                this.ajouterMontantJSON();
            }
            return true;
        },

        gain(amount, options = {}) {
            const value = toInteger(amount);
            if (value === 0) return;
            this._state.balance += value;
            this._render();
            if (options.persist !== false) {
                this.ajouterMontantJSON();
            }
        },

        set(amount, options = {}) {
            this._state.balance = toInteger(amount);
            this._render();
            if (options.persist !== false) {
                this.ajouterMontantJSON();
            }
        },

        _render() {
            const formatted = formatAmount(this._state.balance); //Va changer tous les #blackjack-balance et [data-balance] dans tout le DOM 
            this._state.displays.forEach((node) => {
                if (node) {
                    node.textContent = formatted;
                }
            });
        },

        _collectDisplays(selectors) { 
            const nodes = new Set();
            selectors.forEach((selector) => {
                document.querySelectorAll(selector).forEach((node) => nodes.add(node));
            });
            return Array.from(nodes); //Va selectionner le solde dans tous les éléments correspondants
        },

        ajouterMontantJSON() {
            if (!this._state.userId || !this._state.saveUrl) return;
            const payload = {
                balance: toInteger(this._state.balance),
            };

            fetch(this._state.saveUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this._state.csrfToken || this._findCsrfToken(),
                },
                body: JSON.stringify(payload),
            })
                .then((response) => {
                    if (!response.ok) {
                        return response
                            .json()
                            .catch(() => null)
                            .then((error) => {
                                console.warn('Balance.persist failed', { status: response.status, error });
                            });
                    }

                    return response.json().then((data) => {
                        if (data && typeof data.balance === 'number') {
                            this._state.balance = toInteger(data.balance);
                            this._render();
                        }
                    });
                })
                .catch((err) => {
                    console.warn('Balance.persist failed', err);
                });
        },

        _findCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]'); //Doit utiliser le token pas applicable dans JSON
            return meta ? meta.content : null;
        },
    };

    window.Balance = Balance;
})();


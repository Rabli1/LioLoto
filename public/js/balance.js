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
                this._persist();
            }
            return true;
        },

        gain(amount, options = {}) {
            const value = toInteger(amount);
            if (value === 0) return;
            this._state.balance += value;
            this._render();
            if (options.persist !== false) {
                this._persist();
            }
        },

        set(amount, options = {}) {
            this._state.balance = toInteger(amount);
            this._render();
            if (options.persist !== false) {
                this._persist();
            }
        },

        _render() {
            const formatted = formatAmount(this._state.balance);
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
            return Array.from(nodes);
        },

        _persist() {
            if (!this._state.userId || !this._state.saveUrl) return;
            const payload = {
                balance: toInteger(this._state.balance),
                game: this._inferGameName(),
            };

            fetch(this._state.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this._state.csrfToken || this._findCsrfToken(),
                },
                body: JSON.stringify(payload),
            }).catch((err) => {
                console.warn('Balance.persist failed', err);
            });
        },

        _findCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : null;
        },

        _inferGameName() {
            const path = window.location.pathname || '';
            if (path.includes('blackjack')) return 'blackjack';
            if (path.includes('plinko')) return 'plinko';
            return 'game';
        },
    };

    window.Balance = Balance;
})();


/*https://reactbits.dev/components/pill-nav*/

import { useEffect, useRef, useState } from 'react';
import { gsap } from 'gsap';
import '../../css/nav.css';

const PillNav = ({ activeHref, user, admin, users = [] }) => {
    const ease = 'power3.easeOut';
    const baseColor = '#fff';
    const pillColor = '#dc3545';
    const hoveredPillTextColor = '#dc3545';
    const resolvedPillTextColor = baseColor;

    const [searchOpen, setSearchOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [localUsers, setLocalUsers] = useState(users || []);
    const [windowWidth, setWindowWidth] = useState(window.innerWidth);

    const circleRefs = useRef([]);
    const tlRefs = useRef([]);
    const activeTweenRefs = useRef([]);
    const navItemsRef = useRef(null);
    const searchBoxRef = useRef(null);
    const inputRef = useRef(null);

    const items = [
        { href: '/game', label: 'Jeux' },
        { href: '/leaderboard', label: 'Classement' },
        ...(user
            ? [
                { href: `/user/profile?id=${user.id}`, label: 'Profil' },
                { href: '/user/deconnection', label: 'Déconnexion' },
            ]
            : [
                { href: '/user/connection', label: 'Connexion' },
                { href: '/user/signIn', label: 'Inscription' },
            ]),
        ...(admin ? [{ href: '/admin/dashboard', label: 'Admin' }] : []),
        { href: '/user/support', label: 'Support' },
    ];

    useEffect(() => {
        const handleResize = () => {
            setWindowWidth(window.innerWidth);
        };

        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    useEffect(() => {
        if ((users == null || users.length === 0) && navItemsRef.current) {
            const datasetUsers = navItemsRef.current.closest('nav')?.dataset?.users;
            if (datasetUsers) {
                try {
                    const parsed = JSON.parse(datasetUsers);
                    setLocalUsers(Array.isArray(parsed) ? parsed : []);
                } catch (err) {
                }
            }
        } else {
            setLocalUsers(users || []);
        }
    }, [users]);

    const filteredUsers = searchQuery.trim() !== ''
        ? localUsers
            .filter(u => {
                if (!u?.name) return false;
                return u.name.toLowerCase().startsWith(searchQuery.trim().toLowerCase());
            })
            .slice(0, 5)
        : [];

    useEffect(() => {
        const layout = () => {
            circleRefs.current.forEach(circle => {
                if (!circle?.parentElement) return;
                const pill = circle.parentElement;
                const rect = pill.getBoundingClientRect();
                const { width: w, height: h } = rect;
                const R = ((w * w) / 4 + h * h) / (2 * h);
                const D = Math.ceil(2 * R) + 2;
                const delta = Math.ceil(R - Math.sqrt(Math.max(0, R * R - (w * w) / 4))) + 1;
                const originY = D - delta;

                circle.style.width = `${D}px`;
                circle.style.height = `${D}px`;
                circle.style.bottom = `-${delta}px`;

                gsap.set(circle, { xPercent: -50, scale: 0, transformOrigin: `50% ${originY}px` });

                const label = pill.querySelector('.pill-label');
                const white = pill.querySelector('.pill-label-hover');

                if (label) gsap.set(label, { y: 0 });
                if (white) gsap.set(white, { y: h + 12, opacity: 0 });

                const index = circleRefs.current.indexOf(circle);
                if (index === -1) return;

                tlRefs.current[index]?.kill();
                const tl = gsap.timeline({ paused: true });
                tl.to(circle, { scale: 1.2, xPercent: -50, duration: 2, ease, overwrite: 'auto' }, 0);
                tl.to(label, { y: -(h + 8), duration: 2, ease, overwrite: 'auto' }, 0);
                tl.to(white, { y: 0, opacity: 1, duration: 2, ease, overwrite: 'auto' }, 0);
                tlRefs.current[index] = tl;
            });
        };

        layout();
        window.addEventListener('resize', layout);
        return () => window.removeEventListener('resize', layout);
    }, [items]);

    const handleEnter = i => {
        const tl = tlRefs.current[i];
        if (!tl) return;
        activeTweenRefs.current[i]?.kill();
        activeTweenRefs.current[i] = tl.tweenTo(tl.duration(), { duration: 0.3, ease });
    };

    const handleLeave = i => {
        const tl = tlRefs.current[i];
        if (!tl) return;
        activeTweenRefs.current[i]?.kill();
        activeTweenRefs.current[i] = tl.tweenTo(0, { duration: 0.2, ease });
    };

    const toggleSearch = () => {
        setSearchOpen(prev => {
            const next = !prev;
            if (next) {
                setTimeout(() => inputRef.current?.focus(), 0);
            } else {
                setSearchQuery('');
            }
            return next;
        });
    };

    const closeSearch = () => {
        setSearchOpen(false);
        setSearchQuery('');
    };

    useEffect(() => {
        const onDocClick = (e) => {
            if (!searchOpen) return;
            if (!searchBoxRef.current) return;
            if (!searchBoxRef.current.contains(e.target) && !e.target.closest('.btn-search-toggle')) {
                closeSearch();
            }
        };
        document.addEventListener('mousedown', onDocClick);
        return () => document.removeEventListener('mousedown', onDocClick);
    }, [searchOpen]);

    const inputStyle = {
        boxShadow: 'none !important',
        outline: 'none !important',
        border: '1px solid #ddd'
    };

    const inputFocusStyle = {
        ...inputStyle,
        borderColor: '#ddd'
    };

    const cssVars = {
        ['--base']: baseColor,
        ['--pill-bg']: pillColor,
        ['--hover-text']: hoveredPillTextColor,
        ['--pill-text']: resolvedPillTextColor
    };

    return (
        <div className="pill-nav-container" data-users={localUsers && localUsers.length ? JSON.stringify(localUsers) : undefined}>
            <nav className="pill-nav" style={cssVars}>
                <div className="pill-nav-items" ref={navItemsRef}>
                    <ul className="pill-list">
                        {items.map((item, i) => (
                            <li key={item.href}>
                                <a
                                    href={item.href}
                                    className={`pill${activeHref.startsWith(item.href.split('?')[0]) ? ' is-active' : ''}`}
                                    onMouseEnter={() => handleEnter(i)}
                                    onMouseLeave={() => handleLeave(i)}
                                >
                                    <span className="hover-circle" ref={el => (circleRefs.current[i] = el)} />
                                    <span className="label-stack">
                                        <span className="pill-label">{item.label}</span>
                                        <span className="pill-label-hover">{item.label}</span>
                                    </span>
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>

                {windowWidth > 750 && (
                    <div className="nav-item d-flex align-items-center ms-2 position-relative">
                        <button
                            className="btn btn-link p-0 text-white btn-search-toggle"
                            onClick={toggleSearch}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                            </svg>
                        </button>

                        {searchOpen && (
                            <div
                                ref={searchBoxRef}
                                className="position-absolute end-0 top-100 mt-2 bg-white rounded shadow p-2"
                                style={{ width: '250px', zIndex: 1000 }}
                            >
                                <input
                                    ref={inputRef}
                                    type="text"
                                    className="form-control mb-1"
                                    placeholder="Rechercher utilisateur"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    onFocus={(e) => e.target.style.outline = 'none'}
                                    style={inputStyle}
                                />
                                <div className="w-100" style={{ maxHeight: '250px', overflowY: 'auto' }}>
                                    {filteredUsers.map(u => (
                                        <div key={u.id} className="d-flex align-items-center p-1 border-bottom">
                                            <i className={`fa-solid ${u.profileImage} pfp-${u.profileColor} me-2`}></i>
                                            <a
                                                href={`/user/profile?id=${u.id}`}
                                                className="text-dark text-decoration-none"
                                                onClick={closeSearch}
                                            >
                                                {u.name}
                                            </a>
                                        </div>
                                    ))}
                                </div>
                                <button
                                    type="button"
                                    className="btn btn-link p-0 position-absolute"
                                    style={{ top: '10px', right: '10px' }}
                                    onClick={closeSearch}
                                >
                                    <div class="icon-container">
                                        <svg width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        )}
                    </div>
                )}
            </nav>
        </div>
    );
};

export default PillNav;
import { useEffect, useRef, useState } from "react";
import { Link, NavLink } from "react-router-dom";

function Navbar() {
    const [menuOpen, setMenuOpen] = useState(false);
    const menuButtonRef = useRef(null);

    useEffect(() => {
        if (!menuOpen) {
            return undefined;
        }

        function handleKeyDown(event) {
            if (event.key === "Escape") {
                setMenuOpen(false);
                menuButtonRef.current?.focus();
            }
        }

        document.addEventListener("keydown", handleKeyDown);
        return () => document.removeEventListener("keydown", handleKeyDown);
    }, [menuOpen]);

    return (
        <nav className="navbar" aria-label="Main navigation">
            <div className="navbar-logo">
                <Link to="/" aria-label="Araullo University South Student Council home">
                    <img
                        src="/assets/aussc-header.png"
                        alt="Araullo University South Student Council"
                    />
                </Link>
            </div>

            <button
                className={`navbar-menu-button${menuOpen ? " is-open" : ""}`}
                ref={menuButtonRef}
                type="button"
                aria-label={menuOpen ? "Close navigation menu" : "Open navigation menu"}
                aria-expanded={menuOpen}
                aria-controls="primary-navigation"
                onClick={() => setMenuOpen((open) => !open)}
            >
                <span />
                <span />
                <span />
            </button>

            <div
                className={`navbar-links${menuOpen ? " is-open" : ""}`}
                id="primary-navigation"
            >
                <NavLink to="/" onClick={() => setMenuOpen(false)}>
                    Home
                </NavLink>
                <NavLink to="/events" onClick={() => setMenuOpen(false)}>
                    Events
                </NavLink>
                <NavLink to="/my-events" onClick={() => setMenuOpen(false)}>
                    My Events
                </NavLink>
                <NavLink to="/login" onClick={() => setMenuOpen(false)}>
                    Login
                </NavLink>
            </div>
        </nav>
    );
}

export default Navbar;
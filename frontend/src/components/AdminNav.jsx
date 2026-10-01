import { Link, NavLink } from "react-router-dom";

function AdminNav() {
    const links = [
        { label: "Overview", to: "/admin", end: true },
        { label: "Events", to: "/admin/events" },
        { label: "Registrations", to: "/admin/registrations" },
        { label: "Attendance", to: "/admin/attendance" },
        { label: "People", to: "/admin/users" },
        { label: "Highlights", to: "/admin/highlights" }
    ];

    return (
        <header className="admin-topbar">
            <div className="admin-topbar-main">
                <Link className="admin-brand" to="/admin" aria-label="Admin overview">
                    <img
                        src="/assets/aussc-header.png"
                        alt="Araullo University South Student Council"
                    />
                    <span>ADMINISTRATION</span>
                </Link>
                <Link className="admin-view-site" to="/">
                    View public site
                    <span aria-hidden="true">↗</span>
                </Link>
            </div>

            <nav className="admin-nav" aria-label="Admin sections">
                {links.map((link) => (
                    <NavLink
                        key={link.to}
                        to={link.to}
                        end={link.end}
                        className={({ isActive }) =>
                            `admin-nav-link${isActive ? " is-active" : ""}`
                        }
                    >
                        {link.label}
                    </NavLink>
                ))}
            </nav>
        </header>
    );
}

export default AdminNav;

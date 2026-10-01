import { useEffect, useState } from "react";

import AdminNav from "../../components/AdminNav";
import { getAdminUsers } from "../../services/api";

function Users() {
    const [users, setUsers] = useState([]);
    const [studentCount, setStudentCount] = useState(0);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [query, setQuery] = useState("");
    const [roleFilter, setRoleFilter] = useState("all");

    useEffect(() => {
        getAdminUsers()
            .then((data) => {
                setUsers(data.users || []);
                setStudentCount(Number(data.student_count) || 0);
            })
            .catch((requestError) => setError(requestError.message))
            .finally(() => setLoading(false));
    }, []);

    const filteredUsers = users.filter((user) => {
        const matchesRole = roleFilter === "all" || user.role === roleFilter;
        const searchText = `${user.first_name} ${user.last_name} ${user.student_id} ${user.email} ${user.course || ""}`.toLowerCase();
        return matchesRole && searchText.includes(query.trim().toLowerCase());
    });

    return (
        <>
        <AdminNav />
        <main className="admin-page">
            <section className="admin-header">
                <div>
                <span>ACCOUNT DIRECTORY</span>
                <h1>Users</h1>
                <p>{studentCount} student account{studentCount === 1 ? "" : "s"}</p>
                </div>
            </section>

            <section className="admin-list-section" aria-label="User directory">
                <div className="admin-list-toolbar">
                    <label className="admin-search-field">
                        <span className="sr-only">Search people</span>
                        <input
                            type="search"
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder="Search name, email, or student ID"
                        />
                    </label>
                    <label className="admin-filter-field">
                        <span className="sr-only">Filter by role</span>
                        <select value={roleFilter} onChange={(event) => setRoleFilter(event.target.value)}>
                            <option value="all">All roles</option>
                            <option value="student">Students</option>
                            <option value="admin">Administrators</option>
                        </select>
                    </label>
                </div>

                {error && <p className="admin-feedback is-error" role="alert">{error}</p>}
                {loading ? (
                    <p className="admin-feedback" role="status">Loading accounts…</p>
                ) : filteredUsers.length ? (
                    <div className="admin-data-scroll">
                        <table className="admin-data-table">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Student ID</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Program</th>
                                    <th scope="col">Year</th>
                                    <th scope="col">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredUsers.map((user) => (
                                    <tr key={user.id}>
                                        <td><strong>{user.first_name} {user.last_name}</strong></td>
                                        <td>{user.student_id}</td>
                                        <td>{user.email}</td>
                                        <td>{user.course || "—"}</td>
                                        <td>{user.year_level || "—"}</td>
                                        <td><span className={`admin-status is-${user.role}`}>{user.role}</span></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="admin-empty-state">
                        <h2>{users.length ? "No matching accounts" : "No accounts found"}</h2>
                        <p>{users.length
                            ? "Try another search or role filter."
                            : "Accounts created through registration will appear here."}</p>
                    </div>
                )}
            </section>
        </main>
        </>
    );
}

export default Users;

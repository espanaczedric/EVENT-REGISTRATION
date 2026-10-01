import AdminNav from "../../components/AdminNav";
import { Link } from "react-router-dom";

function Attendance() {
    return (
        <>
        <AdminNav />
        <main className="admin-page">
            <section className="admin-header">
                <div>
                    <span>ATTENDANCE</span>
                    <h1>Check-in</h1>
                    <p>Verify event registrations and record attendee check-ins.</p>
                </div>
                <Link to="/admin/attendance/scanner" className="admin-primary-action">
                    Open QR scanner
                </Link>
            </section>
        </main>
        </>
    );
}

export default Attendance;

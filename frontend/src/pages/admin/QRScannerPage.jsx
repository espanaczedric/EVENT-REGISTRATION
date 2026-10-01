import QRScanner from "../../components/QRScanner";
import AdminNav from "../../components/AdminNav";

function QRScannerPage() {
    return (
        <>
        <AdminNav />
        <main className="admin-page">
            <section className="admin-header">
                <span>ATTENDANCE</span>
                <h1>Check-in</h1>
            </section>
            <QRScanner />
        </main>
        </>
    );
}

export default QRScannerPage;

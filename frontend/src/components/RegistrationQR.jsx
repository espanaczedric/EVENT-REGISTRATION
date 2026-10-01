import QRCode from "react-qr-code";

function RegistrationQR({
    registrationCode
}) {

    return (

        <div className="registration-qr">

            <h2>
                Registration Confirmed
            </h2>

            <p>
                Show this QR code at
                the event entrance.
            </p>

            <div className="qr-box">

                <QRCode
                    value={registrationCode}
                    size={250}
                />

            </div>

            <strong>
                {registrationCode}
            </strong>

        </div>
    );
}

export default RegistrationQR;
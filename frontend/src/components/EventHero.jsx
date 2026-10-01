import { Link } from "react-router-dom";
import EventImage from "./EventImage";

function EventHero({ event }) {

    if (!event) {
        return null;
    }

    const eventDate =
        new Date(event.start_date)
            .toLocaleDateString(
                "en-US",
                {
                    month: "long",
                    day: "numeric",
                    year: "numeric"
                }
            );

    return (

        <section className="event-hero">

            <EventImage
                src={event.cover_image}
                alt={event.title}
                className="event-hero-image"
                loading="eager"
            />

            <div className="hero-overlay">

                <div className="hero-content">

                    <span className="event-label">

                        {event.status === "ongoing"
                            ? "HAPPENING NOW"
                            : "UPCOMING EVENT"}

                    </span>

                    <h1>
                        {event.title}
                    </h1>

                    <div className="hero-event-meta">
                        <p className="event-date">
                            <span>DATE</span>
                            {eventDate}
                        </p>

                        {event.location && (
                            <p className="event-location">
                                <span>LOCATION</span>
                                {event.location}
                            </p>
                        )}
                    </div>

                    <p className="event-description">
                        {event.description}
                    </p>

                    <div className="hero-buttons">

                        <Link
                            to={`/register/${event.id}`}
                            className="register-button"
                        >
                            Register Now
                        </Link>

                        <Link
                            to={`/events/${event.id}`}
                            className="details-button"
                        >
                            View Event
                        </Link>

                    </div>

                </div>

            </div>

        </section>
    );
}

export default EventHero;
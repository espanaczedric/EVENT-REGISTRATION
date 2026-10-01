import { Link } from "react-router-dom";
import EventImage from "./EventImage";

function EventCarousel({ events }) {
    if (!events.length) {
        return (
            <div className="event-empty-state">
                <h3>No previous events yet</h3>
                <p>Completed events will appear here.</p>
            </div>
        );
    }

    return (

        <div className="event-carousel">

            {events.map(event => {

                const date =
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

                    <article
                        className="event-slide"
                        key={event.id}
                    >

                        <div className="event-image">
                            <EventImage
                                src={event.cover_image}
                                alt={event.title}
                                className="event-carousel-image"
                            />
                        </div>

                        <div className="event-info">

                            <span className="event-date">
                                {date}
                            </span>

                            <h3>
                                {event.title}
                            </h3>

                            <p>
                                {event.description}
                            </p>

                            <Link
                                to={`/events/${event.id}`}
                            >
                                View Highlights →
                            </Link>

                        </div>

                    </article>

                );
            })}

        </div>
    );
}

export default EventCarousel;
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import Navbar from "../components/Navbar";
import EventImage from "../components/EventImage";
import { getUpcomingEvent, getPreviousEvents } from "../services/api";

function Events() {
    const [upcoming, setUpcoming] = useState(null);
    const [previous, setPrevious] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadError, setLoadError] = useState(false);

    useEffect(() => {
        async function loadEvents() {
            try {
                const upcomingData = await getUpcomingEvent();
                const previousData = await getPreviousEvents();

                setUpcoming(upcomingData.event);
                setPrevious(previousData.events || []);
            } catch (error) {
                setLoadError(true);
                console.error("Failed to load events:", error);
            } finally {
                setLoading(false);
            }
        }

        loadEvents();
    }, []);

    if (loading) {
        return <div className="loading">Loading events...</div>;
    }

    return (
        <>
            <Navbar />

            <main className="events-page">

                <section className="events-header">
                    <span>UNIVERSITY EVENTS</span>

                    <h1>
                        Discover Events
                    </h1>

                    <p>
                        Explore upcoming university events,
                        register your spot, and relive previous
                        campus experiences.
                    </p>
                </section>

                {loadError ? (
                    <p className="event-load-error" role="alert">
                        Event information could not be loaded. Refresh the page to try again.
                    </p>
                ) : upcoming ? (
                    <section className="featured-event">

                        <EventImage
                            src={upcoming.cover_image}
                            alt={upcoming.title}
                            className="featured-event-image"
                            loading="eager"
                        />

                        <div className="featured-event-content">

                            <span>
                                {upcoming.status === "ongoing"
                                    ? "HAPPENING NOW"
                                    : "UPCOMING EVENT"}
                            </span>

                            <h2>
                                {upcoming.title}
                            </h2>

                            <p>
                                {upcoming.description}
                            </p>

                            <p className="featured-event-meta">
                                <strong>DATE</strong>
                                {new Date(upcoming.start_date).toLocaleDateString("en-US", {
                                    month: "long",
                                    day: "numeric",
                                    year: "numeric"
                                })}
                                {upcoming.location && ` · ${upcoming.location}`}
                            </p>

                            <Link
                                to={`/events/${upcoming.id}`}
                                className="register-button"
                            >
                                View Event
                            </Link>

                        </div>

                    </section>
                ) : (
                    <div className="event-empty-state">
                        <h2>No upcoming events yet</h2>
                        <p>Check back soon for the next campus event.</p>
                    </div>
                )}

                <section className="all-events">

                    <div className="section-heading">

                        <span>ARCHIVE</span>

                        <h2>
                            Previous Events
                        </h2>

                    </div>

                    {loadError ? null : previous.length ? (
                    <div className="events-grid">

                        {previous.map((event) => (

                            <article
                                className="event-card"
                                key={event.id}
                            >

                                <EventImage
                                    src={event.cover_image}
                                    alt={event.title}
                                    className="event-card-image"
                                />

                                <div className="event-card-content">

                                    <small>
                                        {new Date(
                                            event.start_date
                                        ).toLocaleDateString(
                                            "en-US",
                                            {
                                                month: "long",
                                                day: "numeric",
                                                year: "numeric"
                                            }
                                        )}
                                    </small>

                                    <h3>
                                        {event.title}
                                    </h3>

                                    <p>
                                        {event.description}
                                    </p>

                                    <Link
                                        to={`/events/${event.id}`}
                                    >
                                        View Event →
                                    </Link>

                                </div>

                            </article>

                        ))}

                    </div>
                    ) : (
                        <div className="event-empty-state">
                            <h3>No previous events yet</h3>
                            <p>Completed events will appear here.</p>
                        </div>
                    )}

                </section>

            </main>
        </>
    );
}

export default Events;
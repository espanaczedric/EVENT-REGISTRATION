import { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import Navbar from "../components/Navbar";
import EventHero from "../components/EventHero";
import EventCarousel from "../components/EventCarousel";

import {
    getUpcomingEvent,
    getPreviousEvents
} from "../services/api";

function Home() {

    const [upcoming, setUpcoming] =
        useState(null);

    const [previous, setPrevious] =
        useState([]);

    const [loading, setLoading] =
        useState(true);

    const [loadError, setLoadError] =
        useState(false);


    useEffect(() => {

        async function loadEvents() {

            try {

                const upcomingData =
                    await getUpcomingEvent();

                const previousData =
                    await getPreviousEvents();

                setUpcoming(
                    upcomingData.event
                );

                setPrevious(
                    previousData.events || []
                );

            } catch (error) {

                setLoadError(true);

                console.error(
                    "Failed to load events:",
                    error
                );

            } finally {

                setLoading(false);

            }
        }

        loadEvents();

    }, []);


    if (loading) {

        return (
            <div className="loading">
                Loading events...
            </div>
        );

    }


    return (

        <>

            <Navbar />

            <main>

                {loadError ? (
                    <section className="event-empty-state upcoming-empty-state" role="alert">
                        <span>EVENTS</span>
                        <h1>Event information is unavailable</h1>
                        <p>Please refresh the page to try again.</p>
                    </section>
                ) : upcoming ? (
                    <EventHero event={upcoming} />
                ) : (
                    <section className="event-empty-state upcoming-empty-state">
                        <span>UPCOMING</span>
                        <h1>No upcoming events yet</h1>
                        <p>Check back soon for the next campus event.</p>
                        <Link to="/events" className="register-button">
                            Browse Events
                        </Link>
                    </section>
                )}


                <section className="previous-section">

                    <div className="section-heading">

                        <span>
                            EXPLORE
                        </span>

                        <h2>
                            Previous Events
                        </h2>

                        <p>
                            Take a look back at
                            the moments that brought
                            our university community
                            together.
                        </p>

                    </div>


                    {loadError ? (
                        <p className="event-load-error" role="alert">
                            Previous events could not be loaded.
                        </p>
                    ) : (
                        <EventCarousel events={previous} />
                    )}

                </section>

            </main>

        </>
    );
}

export default Home;
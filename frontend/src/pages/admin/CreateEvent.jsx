import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";

import AdminNav from "../../components/AdminNav";
import EventImage from "../../components/EventImage";
import {
    createAdminEvent,
    getEvent,
    getEventImageUrl,
    updateAdminEvent
} from "../../services/api";

function toDateTimeLocal(value) {
    return value ? value.replace(" ", "T").slice(0, 16) : "";
}

function CreateEvent({ editMode = false }) {
    const navigate = useNavigate();
    const { id } = useParams();
    const [form, setForm] = useState({
        title: "",
        description: "",
        location: "",
        start_date: "",
        end_date: "",
        registration_deadline: "",
        capacity: "",
        status: "upcoming"
    });
    const [coverImage, setCoverImage] = useState(null);
    const [existingImage, setExistingImage] = useState("");
    const [imagePreview, setImagePreview] = useState("");
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [eventLoading, setEventLoading] = useState(editMode);
    const [eventLoadError, setEventLoadError] = useState("");

    useEffect(() => {
        if (!editMode) return undefined;

        let active = true;
        getEvent(id)
            .then((data) => {
                if (!data.success || !data.event) {
                    throw new Error(data.message || "Event not found.");
                }

                if (!active) return;
                const event = data.event;
                setForm({
                    title: event.title || "",
                    description: event.description || "",
                    location: event.location || "",
                    start_date: toDateTimeLocal(event.start_date),
                    end_date: toDateTimeLocal(event.end_date),
                    registration_deadline: toDateTimeLocal(event.registration_deadline),
                    capacity: String(event.capacity ?? ""),
                    status: event.status || "upcoming"
                });
                setExistingImage(getEventImageUrl(event.cover_image));
            })
            .catch((requestError) => {
                if (active) setEventLoadError(requestError.message);
            })
            .finally(() => {
                if (active) setEventLoading(false);
            });

        return () => {
            active = false;
        };
    }, [editMode, id]);

    useEffect(() => {
        if (!coverImage) {
            setImagePreview(existingImage);
            return undefined;
        }

        const previewUrl = URL.createObjectURL(coverImage);
        setImagePreview(previewUrl);
        return () => URL.revokeObjectURL(previewUrl);
    }, [coverImage, existingImage]);

    const handleChange = (event) => {
        const { name, value } = event.target;
        setForm((current) => ({ ...current, [name]: value }));
    };

    const handleImageChange = (event) => {
        const file = event.target.files?.[0] || null;
        setError("");

        if (file && file.size > 5 * 1024 * 1024) {
            setCoverImage(null);
            event.target.value = "";
            setError("Choose an image smaller than 5 MB.");
            return;
        }

        setCoverImage(file);
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        setError("");

        try {
            const formData = new FormData(event.currentTarget);
            if (editMode) {
                await updateAdminEvent(id, formData);
            } else {
                await createAdminEvent(formData);
            }
            navigate("/admin/events");
        } catch (requestError) {
            setError(requestError.message || "The event could not be saved. Please try again.");
        } finally {
            setSaving(false);
        }
    };

    const previewDate = form.start_date
        ? new Date(form.start_date).toLocaleString("en-US", {
            month: "long",
            day: "numeric",
            year: "numeric",
            hour: "numeric",
            minute: "2-digit"
        })
        : "Date and time will appear here";

    if (eventLoading) {
        return (
            <>
                <AdminNav />
                <main className="admin-page">
                    <p className="admin-feedback" role="status">Loading event…</p>
                </main>
            </>
        );
    }

    if (editMode && eventLoadError) {
        return (
            <>
                <AdminNav />
                <main className="admin-page">
                    <p className="admin-feedback is-error" role="alert">{eventLoadError}</p>
                    <Link to="/admin/events" className="admin-secondary-action">Back to events</Link>
                </main>
            </>
        );
    }

    return (
        <>
            <AdminNav />
            <main className="admin-page admin-create-page">
                <section className="admin-header admin-page-heading">
                    <div>
                        <span>EVENT OPERATIONS</span>
                        <h1>{editMode ? "Edit event" : "Create event"}</h1>
                        <p>{editMode
                            ? "Update the information students use to plan and register."
                            : "Publish the details students need to decide and register."}</p>
                    </div>
                    <Link to="/admin/events" className="admin-secondary-action">
                        <span aria-hidden="true">←</span>
                        Back to events
                    </Link>
                </section>

                {error && <p className="admin-feedback is-error" role="alert">{error}</p>}

                <div className="admin-create-layout">
                    <form className="admin-form admin-create-form" onSubmit={handleSubmit}>
                        <section className="admin-form-section">
                            <div className="admin-section-heading">
                                <div>
                                    <span>01 · EVENT DETAILS</span>
                                    <h2>What are students joining?</h2>
                                </div>
                            </div>

                            <label htmlFor="event-title">Event title</label>
                            <input
                                id="event-title"
                                name="title"
                                value={form.title}
                                onChange={handleChange}
                                maxLength={255}
                                placeholder="e.g. Organization Fair 2026"
                                required
                            />

                            <label htmlFor="event-description">About the event</label>
                            <textarea
                                id="event-description"
                                name="description"
                                value={form.description}
                                onChange={handleChange}
                                rows={5}
                                maxLength={3000}
                                placeholder="Explain what will happen and who should attend."
                                required
                            />

                            <label htmlFor="event-location">Location</label>
                            <input
                                id="event-location"
                                name="location"
                                value={form.location}
                                onChange={handleChange}
                                maxLength={255}
                                placeholder="Campus, building, or venue"
                                required
                            />
                        </section>

                        <section className="admin-form-section">
                            <div className="admin-section-heading">
                                <div>
                                    <span>02 · DATE & CAPACITY</span>
                                    <h2>Set the event schedule</h2>
                                </div>
                            </div>

                            <div className="admin-form-grid">
                                <label htmlFor="event-start">Starts</label>
                                <input
                                    id="event-start"
                                    type="datetime-local"
                                    name="start_date"
                                    value={form.start_date}
                                    onChange={handleChange}
                                    required
                                />

                                <label htmlFor="event-end">Ends</label>
                                <input
                                    id="event-end"
                                    type="datetime-local"
                                    name="end_date"
                                    value={form.end_date}
                                    onChange={handleChange}
                                    required
                                />

                                <label htmlFor="event-deadline">Registration closes <span>Optional</span></label>
                                <input
                                    id="event-deadline"
                                    type="datetime-local"
                                    name="registration_deadline"
                                    value={form.registration_deadline}
                                    onChange={handleChange}
                                />

                                <label htmlFor="event-capacity">Capacity</label>
                                <input
                                    id="event-capacity"
                                    type="number"
                                    name="capacity"
                                    min="1"
                                    step="1"
                                    value={form.capacity}
                                    onChange={handleChange}
                                    placeholder="e.g. 250"
                                    required
                                />

                                <label htmlFor="event-status">Status</label>
                                <select
                                    id="event-status"
                                    name="status"
                                    value={form.status}
                                    onChange={handleChange}
                                >
                                    <option value="upcoming">Upcoming</option>
                                    <option value="ongoing">Happening now</option>
                                    {editMode && <option value="completed">Completed</option>}
                                    {editMode && <option value="cancelled">Cancelled</option>}
                                </select>
                            </div>
                        </section>

                        <section className="admin-form-section">
                            <div className="admin-section-heading">
                                <div>
                                    <span>03 · COVER IMAGE</span>
                                    <h2>Choose the event thumbnail</h2>
                                </div>
                            </div>
                            <label htmlFor="event-cover">
                                {editMode ? "Replace cover image" : "Cover image"}
                                <span>JPG, PNG, or WebP · max 5 MB{editMode ? " · optional" : ""}</span>
                            </label>
                            <input
                                id="event-cover"
                                className="admin-file-input"
                                type="file"
                                name="cover_image"
                                accept="image/jpeg,image/png,image/webp"
                                onChange={handleImageChange}
                                required={!editMode}
                            />
                        </section>

                        <div className="admin-form-actions">
                            <Link to="/admin/events" className="admin-secondary-action">Cancel</Link>
                            <button className="admin-primary-action" type="submit" disabled={saving}>
                                {saving
                                    ? editMode ? "Saving…" : "Publishing…"
                                    : editMode ? "Save changes" : "Publish event"}
                            </button>
                        </div>
                    </form>

                    <aside className="admin-event-preview" aria-label="Student event preview">
                        <div className="admin-preview-heading">
                            <span>STUDENT PREVIEW</span>
                            <h2>Event card</h2>
                        </div>
                        <article className="admin-preview-card">
                            <div className="admin-preview-media">
                                {imagePreview ? (
                                    <EventImage
                                        src={imagePreview}
                                        alt="Selected event cover preview"
                                        className="admin-preview-event-image"
                                        loading="eager"
                                    />
                                ) : (
                                    <span>Select a cover image</span>
                                )}
                            </div>
                            <div className="admin-preview-copy">
                                <span>{previewDate}</span>
                                <h3>{form.title || "Your event title"}</h3>
                                <p>{form.description || "Your event description will appear here."}</p>
                                <strong>{form.location || "Event location"}</strong>
                            </div>
                        </article>
                    </aside>
                </div>
            </main>
        </>
    );
}

export default CreateEvent;

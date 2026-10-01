import { useState } from "react";
import { Link, useParams, useNavigate } from "react-router-dom";

import { apiRequest } from "../services/api";

function Register() {

    const { id } = useParams();

    const navigate = useNavigate();

    const [form, setForm] = useState({
        first_name: "",
        last_name: "",
        student_id: "",
        email: "",
        course: "",
        year_level: ""
    });

    const [loading, setLoading] =
        useState(false);


    function handleChange(event) {

        setForm({
            ...form,
            [event.target.name]:
                event.target.value
        });

    }


    async function handleSubmit(event) {

        event.preventDefault();

        setLoading(true);

        try {

            const data = await apiRequest("/registration/create.php", {
                method: "POST",
                body: JSON.stringify({
                    event_id: id,
                    ...form
                })
            });

            if (!data.success) {

                alert(data.message);

                return;
            }

            navigate(
                `/registration/${data.registration_code}`
            );

        } catch (error) {

            alert(
                "Unable to register."
            );

        } finally {

            setLoading(false);

        }

    }


    return (

        <main className="registration-page">

            <h1>
                Register for Event
            </h1>

            <form
                onSubmit={handleSubmit}
            >

                <Link to="/" className="form-back-link">
                    <span aria-hidden="true">←</span>
                    Back to Home
                </Link>

                <input
                    name="first_name"
                    placeholder="First Name"
                    value={form.first_name}
                    onChange={handleChange}
                    required
                />

                <input
                    name="last_name"
                    placeholder="Last Name"
                    value={form.last_name}
                    onChange={handleChange}
                    required
                />

                <input
                    name="student_id"
                    placeholder="01-1234-123456"
                    pattern="01-[0-9]{4}-[0-9]{6}"
                    title="Use the format 01-1234-123456."
                    maxLength={14}
                    value={form.student_id}
                    onChange={handleChange}
                    required
                />

                <input
                    name="email"
                    type="email"
                    placeholder="Email"
                    value={form.email}
                    onChange={handleChange}
                    required
                />

                <input
                    name="course"
                    placeholder="Course / Program"
                    value={form.course}
                    onChange={handleChange}
                    required
                />

                <select
                    name="year_level"
                    value={form.year_level}
                    onChange={handleChange}
                    required
                >

                    <option value="">
                        Select Year Level
                    </option>

                    <option value="1st Year">
                        1st Year
                    </option>

                    <option value="2nd Year">
                        2nd Year
                    </option>

                    <option value="3rd Year">
                        3rd Year
                    </option>

                    <option value="4th Year">
                        4th Year
                    </option>

                </select>


                <button
                    type="submit"
                    disabled={loading}
                >

                    {loading
                        ? "Registering..."
                        : "Register Now"}

                </button>

            </form>

        </main>
    );
}

export default Register;
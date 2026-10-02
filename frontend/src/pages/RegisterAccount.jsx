import { useState } from "react";
import { API_URL } from "../services/api";
import {
    Link,
    useNavigate
} from "react-router-dom";

function RegisterAccount() {

    const navigate = useNavigate();

    const [form, setForm] = useState({
        student_id: "",
        first_name: "",
        last_name: "",
        email: "",
        password: "",
        confirm_password: "",
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


        if (
            form.password !==
            form.confirm_password
        ) {

            alert(
                "Passwords do not match."
            );

            return;
        }


        if (form.password.length < 8) {

            alert(
                "Password must be at least 8 characters."
            );

            return;
        }


        setLoading(true);


        try {

            const response = await fetch(
                `${API_URL}/auth/register.php`,
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body: JSON.stringify({
                        student_id:
                            form.student_id,

                        first_name:
                            form.first_name,

                        last_name:
                            form.last_name,

                        email:
                            form.email,

                        password:
                            form.password,

                        course:
                            form.course,

                        year_level:
                            form.year_level
                    })
                }
            );


            const data =
                await response.json();


            if (!data.success) {

                alert(data.message);

                return;
            }


            alert(
                "Account created successfully!"
            );

            navigate("/login");


        } catch (error) {

            console.error(error);

            alert(
                "Unable to connect to the server."
            );

        } finally {

            setLoading(false);

        }

    }


    return (

        <main className="register-account-page">

            <section className="register-account-container">

                <Link to="/" className="form-back-link">
                    <span aria-hidden="true">←</span>
                    Back to Home
                </Link>

                <div className="login-header">

                    <span>
                        UNIVERSITY EVENTS
                    </span>

                    <h1>
                        Create Account
                    </h1>

                    <p>
                        Create your student account
                        to register for university
                        events.
                    </p>

                </div>


                <form
                    className="login-form"
                    onSubmit={handleSubmit}
                >

                    <label>
                        Student ID
                    </label>

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


                    <label>
                        First Name
                    </label>

                    <input
                        name="first_name"
                        placeholder="First Name"
                        value={form.first_name}
                        onChange={handleChange}
                        required
                    />


                    <label>
                        Last Name
                    </label>

                    <input
                        name="last_name"
                        placeholder="Last Name"
                        value={form.last_name}
                        onChange={handleChange}
                        required
                    />


                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="student@example.com"
                        value={form.email}
                        onChange={handleChange}
                        required
                    />


                    <label>
                        Course / Program
                    </label>

                    <input
                        name="course"
                        placeholder="e.g. BSIT"
                        value={form.course}
                        onChange={handleChange}
                        required
                    />


                    <label>
                        Year Level
                    </label>

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


                    <label>
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="At least 8 characters"
                        value={form.password}
                        onChange={handleChange}
                        required
                    />


                    <label>
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        value={form.confirm_password}
                        onChange={handleChange}
                        required
                    />


                    <button
                        type="submit"
                        disabled={loading}
                    >

                        {loading
                            ? "Creating Account..."
                            : "Create Account"}

                    </button>

                </form>


                <div className="login-footer">

                    <p>
                        Already have an account?
                    </p>

                    <Link to="/login">
                        Sign In
                    </Link>

                </div>

            </section>

        </main>
    );
}

export default RegisterAccount;
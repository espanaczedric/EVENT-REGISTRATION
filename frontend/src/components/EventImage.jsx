import { useEffect, useState } from "react";

import { getEventImageUrl } from "../services/api";

function EventImage({ src, alt, className = "", loading = "lazy" }) {
    const imageUrl = getEventImageUrl(src);
    const [failed, setFailed] = useState(!imageUrl);

    useEffect(() => {
        setFailed(!imageUrl);
    }, [imageUrl]);

    if (failed) {
        return (
            <div
                className={`event-media-fallback ${className}`}
                role="img"
                aria-label={`${alt} artwork unavailable`}
            >
                <span aria-hidden="true">AU</span>
            </div>
        );
    }

    return (
        <img
            className={className}
            src={imageUrl}
            alt={alt}
            loading={loading}
            onError={() => setFailed(true)}
        />
    );
}

export default EventImage;

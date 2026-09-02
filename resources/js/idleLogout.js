import axios from "axios";
import { router } from "@inertiajs/vue3";

const LAST_ACTIVITY_KEY = "lifers:last-activity-at";
const HEARTBEAT_INTERVAL_MS = 4 * 60 * 1000;
const ACTIVITY_WRITE_INTERVAL_MS = 5000;
const IDLE_CHECK_INTERVAL_MS = 5000;

let installed = false;

export function installIdleLogout(initialPage) {
    if (installed || typeof window === "undefined") return;
    installed = true;

    const timeoutMinutes = Number(initialPage?.props?.security?.idle_timeout_minutes ?? 15);
    const timeoutMs = Math.max(1, timeoutMinutes) * 60 * 1000;
    let authenticated = Boolean(initialPage?.props?.auth?.user);
    let lastActivityAt = Date.now();
    let lastActivityWriteAt = 0;
    let lastHeartbeatAt = Date.now();
    let loggingOut = false;

    function storedActivityAt() {
        const value = Number(window.localStorage.getItem(LAST_ACTIVITY_KEY));
        return Number.isFinite(value) ? value : 0;
    }

    function writeActivity(now = Date.now()) {
        lastActivityAt = now;

        if (now - lastActivityWriteAt >= ACTIVITY_WRITE_INTERVAL_MS) {
            window.localStorage.setItem(LAST_ACTIVITY_KEY, String(now));
            lastActivityWriteAt = now;
        }
    }

    async function keepSessionAlive(now) {
        if (!authenticated || loggingOut || now - lastHeartbeatAt < HEARTBEAT_INTERVAL_MS) return;
        lastHeartbeatAt = now;

        try {
            await axios.post("/session/keep-alive", null, {
                headers: { Accept: "application/json" },
            });
        } catch (error) {
            if ([401, 419].includes(error.response?.status)) {
                authenticated = false;
                window.location.assign("/login");
            }
        }
    }

    function registerActivity() {
        const now = Date.now();
        writeActivity(now);
        void keepSessionAlive(now);
    }

    async function logoutForInactivity() {
        if (!authenticated || loggingOut) return;
        loggingOut = true;

        try {
            await axios.post("/logout", null, {
                headers: { Accept: "application/json" },
            });
        } catch {
            // La navigation vers la connexion reste nécessaire si la session
            // serveur avait déjà expiré avant l'appel de déconnexion.
        } finally {
            authenticated = false;
            window.localStorage.removeItem(LAST_ACTIVITY_KEY);
            window.location.assign("/login?inactive=1");
        }
    }

    const activityEvents = ["keydown", "pointerdown", "pointermove", "scroll", "touchstart"];
    activityEvents.forEach((eventName) => {
        window.addEventListener(eventName, registerActivity, { passive: true });
    });

    document.addEventListener("visibilitychange", () => {
        if (document.visibilityState === "visible") registerActivity();
    });

    window.addEventListener("storage", (event) => {
        if (event.key === LAST_ACTIVITY_KEY && event.newValue) {
            lastActivityAt = Math.max(lastActivityAt, Number(event.newValue) || 0);
        }
    });

    router.on("success", ({ detail }) => {
        const wasAuthenticated = authenticated;
        authenticated = Boolean(detail.page.props.auth?.user);

        if (authenticated && !wasAuthenticated) {
            loggingOut = false;
            lastHeartbeatAt = Date.now();
            writeActivity();
        }
    });

    if (authenticated) writeActivity(lastActivityAt);

    window.setInterval(() => {
        if (!authenticated || loggingOut) return;

        const mostRecentActivity = Math.max(lastActivityAt, storedActivityAt());
        if (Date.now() - mostRecentActivity >= timeoutMs) {
            void logoutForInactivity();
        }
    }, IDLE_CHECK_INTERVAL_MS);
}

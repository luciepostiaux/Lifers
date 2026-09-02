<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";

const props = defineProps({
    title: String,
    money: {
        type: [String, Number],
        default: null,
    },
});

const showingNavigation = ref(false);
const page = usePage();
const unreadPrivateMessagesCount = ref(
    Number(page.props.unreadPrivateMessagesCount ?? 0),
);
let privateMessageChannelName = null;

watch(
    () => page.props.unreadPrivateMessagesCount,
    (count) => {
        unreadPrivateMessagesCount.value = Number(count ?? 0);
    },
);

const hasUnreadPrivateMessages = computed(
    () => unreadPrivateMessagesCount.value > 0,
);

const initialNavigationPreference = () => {
    if (typeof window === "undefined") return true;

    try {
        return window.localStorage.getItem("lifers-navigation-pinned") !== "false";
    } catch {
        return true;
    }
};

const navigationPinned = ref(initialNavigationPreference());

const initialNavigationReveal = () => {
    if (typeof window === "undefined") return false;

    try {
        return (
            window.sessionStorage.getItem(
                "lifers-navigation-revealed-after-navigation",
            ) === "true"
        );
    } catch {
        return false;
    }
};

const navigationRevealed = ref(initialNavigationReveal());
const navigationTransitioning = ref(false);
const sidebarElement = ref(null);
const sidebarRevealElement = ref(null);

const navigationItems = computed(() => [
    {
        label: "Tableau de bord",
        route: "dashboard",
        active: ["dashboard"],
    },
    {
        label: "Mon Lifer",
        route: "profil",
        active: ["profil", "profil.*", "lifers.profile.*"],
    },
    {
        label: "Famille",
        route: "family.index",
        active: ["family.*"],
    },
    { label: "Chez moi", route: "athome", active: ["athome"] },
    {
        label: "Études",
        route: "study.index",
        active: ["study.*"],
    },
    { label: "Métier", route: "job", active: ["job", "job.*"] },
    {
        label: "Ville",
        route: "city",
        active: ["city", "city.*", "doctor.*"],
    },
    {
        label: "Communauté",
        route: "social",
        active: ["social"],
    },
    ...(page.props.permissions?.moderate
        ? [
              {
                  label: "Modération",
                  route: "moderation.dashboard",
                  active: ["moderation.*"],
              },
          ]
        : []),
    ...(page.props.permissions?.admin
        ? [
              {
                  label: "Administration",
                  route: "admin.dashboard",
                  active: ["admin.*"],
              },
          ]
        : []),
]);

const formattedDate = new Intl.DateTimeFormat("fr-FR", {
    weekday: "long",
    day: "numeric",
    month: "long",
}).format(new Date());

const formattedMoney = computed(() => {
    if (props.money === null || props.money === "") {
        return null;
    }

    const amount = Number(props.money);

    if (!Number.isFinite(amount)) {
        return null;
    }

    return new Intl.NumberFormat("fr-FR", {
        maximumFractionDigits: 2,
    }).format(amount);
});

const activeLiferName = computed(() =>
    [
        page.props.lifer?.first_name,
        page.props.lifer?.last_name,
    ].filter(Boolean).join(" ") || page.props.auth?.user?.name || "Compte Lifers",
);

const activeLiferInitials = computed(() =>
    activeLiferName.value
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join(""),
);

const isActive = (patterns) =>
    patterns.some((pattern) => route().current(pattern));

const closeNavigation = () => {
    showingNavigation.value = false;
};

const clearPreservedNavigationReveal = () => {
    try {
        window.sessionStorage.removeItem(
            "lifers-navigation-revealed-after-navigation",
        );
    } catch {
        // La navigation reste utilisable même si le stockage est indisponible.
    }
};

const revealNavigation = () => {
    if (!navigationPinned.value) {
        navigationRevealed.value = true;
    }
};

const hideNavigation = () => {
    if (!navigationPinned.value && !navigationTransitioning.value) {
        navigationRevealed.value = false;
        clearPreservedNavigationReveal();
    }
};

const toggleNavigationPin = (event) => {
    navigationPinned.value = !navigationPinned.value;

    if (navigationPinned.value) {
        navigationRevealed.value = false;
        clearPreservedNavigationReveal();
    } else {
        navigationRevealed.value = true;

        if (event.detail > 0) {
            event.currentTarget.blur();
        }
    }

    try {
        window.localStorage.setItem(
            "lifers-navigation-pinned",
            String(navigationPinned.value),
        );
    } catch {
        // La navigation reste utilisable même si le stockage est indisponible.
    }
};

const preserveNavigationReveal = () => {
    if (
        navigationPinned.value ||
        typeof window === "undefined" ||
        !window.matchMedia("(min-width: 1024px)").matches
    ) {
        return;
    }

    navigationRevealed.value = true;

    try {
        window.sessionStorage.setItem(
            "lifers-navigation-revealed-after-navigation",
            "true",
        );
    } catch {
        // La navigation restera au moins ouverte pendant la page actuelle.
    }
};

const handleNavigationSelection = () => {
    navigationTransitioning.value = true;
    preserveNavigationReveal();
    closeNavigation();
};

const handleSidebarFocusOut = (event) => {
    if (!event.currentTarget.contains(event.relatedTarget)) {
        hideNavigation();
    }
};

const handlePointerMove = (event) => {
    if (
        navigationPinned.value ||
        !navigationRevealed.value ||
        sidebarElement.value?.contains(event.target) ||
        sidebarRevealElement.value?.contains(event.target)
    ) {
        return;
    }

    hideNavigation();
};

const listenForPrivateMessages = () => {
    const userId = page.props.auth?.user?.id;

    if (!window.Echo || !userId) {
        return;
    }

    privateMessageChannelName = `App.Models.User.${userId}`;
    window.Echo.private(privateMessageChannelName).listen(
        "MessageSent",
        ({ message, conversation_type: conversationType }) => {
            const openedConversationId = Number(
                page.props.currentConversationId ?? 0,
            );

            if (
                conversationType !== "private" ||
                Number(message?.sender_lifer_id) === Number(page.props.lifer?.id) ||
                Number(message?.conversation_id) === openedConversationId
            ) {
                return;
            }

            unreadPrivateMessagesCount.value += 1;
        },
    );
};

onMounted(() => {
    window.addEventListener("pointermove", handlePointerMove, {
        passive: true,
    });
    listenForPrivateMessages();
});

onBeforeUnmount(() => {
    window.removeEventListener("pointermove", handlePointerMove);

    if (window.Echo && privateMessageChannelName) {
        window.Echo.leave(privateMessageChannelName);
    }
});

const switchToTeam = (team) => {
    navigationTransitioning.value = true;
    preserveNavigationReveal();

    router.put(
        route("current-team.update"),
        { team_id: team.id },
        { preserveState: false },
    );
};

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div
        class="game-layout"
        :class="{
            'game-layout--navigation-unpinned': !navigationPinned,
        }"
    >
        <Head :title="title">
            <meta head-key="robots" name="robots" content="noindex, nofollow" />
            <link rel="preconnect" href="https://fonts.bunny.net" />
            <link
                href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|dm-sans:400,500,600,700&display=swap"
                rel="stylesheet"
            />
        </Head>

        <Banner />

        <button
            v-if="showingNavigation"
            type="button"
            class="game-layout__overlay"
            aria-label="Fermer la navigation"
            @click="closeNavigation"
        ></button>

        <button
            v-if="!navigationPinned"
            ref="sidebarRevealElement"
            type="button"
            class="game-sidebar-reveal"
            aria-label="Afficher la navigation"
            aria-controls="game-navigation"
            @pointerenter="revealNavigation"
            @focus="revealNavigation"
        >
            <span aria-hidden="true">›</span>
        </button>

        <aside
            id="game-navigation"
            ref="sidebarElement"
            class="game-sidebar"
            :class="{
                'game-sidebar--open': showingNavigation,
                'game-sidebar--unpinned': !navigationPinned,
                'game-sidebar--revealed': navigationRevealed,
            }"
            aria-label="Navigation principale"
            @pointerenter="revealNavigation"
            @pointerleave="hideNavigation"
            @focusin="revealNavigation"
            @focusout="handleSidebarFocusOut"
        >
            <div class="game-sidebar__brand-row">
                <Link
                    :href="route('dashboard')"
                    class="game-sidebar__wordmark"
                    aria-label="Tableau de bord Lifers"
                    @click="handleNavigationSelection"
                >
                    LIFERS
                </Link>

                <div class="game-sidebar__brand-actions">
                    <button
                        type="button"
                        class="game-sidebar__pin"
                        :class="{
                            'game-sidebar__pin--unpinned': !navigationPinned,
                        }"
                        :aria-pressed="navigationPinned"
                        :aria-label="
                            navigationPinned
                                ? 'Décrocher la navigation'
                                : 'Épingler la navigation'
                        "
                        :title="
                            navigationPinned
                                ? 'Décrocher la navigation'
                                : 'Épingler la navigation'
                        "
                        @click="toggleNavigationPin"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path d="M7 3h10" />
                            <path d="M8 3v6l-3 4h14l-3-4V3" />
                            <path d="M12 13v8" />
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="game-sidebar__close"
                        aria-label="Fermer la navigation"
                        @click="closeNavigation"
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
            </div>

            <nav class="game-sidebar__navigation">
                <Link
                    v-for="item in navigationItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="game-sidebar__link"
                    :class="{
                        'game-sidebar__link--active': isActive(item.active),
                    }"
                    :aria-current="isActive(item.active) ? 'page' : undefined"
                    @click="handleNavigationSelection"
                >
                    <span class="game-sidebar__marker" aria-hidden="true"></span>
                    <span>{{ item.label }}</span>
                    <span
                        v-if="item.route === 'social' && hasUnreadPrivateMessages"
                        class="game-sidebar__alert"
                        :aria-label="`${unreadPrivateMessagesCount} message${unreadPrivateMessagesCount > 1 ? 's' : ''} privé${unreadPrivateMessagesCount > 1 ? 's' : ''} non lu${unreadPrivateMessagesCount > 1 ? 's' : ''}`"
                        role="status"
                    >
                        <span aria-hidden="true"></span>
                    </span>
                </Link>
            </nav>

            <div class="game-sidebar__account">
                <div class="game-sidebar__identity">
                    <span class="game-sidebar__avatar" aria-hidden="true">
                        {{ activeLiferInitials }}
                    </span>
                    <span class="game-sidebar__identity-copy">
                        <strong>{{ activeLiferName }}</strong>
                        <small>Lifer actif</small>
                    </span>
                </div>

                <Link
                    :href="route('profile.show')"
                    class="game-sidebar__account-link"
                    @click="handleNavigationSelection"
                >
                    Paramètres du compte
                </Link>

                <div class="game-sidebar__legal-links">
                    <Link :href="route('terms.show')">
                        Conditions
                    </Link>
                    <Link :href="route('policy.show')">
                        Confidentialité
                    </Link>
                </div>

                <Link
                    v-if="$page.props.jetstream.hasApiFeatures"
                    :href="route('api-tokens.index')"
                    class="game-sidebar__account-link"
                    @click="handleNavigationSelection"
                >
                    Jetons API
                </Link>

                <details
                    v-if="$page.props.jetstream.hasTeamFeatures"
                    class="game-sidebar__teams"
                >
                    <summary>Équipe</summary>
                    <div class="game-sidebar__team-list">
                        <Link
                            :href="
                                route(
                                    'teams.show',
                                    $page.props.auth.user.current_team,
                                )
                            "
                            class="game-sidebar__account-link"
                            @click="handleNavigationSelection"
                        >
                            Gérer l’équipe
                        </Link>
                        <button
                            v-for="team in $page.props.auth.user.all_teams"
                            :key="team.id"
                            type="button"
                            class="game-sidebar__account-link game-sidebar__team-button"
                            @click="switchToTeam(team)"
                        >
                            {{ team.name }}
                        </button>
                    </div>
                </details>

                <button
                    type="button"
                    class="game-sidebar__logout"
                    @click="logout"
                >
                    Se déconnecter
                </button>
            </div>
        </aside>

        <div class="game-workspace">
            <header class="game-topbar">
                <button
                    type="button"
                    class="game-topbar__menu"
                    :aria-expanded="showingNavigation"
                    aria-controls="game-navigation"
                    aria-label="Ouvrir la navigation"
                    @click="showingNavigation = true"
                >
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </button>

                <Link
                    :href="route('dashboard')"
                    class="game-topbar__wordmark"
                    aria-label="Tableau de bord Lifers"
                >
                    LIFERS
                </Link>

                <div class="game-topbar__page-title">
                    {{ title }}
                </div>

                <div class="game-topbar__utilities">
                    <time class="game-topbar__date">{{ formattedDate }}</time>
                    <span
                        v-if="formattedMoney !== null"
                        class="game-topbar__money"
                    >
                        <strong>{{ formattedMoney }}</strong>
                        <span>Lif’coins</span>
                    </span>
                </div>
            </header>

            <div v-if="$slots.header" class="game-page-heading">
                <slot name="header" />
            </div>

            <main class="game-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.game-layout {
    min-width: 320px;
    min-height: 100svh;
    color: #46324e;
    background: #f4eee5;
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
}

.game-layout ::selection {
    color: #46324e;
    background: #e8ca8a;
}

.game-sidebar {
    position: fixed;
    z-index: 40;
    inset: 0 auto 0 0;
    display: flex;
    width: 252px;
    min-height: 100svh;
    padding: 26px 18px 20px;
    flex-direction: column;
    color: #f8f3ec;
    background: #46324e;
    box-shadow: 8px 0 30px rgb(47 32 53 / 12%);
    overflow-y: auto;
    transition: transform 220ms ease;
}

.game-sidebar__brand-row {
    display: flex;
    min-height: 52px;
    padding-inline: 10px;
    align-items: center;
    justify-content: space-between;
}

.game-sidebar__wordmark,
.game-topbar__wordmark {
    display: inline-flex;
    min-height: 44px;
    align-items: center;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-weight: 800;
    line-height: 0.9;
    letter-spacing: -0.03em;
    text-decoration: none;
}

.game-sidebar__wordmark {
    color: #f8f3ec;
    font-size: 42px;
}

.game-sidebar__brand-actions {
    display: flex;
    align-items: center;
    gap: 4px;
}

.game-sidebar__pin,
.game-sidebar__close {
    display: inline-flex;
    width: 44px;
    height: 44px;
    border: 0;
    border-radius: 12px;
    align-items: center;
    justify-content: center;
    color: #f8f3ec;
    background: transparent;
    font-size: 34px;
    line-height: 1;
    cursor: pointer;
}

.game-sidebar__pin svg {
    width: 19px;
    height: 19px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
    transition: transform 180ms ease;
}

.game-sidebar__pin:hover,
.game-sidebar__close:hover {
    background: rgb(248 243 236 / 9%);
}

.game-sidebar__pin--unpinned svg {
    transform: rotate(-32deg);
}

.game-sidebar__close {
    display: none;
    font-size: 34px;
}

.game-sidebar-reveal {
    display: none;
}

.game-sidebar__navigation {
    display: grid;
    margin-top: 38px;
    gap: 8px;
}

.game-sidebar__link {
    position: relative;
    display: flex;
    min-height: 48px;
    padding: 11px 14px;
    border-radius: 12px;
    align-items: center;
    gap: 12px;
    color: rgb(248 243 236 / 78%);
    font-size: 15px;
    font-weight: 600;
    line-height: 1.2;
    text-decoration: none;
    transition:
        color 180ms ease,
        background-color 180ms ease,
        transform 180ms ease;
}

.game-sidebar__marker {
    width: 7px;
    height: 7px;
    flex: 0 0 7px;
    border-radius: 999px;
    background: rgb(248 243 236 / 28%);
    transition: background-color 180ms ease;
}

.game-sidebar__alert {
    display: inline-flex;
    width: 18px;
    height: 18px;
    margin-left: auto;
    flex: 0 0 18px;
    align-items: center;
    justify-content: center;
}

.game-sidebar__alert > span {
    width: 9px;
    height: 9px;
    border: 2px solid #46324e;
    border-radius: 999px;
    background: #ef9dad;
    box-shadow: 0 0 0 2px rgb(248 243 236 / 22%);
}

.game-sidebar__link:not(.game-sidebar__link--active) .game-sidebar__alert > span {
    border-color: #f8f3ec;
}

.game-sidebar__link:hover {
    color: #f8f3ec;
    background: rgb(248 243 236 / 8%);
    transform: translateX(2px);
}

.game-sidebar__link--active {
    color: #46324e;
    background: #d6a84a;
    box-shadow: 0 8px 18px rgb(27 17 31 / 20%);
}

.game-sidebar__link--active .game-sidebar__marker {
    background: #46324e;
}

.game-sidebar__account {
    display: grid;
    margin-top: auto;
    padding: 18px 10px 0;
    border-top: 1px solid rgb(248 243 236 / 13%);
    gap: 8px;
}

.game-sidebar__identity {
    display: flex;
    margin-bottom: 6px;
    align-items: center;
    gap: 10px;
}

.game-sidebar__avatar {
    display: inline-flex;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    color: #46324e;
    background: #d6a84a;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: 18px;
    font-weight: 800;
}

.game-sidebar__identity-copy {
    display: grid;
    min-width: 0;
    gap: 2px;
}

.game-sidebar__identity-copy strong,
.game-sidebar__identity-copy small {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.game-sidebar__identity-copy strong {
    color: #f8f3ec;
    font-size: 14px;
}

.game-sidebar__identity-copy small {
    color: rgb(248 243 236 / 62%);
    font-size: 11px;
}

.game-sidebar__account-link,
.game-sidebar__logout,
.game-sidebar__teams summary {
    display: flex;
    min-height: 38px;
    padding: 8px 10px;
    border: 0;
    border-radius: 9px;
    align-items: center;
    color: rgb(248 243 236 / 76%);
    background: transparent;
    font: inherit;
    font-size: 12px;
    font-weight: 600;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
}

.game-sidebar__account-link:hover,
.game-sidebar__logout:hover,
.game-sidebar__teams summary:hover {
    color: #f8f3ec;
    background: rgb(248 243 236 / 8%);
}

.game-sidebar__legal-links {
    display: flex;
    padding: 1px 10px 3px;
    flex-wrap: wrap;
    gap: 6px 12px;
}

.game-sidebar__legal-links a {
    color: rgb(248 243 236 / 58%);
    font-size: 10px;
    font-weight: 600;
    text-decoration-color: rgb(214 168 74 / 70%);
    text-underline-offset: 3px;
}

.game-sidebar__legal-links a:hover {
    color: #f8f3ec;
}

.game-sidebar__logout {
    width: 100%;
    color: #efbdc6;
}

.game-sidebar__teams summary {
    list-style: none;
}

.game-sidebar__teams summary::-webkit-details-marker {
    display: none;
}

.game-sidebar__team-list {
    display: grid;
    padding-left: 8px;
    gap: 2px;
}

.game-sidebar__team-button {
    width: 100%;
}

.game-workspace {
    min-height: 100svh;
    margin-left: 252px;
    transition: margin-left 220ms ease;
}

.game-topbar {
    position: sticky;
    z-index: 25;
    top: 0;
    display: flex;
    min-height: 68px;
    padding: 10px clamp(24px, 3vw, 44px);
    border-bottom: 1px solid rgb(70 50 78 / 6%);
    align-items: center;
    gap: 20px;
    background: rgb(248 243 236 / 94%);
    box-shadow: 0 7px 20px rgb(70 50 78 / 5%);
    backdrop-filter: blur(12px);
}

.game-topbar__menu,
.game-topbar__wordmark {
    display: none;
}

.game-topbar__page-title {
    color: #46324e;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
}

.game-topbar__utilities {
    display: flex;
    margin-left: auto;
    align-items: center;
    gap: 22px;
}

.game-topbar__date {
    color: rgb(70 50 78 / 72%);
    font-size: 14px;
    font-weight: 600;
    text-transform: capitalize;
}

.game-topbar__money {
    display: inline-flex;
    min-height: 40px;
    padding: 7px 13px;
    border: 1px solid rgb(214 168 74 / 34%);
    border-radius: 11px;
    align-items: baseline;
    gap: 5px;
    color: #46324e;
    background: rgb(214 168 74 / 14%);
}

.game-topbar__money strong {
    font-size: 16px;
}

.game-topbar__money span {
    font-size: 12px;
    font-weight: 600;
}

.game-page-heading {
    padding: 24px clamp(24px, 3vw, 44px) 0;
}

.game-content {
    min-width: 0;
}

.game-layout__overlay {
    display: none;
}

.game-sidebar__link:focus-visible,
.game-sidebar__wordmark:focus-visible,
.game-sidebar__account-link:focus-visible,
.game-sidebar__logout:focus-visible,
.game-sidebar__close:focus-visible,
.game-sidebar__pin:focus-visible,
.game-sidebar-reveal:focus-visible,
.game-topbar__menu:focus-visible,
.game-topbar__wordmark:focus-visible {
    outline: 3px solid #d6a84a;
    outline-offset: 3px;
}

@media (min-width: 1024px) {
    .game-sidebar-reveal {
        position: fixed;
        z-index: 38;
        top: 50%;
        left: 0;
        display: inline-flex;
        width: 24px;
        height: 72px;
        padding: 0;
        border: 1px solid rgb(248 243 236 / 16%);
        border-left: 0;
        border-radius: 0 12px 12px 0;
        align-items: center;
        justify-content: center;
        color: #f8f3ec;
        background: #46324e;
        box-shadow: 6px 0 18px rgb(47 32 53 / 18%);
        font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
        font-size: 24px;
        transform: translateY(-50%);
        cursor: pointer;
        transition:
            width 180ms ease,
            background-color 180ms ease;
    }

    .game-sidebar-reveal:hover,
    .game-sidebar-reveal:focus-visible {
        width: 30px;
        background: #533c5c;
    }

    .game-sidebar--unpinned {
        transform: translateX(calc(-100% - 12px));
    }

    .game-sidebar-reveal:hover + .game-sidebar,
    .game-sidebar-reveal:focus + .game-sidebar,
    .game-sidebar--unpinned.game-sidebar--revealed,
    .game-sidebar--unpinned:hover,
    .game-sidebar--unpinned:focus-within {
        transform: translateX(0);
    }

    .game-layout--navigation-unpinned .game-workspace {
        margin-left: 0;
    }
}

@media (max-width: 1023px) {
    .game-sidebar {
        width: min(86vw, 300px);
        transform: translateX(-105%);
        transition: transform 220ms ease;
    }

    .game-sidebar--open {
        transform: translateX(0);
    }

    .game-sidebar__close {
        display: inline-flex;
    }

    .game-sidebar__pin {
        display: none;
    }

    .game-workspace {
        margin-left: 0;
    }

    .game-layout__overlay {
        position: fixed;
        z-index: 35;
        display: block;
        inset: 0;
        width: 100%;
        border: 0;
        background: rgb(47 32 53 / 48%);
        backdrop-filter: blur(2px);
        cursor: pointer;
    }

    .game-topbar {
        min-height: 68px;
        padding-inline: clamp(18px, 4vw, 28px);
    }

    .game-topbar__menu {
        display: inline-grid;
        width: 44px;
        height: 44px;
        padding: 11px 9px;
        border: 0;
        border-radius: 11px;
        align-content: center;
        gap: 5px;
        color: #46324e;
        background: rgb(70 50 78 / 7%);
        cursor: pointer;
    }

    .game-topbar__menu span {
        display: block;
        width: 22px;
        height: 2px;
        border-radius: 99px;
        background: currentColor;
    }

    .game-topbar__wordmark {
        color: #46324e;
        font-size: 32px;
    }

    .game-topbar__page-title {
        display: none;
    }
}

@media (max-width: 639px) {
    .game-topbar {
        gap: 12px;
    }

    .game-topbar__date {
        display: none;
    }

    .game-topbar__utilities {
        gap: 8px;
    }

    .game-topbar__money {
        min-height: 38px;
        padding: 6px 10px;
    }

    .game-topbar__money span {
        display: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .game-sidebar,
    .game-sidebar__link,
    .game-sidebar__pin svg,
    .game-sidebar-reveal,
    .game-workspace {
        transition: none;
    }

    .game-sidebar__link:hover {
        transform: none;
    }
}
</style>

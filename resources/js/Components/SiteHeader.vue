<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
    showPublicNavigation: {
        type: Boolean,
        default: false,
    },
});

const menuOpen = ref(false);

const publicLinks = [
    { label: "Le jeu", href: "#le-jeu" },
    { label: "Ta vie, ton choix", href: "#seconde-vie" },
    { label: "La ville", href: "#ville" },
    { label: "La communauté", href: "#communaute" },
    { label: "Actualités", href: "#actualites" },
];
</script>

<template>
    <header class="site-header" @keydown.esc="menuOpen = false">
        <div class="site-header__inner">
            <Link
                :href="route('home')"
                class="site-header__wordmark site-header__focus-ring"
                aria-label="Accueil Lifers"
            >
                LIFERS
            </Link>

            <nav v-if="showPublicNavigation" class="site-header__public-navigation" aria-label="Découvrir Lifers">
                <a
                    v-for="link in publicLinks"
                    :key="link.href"
                    :href="link.href"
                    class="site-header__public-link site-header__focus-ring"
                >
                    {{ link.label }}
                </a>
            </nav>

            <div class="site-header__utilities">
                <button
                    v-if="showPublicNavigation"
                    class="site-header__menu-toggle site-header__focus-ring"
                    type="button"
                    :aria-expanded="menuOpen"
                    aria-controls="public-navigation-menu"
                    :aria-label="menuOpen ? 'Fermer le menu' : 'Ouvrir le menu'"
                    @click="menuOpen = !menuOpen"
                >
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </button>

                <nav
                    v-if="canLogin"
                    class="site-header__navigation"
                    aria-label="Accès au compte"
                >
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="site-header__primary-action site-header__focus-ring"
                    >
                        Tableau de bord
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="site-header__login-link site-header__focus-ring"
                        >
                            Se connecter
                        </Link>
                        <Link
                            v-if="canRegister && route().has('register')"
                            :href="route('register')"
                            class="site-header__primary-action site-header__primary-action--register site-header__focus-ring"
                        >
                            Créer mon Lifer
                        </Link>
                    </template>
                </nav>
            </div>
        </div>

        <nav
            v-if="showPublicNavigation && menuOpen"
            id="public-navigation-menu"
            class="site-header__mobile-navigation"
            aria-label="Découvrir Lifers sur mobile"
        >
            <a
                v-for="link in publicLinks"
                :key="`mobile-${link.href}`"
                :href="link.href"
                class="site-header__mobile-link site-header__focus-ring"
                @click="menuOpen = false"
            >
                {{ link.label }}
            </a>
        </nav>
    </header>
</template>

<style scoped>
.site-header {
    position: relative;
    z-index: 10;
    width: 100%;
    flex: 0 0 auto;
    color: #46324e;
    background: #f4eee5;
    box-shadow:
        0 1px 0 rgb(70 50 78 / 4%),
        0 7px 20px rgb(70 50 78 / 7%);
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
}

.site-header__inner {
    position: relative;
    z-index: 1;
    display: flex;
    width: min(100%, 1696px);
    min-height: 84px;
    margin-inline: auto;
    padding-inline: clamp(40px, 3.2vw, 64px);
    align-items: center;
    justify-content: space-between;
    gap: 32px;
}

.site-header__wordmark {
    display: inline-flex;
    min-height: 44px;
    align-items: center;
    color: #46324e;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(52px, 3.45vw, 58px);
    font-weight: 800;
    line-height: 0.9;
    letter-spacing: -0.03em;
    text-decoration: none;
}

.site-header__navigation {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: clamp(28px, 2vw, 32px);
}

.site-header__public-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(14px, 1.65vw, 30px);
}

.site-header__public-link {
    display: inline-flex;
    min-height: 44px;
    padding: 8px 1px;
    align-items: center;
    color: #46324e;
    font-size: clamp(14px, 1vw, 16px);
    font-weight: 600;
    white-space: nowrap;
    text-decoration-line: underline;
    text-decoration-color: transparent;
    text-decoration-thickness: 3px;
    text-underline-offset: 7px;
    transition: text-decoration-color 180ms ease;
}

.site-header__public-link:hover {
    text-decoration-color: #d6a84a;
}

.site-header__utilities {
    display: flex;
    align-items: center;
    gap: 14px;
}

.site-header__menu-toggle,
.site-header__mobile-navigation {
    display: none;
}

.site-header__login-link,
.site-header__primary-action {
    display: inline-flex;
    min-height: 44px;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition:
        color 180ms ease,
        box-shadow 180ms ease,
        transform 180ms ease,
        text-decoration-color 180ms ease;
}

.site-header__login-link {
    padding: 8px 2px;
    color: #46324e;
    font-size: 17px;
    font-weight: 600;
    text-decoration-line: underline;
    text-decoration-color: transparent;
    text-decoration-thickness: 3px;
    text-underline-offset: 7px;
}

.site-header__login-link:hover {
    text-decoration-color: #d6a84a;
}

.site-header__primary-action {
    min-width: 188px;
    min-height: 56px;
    padding: 14px 28px;
    border-radius: 13px;
    color: #46324e;
    background: #d6a84a;
    box-shadow: 0 8px 20px rgb(70 50 78 / 12%);
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
    text-align: center;
}

.site-header__primary-action:hover {
    box-shadow: 0 12px 26px rgb(70 50 78 / 20%);
    transform: translateY(-1px);
}

.site-header__primary-action:active {
    box-shadow: 0 5px 14px rgb(70 50 78 / 14%);
    transform: translateY(0);
}

.site-header__focus-ring:focus-visible {
    outline: 3px solid #46324e;
    outline-offset: 4px;
    box-shadow: 0 0 0 7px rgb(244 238 229 / 90%);
}

@media (max-width: 1279px) {
    .site-header__inner {
        min-height: 76px;
        padding-inline: clamp(28px, 3.5vw, 36px);
    }

    .site-header__wordmark {
        font-size: clamp(44px, 4.2vw, 48px);
    }
}

@media (max-width: 1399px) {
    .site-header__public-navigation {
        display: none;
    }

    .site-header__menu-toggle {
        display: grid;
        width: 46px;
        height: 46px;
        padding: 11px;
        border: 1px solid rgb(70 50 78 / 14%);
        border-radius: 12px;
        place-content: center;
        gap: 5px;
        color: #46324e;
        background: rgb(255 250 244 / 72%);
        cursor: pointer;
    }

    .site-header__menu-toggle span {
        display: block;
        width: 21px;
        height: 2px;
        border-radius: 999px;
        background: currentcolor;
    }

    .site-header__mobile-navigation {
        position: absolute;
        z-index: 2;
        top: 100%;
        right: clamp(28px, 3.5vw, 36px);
        display: grid;
        width: min(320px, calc(100% - 56px));
        padding: 10px;
        border: 1px solid rgb(70 50 78 / 10%);
        border-radius: 0 0 18px 18px;
        gap: 2px;
        background: #faf6ef;
        box-shadow: 0 18px 34px rgb(70 50 78 / 14%);
    }

    .site-header__mobile-link {
        display: flex;
        min-height: 46px;
        padding: 10px 14px;
        border-radius: 10px;
        align-items: center;
        color: #46324e;
        font-weight: 700;
        text-decoration: none;
    }

    .site-header__mobile-link:hover {
        background: #f3e7c8;
    }
}

@media (max-width: 767px) {
    .site-header__inner {
        min-height: 68px;
        padding-inline: clamp(18px, 5vw, 28px);
        gap: 18px;
    }

    .site-header__wordmark {
        font-size: clamp(36px, 11vw, 40px);
    }

    .site-header__navigation {
        gap: 14px;
    }

    .site-header__mobile-navigation {
        right: clamp(18px, 5vw, 28px);
        width: min(320px, calc(100% - 36px));
    }

    .site-header__login-link {
        font-size: 15px;
    }

    .site-header__primary-action--register {
        display: none;
    }

    .site-header__navigation > .site-header__primary-action:not(.site-header__primary-action--register) {
        min-width: 0;
        min-height: 46px;
        padding: 11px 15px;
        font-size: 14px;
    }
}

@media (max-width: 359px) {
    .site-header__inner {
        padding-inline: 16px;
    }

    .site-header__wordmark {
        font-size: 36px;
    }

    .site-header__login-link {
        font-size: 14px;
    }

    .site-header__utilities {
        gap: 8px;
    }

    .site-header__menu-toggle {
        width: 44px;
        height: 44px;
    }

    .site-header__mobile-navigation {
        right: 16px;
        width: calc(100% - 32px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .site-header__login-link,
    .site-header__primary-action {
        transition: none;
    }

    .site-header__primary-action:hover,
    .site-header__primary-action:active {
        transform: none;
    }
}
</style>

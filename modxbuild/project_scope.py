"""Project-owned MODX elements, files and content scope for deploy/withdraw."""

CHUNKS = {
    1: "head",
    2: "topbarLogo",
    3: "footer",
    4: "mapBlock",
    5: "copyright",
    6: "tail",
    7: "slider",
    8: "searchBar",
    9: "pageFooterBands",
}

SNIPPETS = {
    1: "spMenu",
    2: "pageTitle",
    3: "hotVacancies",
    4: "jobList",
    5: "newsList",
    6: "jobDetail",
    7: "newsDetail",
    8: "newsPageType",
    9: "aboutContent",
    10: "pageProse",
    11: "trainingsContent",
    12: "contactsContent",
}

TEMPLATES = {
    2: "base",
    3: "vacancy",
    4: "blog",
    5: "news",
}

PLUGINS = ("newsDefaults", "vacancyRouter")

TEMPLATE_NAMES = tuple(TEMPLATES.values())

TV_NAMES = (
    "title_mode",
    "show_search",
    "cssClass",
    "vac_city",
    "vac_category",
    "vac_contract",
    "vac_start",
    "vac_salary",
    "vac_tasks",
    "vac_profile",
    "vac_perspective",
    "vac_contacts",
    "vac_homepage",
)

TOP_LEVEL_ALIASES = {
    "home",
    "about",
    "contacts",
    "vacancy",
    "blog",
    "trainings-and-webinars",
    "zapros-na-podbor-personala",
    "politika-obrabotki-personalnykh-dannykh-polzovatelej-sajta",
    "soglashenie-ob-okazanii-uslug-po-ispolzovaniyu-sajta",
}

SECTION_ROOT_ALIASES = {"home", "vacancy", "blog", "trainings-and-webinars"}

TRACKED_FILES = [
    "templates/jd_consult/css/theme-modern.css",
    "templates/jd_consult/css/responsive.css",
    "templates/jd_consult/css/cookie-consent.css",
    "templates/jd_consult/css/presets/preset1.css",
    "templates/jd_consult/js/cookie-consent.js",
    "templates/jd_consult/js/theme-animations.js",
    "assets/about/about-content.html",
    "assets/trainings/trainings-content.html",
    "assets/contacts/contacts-content.html",
]

THEME_PREFIXES = (
    "templates/jd_consult/css/",
    "templates/jd_consult/js/",
    "assets/about/",
    "assets/trainings/",
    "assets/contacts/",
)

RESOURCE_SCAN_MAX_ID = 1500
CUSTOM_DB_TABLES = ("triza_vacancies", "triza_categories")

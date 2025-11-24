<style>
    *:not(:defined) {
        display: none;
    }

    :root {
        --gray-0: whitesmoke;
        --gray-1: #dedede;
        --gray-2: #a9a9c1;
        --info-0: #3a3fd7;
        --primary-0: #363a9a;
        --primary-1: #17b8b3;
        --cyan-0: #17b8b3;
        --accent-0: yellow;
        --accent-1: #ffc800;
        --danger: #e93e3e;
        --danger-1: #c61212;
        --success-0: #56ae56;
        --warning-0: #ffdd9e;
        --warning-1: #ebab34;
        --warning-2: #da9005;
        --dark: #1b2740;
        --light: white;
        --header-bg: var(--primary-0);
        --container-max-width: 55rem;
        --breakpoint: 70rem;
        --dashboard-sidebar-width: 14rem;
        --available-dashboard-sidebar-height: 100vh;

        --dashboard-sidebar-background-color: var(--primary-0);

        --qrcg-font-family: system-ui, -apple-system, Segoe UI, Roboto,
            Helvetica, Arial, sans-serif, Apple Color Emoji,
            Segoe UI Emoji;

        --qrcg-rtl-font-family: 'Segoe UI', Tahoma, Geneva, Verdana,
            sans-serif;
    }

    body.dashboard-page {
        font-family: var(--qrcg-font-family);
        opacity: 0;
    }

    body.resolved.dashboard-page {
        animation: body-fade-in 0.5s 0.1s ease-in both;
    }

    @keyframes body-fade-in {
        from {
            opacity: 0.0000001;
        }

        to {
            opacity: 100%;
        }
    }
</style>

@if (!empty(config('dashboard.header_height')))
    <style>
        :root {
            --settings-dashboard-header-height: {{ config('dashboard.header_height') }}rem;
        }
    </style>
@endif

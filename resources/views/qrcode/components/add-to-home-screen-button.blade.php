@php
    $bg = $composer->designValue('add_to_home_screen_button_background_color', null);
    $hoverBg = $composer->designValue('add_to_home_screen_button_hover_background_color', null);
    $color = $composer->designValue('add_to_home_screen_button_text_color', null);
    $text = $composer->designValue('add_to_home_screen_button_text', null);
@endphp

<script>
    if ('serviceWorker' in navigator) {
        console.log("Will the service worker register?");
        navigator.serviceWorker.register('/assets/lib/sw.js?v=1')
            .then(function(reg) {
                console.log("Service worker is registered.");
            }).catch(function(err) {
                console.log("Service worker is not registerd :", err)
            });
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        // Prevent the mini-infobar from appearing on mobile.
        event.preventDefault();

        // Stash the event so it can be triggered later.
        window.deferredPrompt = event;
        // Remove the 'hidden' class from the install button container.
        showAddToHomeScreenButtonIfFound()
    });


    async function showAddToHomeScreenButtonIfFound() {
        await new Promise(r => setTimeout(r, 300))

        const button = document.querySelector('.add-to-home-screen')


        if (!button) {
            return;
        }

        button.setAttribute('style', "")

        button.addEventListener('click', async () => {

            const promptEvent = window.deferredPrompt;

            if (!promptEvent) {
                // The deferred prompt isn't available.
                return;
            }
            // Show the install prompt.
            promptEvent.prompt();
            // Log the result
            const result = await promptEvent.userChoice;

            // Reset the deferred prompt variable, since
            // prompt() can only be called once.
            window.deferredPrompt = null;

        });
    }
</script>

@if (!empty($bg))
    <style>
        .button.add-to-home-screen {
            background-color: {{ $bg }};
        }
    </style>
@endif

@if (!empty($hoverBg))
    <style>
        .button.add-to-home-screen:hover {
            background-color: {{ $hoverBg }};
        }
    </style>
@endif

@if (!empty($color))
    <style>
        .button.add-to-home-screen {
            color: {{ $color }};
        }
    </style>
@endif

<style>
    .add-to-home-screen {
        display: flex;
        width: 100%;
        margin: 1rem 0 2rem;
    }
</style>

@if ($composer->designValue('add_to_home_screen_button') === 'enabled')
    <button class="button add-to-home-screen primary" style="display: none">
        {{ $text ?? t('Add To Home Screen') }}
    </button>
@endif

@extends('qrcode.pages.layout')

@section('page')
    <div class="layout-generated-webpage safe-browsing-redirect">
        @include('blue.components.logo', ['inverse' => true])

        <p>{{ t('You are about to leave our site and go to:') }}</p>

        <div class="destination">{{ $destination }}</div>

        <div class="actions">
            <div class=redirect-text>
                {{ t('Redirecting ...') }}
            </div>

            <div class="cancel" id="cancelBtn">{{ t('Cancel') }}</div>

            <a href="{{ $destination }}" class="button primary">
                {{ t('Continue') }}
            </a>
        </div>

        <footer>
            {{ config('app.name') }} &copy; {{ now()->format('Y') }}
        </footer>


        <script>
            const cancelBtn = document.getElementById('cancelBtn');

            cancelBtn.addEventListener('click', () => {
                window.history.back();
            });
        </script>
    </div>
@endsection

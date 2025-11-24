@extends('qrcode.types.layout')

@section('qrcode-layout-head')
    {!! $composer->script() !!}
@endsection

@section('page')

    @include('qrcode.components.splash-screen')

    <div class="layout-generated-webpage">
        @include('qrcode.components.banner-background')

        <div class="details-container">
            <div class="gradient-bg"></div>
            <div class="main-details">
                @if ($composer->shouldRenderQRCodeFrame())
                    <div class="qrcode-container">
                        @if ($composer->shouldRenderQRCode())
                            <img src="{{ $composer->qrcodeUrl() }}" />
                        @endif
                        @if ($composer->shouldRenderLogo())
                            <img src="{{ $composer->fileUrl('logo') }}" />
                        @endif
                    </div>
                @endif
                <div class="vertical-list">
                    <h1 class="name">
                        {!! $composer->qrcodeData('firstName', 'Lisa') !!}
                        {!! $composer->qrcodeData('lastName', 'D') !!}
                    </h1>
                    @if (!empty($composer->qrcodeData('job')))
                        <p class="designation">
                            {!! $composer->qrcodeData('job') !!}
                        </p>
                    @endif
                    @if (!empty($composer->qrcodeData('company')))
                        <p class="company">
                            {!! $composer->qrcodeData('company') !!}
                        </p>
                    @endif
                    @if (!empty($composer->qrcodeData('bio')))
                        <div class="short-bio">
                            {!! $composer->qrcodeData('bio') !!}
                        </div>
                    @endif
                </div>
            </div>

            @if ($composer->shouldShowContactIcons())
                <div class="social-icons">
                    @if (!empty($composer->getFirstPhone()))
                        <a href="tel:{{ $composer->getFirstPhone() }}">
                            @include('blue.components.icons.phone')
                        </a>
                    @endif

                    @if (!empty($composer->getFirstEmail()))
                        <a href="mailto:{{ $composer->getFirstEmail() }}">
                            @include('blue.components.icons.email')
                        </a>
                    @endif

                    @if (!empty($composer->qrcodeData('maps_url')))
                        <a href="{{ $composer->qrcodeData('maps_url', '#') }}" target="_blank">
                            @include('blue.components.icons.map-marker')
                        </a>
                    @endif

                    @if (!$composer->websites()->isEmpty())
                        <a href="{{ $composer->websites()[0] }}" target="_blank">
                            @include('blue.components.icons.web')
                        </a>
                    @endif

                    @if (!empty($composer->qrcodeData('whatsapp_number')))
                        <a href="{{ $composer->whatsappNumber() }}" target="_blank">
                            @include('blue.components.icons.social.whatsapp')
                        </a>
                    @endif
                </div>
            @endif

            @include('qrcode.components.add-to-home-screen-button')

            @if ($composer->designValue('social_icons_position') == 'below_contact_icons')
                <div class="social-icons">
                    @include('blue.components.social-links', ['urls' => $composer->qrcodeData('socialProfiles')])
                </div>
            @endif

            @include('qrcode.components.share-on-whatsapp', ['message' => $composer->getQRCode()->name])

            @include('qrcode.types.vcard-plus.add-to-contacts', ['position' => 'top-section'])

            @if (!empty($composer->customLinks()))
                <div class="custom-links">
                    @foreach ($composer->customLinks() as $link)
                        <a class="button custom-link {{ $link['id'] }}" href="{{ @$link['url'] }}" {!! $composer->customLinkTarget($link) !!}>
                            {{ @$link['text'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            {!! $composer->renderLeadForm() !!}

            @if ($composer->shouldShowContactDetails())
                <div class="white-card contacts-card">
                    <header>
                        @include('blue.components.icons.phone')
                        {{ t('Contacts') }}
                    </header>

                    <div class="body">

                        @foreach ($composer->contacts() as $item)
                            @if (!empty($item['value']))
                                <div class="vertical-item">
                                    <div class="key">
                                        {{ t($item['name']) }}
                                    </div>
                                    <div class="value">
                                        @if (empty($item['link']))
                                            {{ $item['value'] }}
                                        @else
                                            <a href="{{ $item['link'] }}">
                                                {{ $item['value'] }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!$composer->websites()->isEmpty())
                <div class="white-card">
                    <header>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <title>web</title>
                            <path
                                d="M16.36,14C16.44,13.34 16.5,12.68 16.5,12C16.5,11.32 16.44,10.66 16.36,10H19.74C19.9,10.64 20,11.31 20,12C20,12.69 19.9,13.36 19.74,14M14.59,19.56C15.19,18.45 15.65,17.25 15.97,16H18.92C17.96,17.65 16.43,18.93 14.59,19.56M14.34,14H9.66C9.56,13.34 9.5,12.68 9.5,12C9.5,11.32 9.56,10.65 9.66,10H14.34C14.43,10.65 14.5,11.32 14.5,12C14.5,12.68 14.43,13.34 14.34,14M12,19.96C11.17,18.76 10.5,17.43 10.09,16H13.91C13.5,17.43 12.83,18.76 12,19.96M8,8H5.08C6.03,6.34 7.57,5.06 9.4,4.44C8.8,5.55 8.35,6.75 8,8M5.08,16H8C8.35,17.25 8.8,18.45 9.4,19.56C7.57,18.93 6.03,17.65 5.08,16M4.26,14C4.1,13.36 4,12.69 4,12C4,11.31 4.1,10.64 4.26,10H7.64C7.56,10.66 7.5,11.32 7.5,12C7.5,12.68 7.56,13.34 7.64,14M12,4.03C12.83,5.23 13.5,6.57 13.91,8H10.09C10.5,6.57 11.17,5.23 12,4.03M18.92,8H15.97C15.65,6.75 15.19,5.55 14.59,4.44C16.43,5.07 17.96,6.34 18.92,8M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                        </svg>
                        {{ $composer->websites()->count() > 1 ? t('Websites') : t('Website') }}
                    </header>

                    <div class="body">
                        @foreach ($composer->websites() as $website)
                            <p>
                                <a href="{{ $website }}" target="_blank">
                                    {{ $website }}
                                </a>
                            </p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($composer->address()))
                <div class="white-card">
                    <header>
                        @include('blue.components.icons.map-marker')
                        {{ t('Address') }}
                    </header>

                    <div class="body">
                        <p>
                            @if ($composer->hasMapsUrl())
                                <a target="_blank" href="{{ $composer->qrcodeData('maps_url') }}">
                                    {{ $composer->address() }}
                                </a>
                            @else
                                {{ $composer->address() }}
                            @endif
                        </p>
                    </div>
                </div>
            @endif

            @if (!empty($composer->qrcodeData('second_address')))
                <div class="white-card">
                    <header>
                        @include('blue.components.icons.map-marker')
                        {{ t('Second Address') }}
                    </header>

                    <div class="body">
                        <p>
                            {!! $composer->qrcodeData('second_address') !!}
                        </p>
                    </div>
                </div>
            @endif

            @if ($composer->qrcodeData('openingHoursEnabled') == 'enabled')
                <div class="white-card">
                    <header>
                        @include('blue.components.icons.timer')
                        {{ t('Opening Hours') }}
                    </header>

                    <div class="body">
                        @include('qrcode.components.opening-hours', ['hours' => $composer->openingHours()])
                    </div>
                </div>
            @endif

            @if ($composer->designValue('social_icons_position') != 'below_contact_icons')
                <div class="social-icons">
                    @include('blue.components.social-links', ['urls' => $composer->qrcodeData('socialProfiles')])
                </div>
            @endif

            @if (!empty($composer->faqs()))
                @include('qrcode.components.faqs', ['title' => $composer->faqsMainTitle(), 'items' => $composer->faqs()])
            @endif


            @if ($composer->shouldRenderPortfolio())
                @include('qrcode.types.vcard-plus.portfolio')
            @endif

            @include('qrcode.types.vcard-plus.add-to-contacts', ['position' => 'bottom-section'])

            @if ($composer->designValue('custom_code_enabled') === 'enabled' && !empty($composer->designValue('custom_code')))
                {!! $composer->designValue('custom_code') !!}
            @endif

            @include('qrcode.components.share-button')

            @include('qrcode.components.additional-information-popup')

        </div>
    </div>

    @if ($composer->designValue('add_to_contacts_button_style') != 'classic')
        <button class="button circle add-contact floating" title="Add contact">
            <svg viewBox="0 0 488.03277 517.12427">
                <path
                    d="m 385.05518,360.93436 c -7.2349,0.10037 -13.85465,6.31238 -14.24372,13.5687 -0.22187,3.32435 -0.008,6.65798 -0.0903,9.98638 0,5.07408 0,10.14817 0,15.22226 -8.53734,0.0258 -17.07806,-0.0789 -25.61304,0.0972 -6.88367,0.65972 -12.77427,6.77179 -13.14545,13.684 -0.54438,6.53872 3.78293,13.16859 10.05927,15.16801 3.66617,1.21807 7.57551,0.66796 11.36289,0.78125 5.77877,0 11.55755,0 17.33633,0 0.0258,8.53668 -0.0789,17.07676 0.0972,25.61108 0.65784,6.88551 6.77296,12.7764 13.68605,13.14635 6.56036,0.54748 13.21624,-3.81282 15.18597,-10.12428 1.20084,-3.68519 0.64155,-7.61069 0.75929,-11.41284 0,-5.7401 0,-11.48021 0,-17.22031 8.53797,-0.0265 17.07938,0.0756 25.61498,-0.0973 6.86578,-0.66194 12.74365,-6.73916 13.14056,-13.63278 0.56214,-6.51349 -3.70372,-13.14071 -9.94482,-15.17827 -3.69305,-1.2805 -7.65515,-0.70605 -11.48025,-0.82214 -5.77682,0 -11.55365,0 -17.33047,0 -0.0264,-8.53473 0.0754,-17.07284 -0.0971,-25.60523 -0.65595,-6.95879 -6.89901,-12.9055 -13.89705,-13.15454 -0.4665,-0.0265 -0.93334,-0.0185 -1.40039,-0.0176 z M 217.02393,0.00467346 c -25.39437,0.100464 -50.67975,8.51445564 -71.05271,23.67889454 -20.4376,15.098037 -36.02998,36.717439 -43.63045,60.984834 -7.310848,23.129148 -7.475004,48.468898 -0.36668,71.668648 5.86052,19.26285 16.63478,36.99712 31.0297,51.0746 0.24951,0.34973 1.22279,0.84583 0.32104,0.94168 -34.425792,12.60645 -65.334124,34.70118 -88.313659,63.27663 -21.628117,26.82008 -36.2956091,59.21688 -42.0622197,93.19247 -2.24559113,13.05804 -3.12906423,26.33 -2.91889313,39.57286 0.029959,9.71874 -0.088535,19.44029 0.1086718,29.15704 0.89477523,16.06 8.90648793,31.5457 21.43682703,41.61882 10.49193,8.53926 24.030206,13.29042 37.570641,12.98391 31.70712,0.0419 63.414322,0.005 95.121482,0.0176 53.2944,0 106.5888,0 159.8832,0 16.87242,16.4535 39.58311,26.81931 63.0943,28.61918 21.00549,1.71103 42.52537,-3.21368 60.6082,-14.06555 19.24485,-11.45904 34.61992,-29.33487 42.887,-50.16733 7.84504,-19.60496 9.43282,-41.65543 4.41183,-62.17043 -3.97588,-16.44415 -12.1234,-31.85779 -23.4971,-44.38547 -11.66748,-12.91929 -26.63235,-22.87749 -43.13313,-28.45931 -14.76385,-33.75756 -38.96784,-63.29731 -69.09802,-84.49349 -14.66412,-10.3359 -30.6943,-18.73606 -47.55433,-24.86979 17.79932,-17.15913 30.24484,-39.85283 34.84631,-64.16605 4.94043,-25.57286 1.41786,-52.744911 -10.13606,-76.11028 C 314.97348,44.208211 295.43805,24.476884 271.80705,12.720134 254.88237,4.2628205 235.94857,-0.16450214 217.02393,0.00467346 Z m 0.78711,35.00976554 c 9.07533,0.0034 18.14482,1.454829 26.77148,4.271484 1.00059,0.336868 2.24659,0.76004 3.32775,1.164712 11.8987,4.420339 22.73402,11.594573 31.58241,20.682945 0.61712,0.644909 1.43596,1.495734 2.10333,2.245478 5.16446,5.67981 9.56544,12.055308 13.02558,18.908818 0.51355,1.014241 1.12085,2.269485 1.63024,3.394709 4.43247,9.806957 7.01391,20.451135 7.5221,31.202945 0.0264,0.65896 0.0618,1.48358 0.0735,2.19394 0.0704,2.70219 -0.002,5.43442 -0.18483,8.05606 -0.66928,9.50717 -2.94194,18.95686 -6.68554,27.68164 -1.82224,4.29 -4.08662,8.53831 -6.54297,12.39453 -4.34275,6.79558 -9.6325,12.98288 -15.66797,18.33203 -1.01272,0.88602 -2.21534,1.9103 -3.34026,2.80409 -2.70972,2.16054 -5.56853,4.177 -8.45781,5.96548 -13.70481,8.49013 -29.83506,13.01925 -45.96129,12.81213 -1.41684,-0.0134 -2.86078,-0.0479 -4.17981,-0.13574 -6.80882,-0.38687 -13.62286,-1.61082 -20.08572,-3.59094 -2.77818,-0.84732 -5.44668,-1.81239 -8.1548,-2.94877 -5.26762,-2.20274 -10.41273,-5.03258 -15.17729,-8.26851 -1.81836,-1.24575 -3.63399,-2.5855 -5.28095,-3.91466 -14.75389,-11.80071 -25.549,-28.52823 -29.94343,-46.92478 -3.57185,-14.64669 -3.18111,-30.23497 1.11065,-44.686966 0.33963,-1.141159 0.77046,-2.510092 1.19744,-3.722949 1.78202,-5.170745 4.1496,-10.283075 6.84944,-14.937207 7.82392,-13.487193 19.47404,-24.92353 33.22596,-32.42403 5.75374,-3.152749 11.8716,-5.636048 18.1881,-7.408002 0.58029,-0.158842 1.32752,-0.365407 1.9547,-0.518327 4.02377,-1.026866 8.1233,-1.753353 12.25429,-2.176985 1.06407,-0.101838 2.34989,-0.213054 3.45441,-0.279275 1.79442,-0.116723 3.59334,-0.161489 5.39129,-0.17385 z M 262.97901,233.7117 c 3.27641,0.60794 6.54521,1.2997 9.7196,2.08432 11.67109,2.85894 23.1987,7.04611 34.04115,12.40664 1.86078,0.91506 3.62827,1.82672 5.48865,2.82728 8.3425,4.50021 16.29488,9.71802 23.76427,15.55285 6.33844,4.96378 12.47452,10.48391 18.07318,16.36809 8.56692,8.97478 16.15272,18.88838 22.54596,29.52254 -21.68495,1.85651 -42.67786,10.89642 -58.9294,25.37351 -16.18317,14.31932 -27.76863,33.82364 -32.28519,54.97863 -4.36588,20.03726 -2.57505,41.38815 5.20678,60.37247 -77.16194,-0.004 -154.32405,0.009 -231.48587,-0.013 -3.815249,-0.008 -7.679141,-0.66134 -11.103687,-2.40537 -7.291746,-3.56353 -12.479685,-11.18128 -12.854949,-19.32263 -0.136401,-3.57447 -0.008,-7.15321 -0.05744,-10.72966 0.02551,-9.32696 -0.09061,-18.65659 0.131157,-27.98149 0.551192,-15.10374 3.239499,-30.11956 7.859077,-44.5068 3.063704,-9.52629 7.044908,-18.94772 11.815284,-27.82477 4.734486,-8.85659 10.321639,-17.40791 16.583825,-25.32853 11.187477,-14.18773 24.759436,-26.68837 39.903613,-36.66115 13.55317,-8.9441 28.54454,-15.96778 44.17756,-20.59354 5.69039,-1.70102 11.5259,-3.06165 17.41229,-4.16232 0.63101,-0.11725 1.18391,0.40103 1.79445,0.53269 25.20759,9.68768 53.70116,10.54024 79.43761,2.3537 2.78841,-0.87477 5.5436,-1.85588 8.26013,-2.93326 0.16731,0.0299 0.33463,0.0599 0.50195,0.0898 z m 122.59961,113.33008 c 2.0496,-0.001 3.94644,0.0885 5.96289,0.26367 1.34981,0.12305 2.92903,0.30726 4.35907,0.53561 12.33328,1.89931 24.09259,7.3177 33.54314,15.46758 10.33631,8.87887 17.98435,20.90359 21.41419,34.10814 0.5782,2.20083 1.08511,4.61557 1.43517,6.93786 0.18891,1.24281 0.34045,2.48899 0.44957,3.65148 0.35879,3.75688 0.41165,7.54432 0.14456,11.3091 -0.73288,11.03026 -4.34529,22.04886 -10.3457,31.42773 -8.47902,13.28758 -21.66711,23.54123 -36.73291,28.2422 -13.85319,4.41304 -29.3096,4.06519 -42.88233,-0.77931 -13.48073,-4.79855 -25.3169,-14.01568 -33.32817,-25.86628 -3.31503,-4.88579 -6.03515,-10.31924 -7.91987,-15.83684 -3.0749,-8.91459 -4.25913,-18.6375 -3.37679,-28.09561 0.32094,-3.75636 1.04724,-7.68803 1.97835,-11.18369 2.02573,-7.57601 5.3723,-14.79906 9.86133,-21.23047 1.179,-1.66693 2.53351,-3.45159 3.911,-5.06126 2.04007,-2.41287 4.31964,-4.73919 6.58609,-6.7576 12.0148,-10.70677 27.93623,-17.03504 44.06143,-17.11753 0.29299,-0.005 0.58598,-0.0108 0.87898,-0.0148 z" />
            </svg>
        </button>
    @endif

    @include('qrcode.components.automatic-form-popup')

@endsection

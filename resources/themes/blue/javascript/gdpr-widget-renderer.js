import { BaseRenderer } from './base-renderer'

class GdprWidgetRenderer extends BaseRenderer {
    get cookieWidget() {
        return document.querySelector('#cookie-consent')
    }

    //Helper Functions
    create_UUID() {
        var dt = new Date().getTime()
        var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(
            /[xy]/g,
            function (c) {
                var r = (dt + Math.random() * 16) % 16 | 0
                dt = Math.floor(dt / 16)
                return (c == 'x' ? r : (r & 0x3) | 0x8).toString(16)
            }
        )
        return uuid
    }

    setCookie(cname, cvalue, exdays) {
        var d = new Date()
        d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000)
        var expires = 'expires=' + d.toUTCString()
        document.cookie = cname + '=' + cvalue + ';' + expires + ';path=/'
    }

    getCookie(cname) {
        var name = cname + '='
        var ca = document.cookie.split(';')
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i]
            while (c.charAt(0) == ' ') {
                c = c.substring(1)
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length)
            }
        }
        return ''
    }

    checkAndLoadScriptsForAllowedCookie() {
        const cookieConsentValue = this.getCookie('cookie_consent')
        if (cookieConsentValue != '') {
            const cookieConsentJsonValue = JSON.parse(cookieConsentValue)
            //console.log(cookieConsentJsonValue);

            //--consent_necessary
            if (cookieConsentJsonValue['consent_necessary'] == true) {
            }

            //--consent_preferences
            if (cookieConsentJsonValue['consent_preferences'] == true) {
            }

            //--consent_statistics
            if (cookieConsentJsonValue['consent_statistics'] == true) {
            }

            //--consent_marketing
            if (cookieConsentJsonValue['consent_marketing'] == true) {
            }

            //--consent_unclassified other
            if (cookieConsentJsonValue['consent_unclassified'] == true) {
            }
        }
    }

    displayGDPR() {
        if (this.getCookie('cookie_consent') == '') {
            var cookieConsentDiv = document.getElementById('cookie-consent')

            if (cookieConsentDiv) {
                var hiddenDiv = document.querySelector('.hidden-div')

                if (hiddenDiv) {
                    hiddenDiv.classList.remove('hidden-div')
                }
            }
        }
    }

    start() {
        this.initPopupScript()

        this.initLanguage()

        //Display GDPR:
        this.displayGDPR()
        //GDPR SET
        this.checkAndLoadScriptsForAllowedCookie() //check and load Scripts for Allowed Cookies
    }

    shouldRun() {
        return !!this.$('#cookie-consent')
    }

    onDomContentLoaded() {
        const script = document.createElement('script')

        script.src = '/assets/lib/jquery.min.js'

        script.async = true

        script.onload = () => {
            this.start()
        }

        document.head.appendChild(script)
    }

    //--SET COOKIE PREFERENCES
    setCookieConsent(
        cookie_consent_name,
        cookie_consent_lifetime,
        consent_necessary,
        consent_preferences,
        consent_statistics,
        consent_marketing,
        consent_unclassified
    ) {
        let consent_id = this.create_UUID()
        let consent_url = window.location.href
        let consent_client_datetime = new Date(
            new Date(new Date(new Date()).toISOString()).getTime() -
                new Date().getTimezoneOffset() * 60000
        )
            .toISOString()
            .slice(0, 19)
            .replace('T', ' ') //Date.now(); // in miliseconds
        let value = {
            consent_id: consent_id,
            cookie_consent_name: cookie_consent_name,
            consent_necessary: consent_necessary,
            consent_preferences: consent_preferences,
            consent_statistics: consent_statistics,
            consent_marketing: consent_marketing,
            consent_unclassified: consent_unclassified,
            cookie_consent_lifetime: cookie_consent_lifetime,
            consent_client_datetime: consent_client_datetime,
        }

        value = JSON.stringify(value)

        let cookie_consent_value = value

        let _token = jQuery('meta[name="csrf-token"]').attr('content') // very important for ajax all

        jQuery.ajax({
            type: 'GET',
            url: '/set-cookie-consent',
            data: {
                _token: _token,
                cookie_consent_name: cookie_consent_name,
                consent_id: consent_id,
                cookie_consent_value: cookie_consent_value,
                consent_necessary: consent_necessary,
                consent_preferences: consent_preferences,
                consent_statistics: consent_statistics,
                consent_marketing: consent_marketing,
                consent_unclassified: consent_unclassified,
                consent_url: consent_url,
                cookie_consent_lifetime: cookie_consent_lifetime,
                consent_client_datetime: consent_client_datetime,
            },

            success: (data) => {
                this.setCookie(
                    'cookie_consent',
                    cookie_consent_value,
                    cookie_consent_lifetime
                ) //set actually cookie

                this.displayGDPR()

                this.checkAndLoadScriptsForAllowedCookie() //check and load Scripts for Allowed Cookies

                this.close()
                // location.reload() // force to reload page with new cookies settings.E.G checkAndLoadScriptsForAllowedCookie will attached google analitycs script to head we need to refresh the page for the script to work!
            },
        })
    }

    close() {
        this.cookieWidget.addEventListener('animationend', () => {
            this.cookieWidget.remove()
        })

        this.cookieWidget.classList.add('closing')
    }

    onDocumentClick(e) {
        const elem = e.target
        const block = () => {
            e.preventDefault()
            e.stopPropagation()
        }

        if (elem.closest('#button-accept-all')) {
            block()
            this.setCookieConsent(
                'cookie_consent',
                365,
                true,
                true,
                true,
                true,
                true
            )
        }

        if (elem.closest('#accept-necessary')) {
            block()
            this.setCookieConsent(
                'cookie_consent',
                365,
                true,
                false,
                false,
                false,
                false
            )
        }
    }

    initPopupScript() {
        var customizeLink = document.getElementById('customize-link')
        var popupOverlay = document.getElementsByClassName('popup-overlay')[0]
        var popupOK = document.getElementById('popup-ok')
        var popupCancel = document.getElementById('popup-cancel')

        customizeLink.addEventListener('click', function (event) {
            event.preventDefault() // Prevent the default link behavior
            popupOverlay.style.display = 'block'
        })

        popupOK.addEventListener('click', function () {
            var necessaryCheckbox = document.querySelector(
                "input[name='necessary']"
            )
            var preferencesCheckbox = document.querySelector(
                "input[name='preferences']"
            )
            var statisticsCheckbox = document.querySelector(
                "input[name='statistics']"
            )
            var marketingCheckbox = document.querySelector(
                "input[name='marketing']"
            )
            var othersCheckbox = document.querySelector("input[name='others']")

            var necessaryValue = necessaryCheckbox.checked
            var preferencesValue = preferencesCheckbox.checked
            var statisticsValue = statisticsCheckbox.checked
            var marketingValue = marketingCheckbox.checked
            var othersValue = othersCheckbox.checked

            const gdpr = new GdprWidgetRenderer()

            gdpr.setCookieConsent(
                'cookie_consent',
                365,
                necessaryValue,
                preferencesValue,
                statisticsValue,
                marketingValue,
                othersValue
            )

            popupOverlay.style.display = 'none'
        })

        popupCancel.addEventListener('click', function () {
            popupOverlay.style.display = 'none'
        })
    }

    initLanguage() {
        //--SET CHANGE LANGUAGE
        jQuery(function () {
            // Lets be professional, shall we?
            'use strict'
            // Some variables for later
            var dictionary, set_lang
            // Object literal behaving as multi-dictionary
            dictionary = {
                english: {
                    'cookie-consent-msg':
                        'This website uses cookies & third party services.',
                    'cookie-consent-more-info': 'Privacy Policy',
                    'button-accept-all': 'Accept All',
                    'button-accept-necessary': 'Accept Necessary',
                    labelCustomize: 'Customize',
                    popupCookieH3: 'Cookie Settings',
                    labelNecessary:
                        'Necessary. These trackers are used for activities that are strictly necessary to operate or deliver the service you requested from us and, therefore, do not require you to consent.',
                    labelPreferences:
                        'Preferences. These trackers enable basic interactions and functionalities that allow you to access selected features of our service and facilitate your communication with us.',
                    labelStatistics:
                        'Statistics. These trackers help us to measure traffic and analyze your behavior to improve our service.',
                    labelMarketing:
                        'Marketing. These trackers help us to deliver personalized ads or marketing content to you, and to measure their performance.',
                    labelOthers:
                        'Others. Unclassified cookies are cookies that do not belong to any other category or are in the process of categorization.',
                    buttonApply: 'Apply',
                    buttonCancel: 'Cancel',
                },
                austria: {
                    'cookie-consent-msg':
                        'Diese Website verwendet Cookies und Dienste Dritter.',
                    'cookie-consent-more-info': 'Mehr Info',
                    labelCustomize: 'Aanpassen',
                    'button-accept-all': 'Alle akzeptieren',
                    'button-accept-necessary': 'Notwendiges akzeptieren',
                    popupCookieH3: 'Cookie-Einstellungen',
                    labelNecessary:
                        'Notwendig. Diese Tracker werden für Aktivitäten verwendet, die für den Betrieb oder die Bereitstellung des von Ihnen angeforderten Dienstes strikt erforderlich sind und daher keiner Zustimmung bedürfen.',
                    labelPreferences:
                        'Präferenzen. Diese Tracker ermöglichen grundlegende Interaktionen und Funktionen, die es Ihnen ermöglichen, ausgewählte Funktionen unseres Dienstes zu nutzen und die Kommunikation mit uns zu erleichtern.',
                    labelStatistics:
                        'Statistiken. Diese Tracker helfen uns, den Verkehr zu messen und Ihr Verhalten zu analysieren, um unseren Service zu verbessern.',
                    labelMarketing:
                        'Marketing. Diese Tracker helfen uns dabei, personalisierte Werbung oder Marketinginhalte an Sie zu liefern und deren Leistung zu messen.',
                    labelOthers:
                        'Andere. Nicht klassifizierte Cookies sind Cookies, die keiner anderen Kategorie angehören oder sich im Kategorisierungsprozess befinden.',
                    buttonApply: 'Anwenden',
                    buttonCancel: 'Abbrechen',
                },
                belgium: {
                    'cookie-consent-msg':
                        'Deze website maakt gebruik van cookies en diensten van derden.',
                    'cookie-consent-more-info': 'Meer informatie',
                    'button-accept-all': 'Alles accepteren',
                    'button-accept-necessary': 'Noodzakelijk accepteren',
                    labelCustomize: 'Aanpassen',
                    popupCookieH3: 'Cookie-instellingen',
                    labelNecessary:
                        'Noodzakelijk. Deze trackers worden gebruikt voor activiteiten die strikt noodzakelijk zijn voor het functioneren of leveren van de door u gevraagde dienst en vereisen daarom geen toestemming.',
                    labelPreferences:
                        'Voorkeuren. Deze trackers maken basisinteracties en functionaliteiten mogelijk die u in staat stellen geselecteerde functies van onze service te gebruiken en uw communicatie met ons te vergemakkelijken.',
                    labelStatistics:
                        'Statistieken. Deze trackers helpen ons om het verkeer te meten en uw gedrag te analyseren om onze service te verbeteren.',
                    labelMarketing:
                        'Marketing. Deze trackers helpen ons om gepersonaliseerde advertenties of marketinginhoud aan u te leveren en de prestaties ervan te meten.',
                    labelOthers:
                        'Overige. Niet-geclassificeerde cookies zijn cookies die niet tot een andere categorie behoren of zich in het categoriseringsproces bevinden.',
                    buttonApply: 'Toepassen',
                    buttonCancel: 'Annuleren',
                },
                bulgaria: {
                    'cookie-consent-msg':
                        'Този уебсайт използва бисквитки и услуги на трети страни.',
                    'cookie-consent-more-info': 'Повече информация',
                    'button-accept-all': 'Приемам всички',
                    'button-accept-necessary': 'Приемам задължителните',
                    labelCustomize: 'Персонализиране',
                    popupCookieH3: 'Настройки за бисквитки',
                    labelNecessary:
                        'Задължителни. Тези проследяващи елементи се използват за дейности, които са стриктно необходими за работата или предоставянето на услугата, която сте поискали от нас и следователно не изискват вашето съгласие.',
                    labelPreferences:
                        'Предпочитания. Тези проследяващи елементи позволяват основни взаимодействия и функционалности, които ви позволяват да използвате избрани функции на нашата услуга и да улесняват комуникацията ви с нас.',
                    labelStatistics:
                        'Статистика. Тези проследяващи елементи помагат ни да измерим трафика и да анализираме поведението ви, за да подобрим нашата услуга.',
                    labelMarketing:
                        'Маркетинг. Тези проследяващи елементи ни помагат да предоставяме персонализирани реклами или маркетингово съдържание и да измерваме техния ефективност.',
                    labelOthers:
                        'Други. Некласифицираните бисквитки са бисквитки, които не принадлежат на никаква друга категория или се намират в процес на класификация.',
                    buttonApply: 'Приложи',
                    buttonCancel: 'Отказ',
                },
                croatia: {
                    'cookie-consent-msg':
                        'Ova web stranica koristi kolačiće i usluge trećih strana.',
                    'cookie-consent-more-info': 'Više informacija',
                    'button-accept-all': 'Prihvati sve',
                    'button-accept-necessary': 'Prihvati nužno',
                    labelCustomize: 'Prilagodi',
                    popupCookieH3: 'Postavke kolačića',
                    labelNecessary:
                        'Nužno. Ovi pratiči koriste se za aktivnosti koje su strogo potrebne za rad ili pružanje usluge koju ste zatražili od nas i stoga ne zahtijevaju vaš pristanak.',
                    labelPreferences:
                        'Postavke. Ovi pratiči omogućuju osnovne interakcije i funkcionalnosti koje vam omogućuju pristup odabranim značajkama naše usluge i olakšavaju komunikaciju s nama.',
                    labelStatistics:
                        'Statistika. Ovi pratiči pomažu nam mjeriti promet i analizirati vaše ponašanje kako bismo poboljšali našu uslugu.',
                    labelMarketing:
                        'Marketing. Ovi pratiči pomažu nam da vam pružimo personalizirane oglase ili marketinški sadržaj i mjerimo njihovu učinkovitost.',
                    labelOthers:
                        'Ostalo. Nepoklasificirani kolačići su kolačići koji ne pripadaju nijednoj drugoj kategoriji ili su u procesu klasifikacije.',
                    buttonApply: 'Primijeni',
                    buttonCancel: 'Odustani',
                },
                cyprus: {
                    'cookie-consent-msg':
                        'Αυτή η ιστοσελίδα χρησιμοποιεί cookies και υπηρεσίες τρίτων.',
                    'cookie-consent-more-info': 'Περισσότερες πληροφορίες',
                    'button-accept-all': 'Αποδοχή όλων',
                    'button-accept-necessary': 'Αποδοχή απαραίτητων',
                    labelCustomize: 'Προσαρμογή',
                    popupCookieH3: 'Ρυθμίσεις cookies',
                    labelNecessary:
                        'Απαραίτητα. Αυτά τα ανιχνευτικά χρησιμοποιούνται για δραστηριότητες που είναι απόλυτα απαραίτητες για τη λειτουργία ή την παροχή της υπηρεσίας που ζητήσατε από εμάς και, συνεπώς, δεν απαιτούν τη συναίνεσή σας.',
                    labelPreferences:
                        'Προτιμήσεις. Αυτά τα ανιχνευτικά επιτρέπουν βασικές αλληλεπιδράσεις και λειτουργίες που σας επιτρέπουν να έχετε πρόσβαση σε επιλεγμένα χαρακτηριστικά της υπηρεσίας μας και να διευκολύνουν την επικοινωνία σας μαζί μας.',
                    labelStatistics:
                        'Στατιστικά. Αυτά τα ανιχνευτικά μας βοηθούν να μετρήσουμε την κίνηση και να αναλύσουμε τη συμπεριφορά σας για να βελτιώσουμε την υπηρεσία μας.',
                    labelMarketing:
                        'Μάρκετινγκ. Αυτά τα ανιχνευτικά μας βοηθούν να σας παρέχουμε εξατομικευμένες διαφημίσεις ή περιεχόμενο μάρκετινγκ και να μετρήσουμε την απόδοσή τους.',
                    labelOthers:
                        'Άλλα. Οι μη κατηγοριοποιημένοι κουλοχέρηδες είναι κουλοχέρηδες που δεν ανήκουν σε καμία άλλη κατηγορία ή βρίσκονται σε διαδικασία κατηγοριοποίησης.',
                    buttonApply: 'Εφαρμογή',
                    buttonCancel: 'Ακύρωση',
                },
                'czech republic': {
                    'cookie-consent-msg':
                        'Tato webová stránka používá cookies a služby třetích stran.',
                    'cookie-consent-more-info': 'Více informací',
                    'button-accept-all': 'Přijmout vše',
                    'button-accept-necessary': 'Přijmout nutné',
                    labelCustomize: 'Přizpůsobit',
                    popupCookieH3: 'Nastavení souborů cookie',
                    labelNecessary:
                        'Nutné. Tyto sledovací prvky se používají pro činnosti, které jsou přísně nutné pro provoz nebo poskytování služby, kterou jste požádali, a proto nevyžadují váš souhlas.',
                    labelPreferences:
                        'Předvolby. Tyto sledovací prvky umožňují základní interakce a funkce, které vám umožní přístup k vybraným funkcím naší služby a usnadňují vaši komunikaci s námi.',
                    labelStatistics:
                        'Statistiky. Tyto sledovací prvky nám pomáhají měřit provoz a analyzovat vaše chování pro zlepšení naší služby.',
                    labelMarketing:
                        'Marketing. Tyto sledovací prvky nám pomáhají zobrazovat personalizované reklamy nebo marketingový obsah a měřit jejich výkon.',
                    labelOthers:
                        'Ostatní. Nezařazené cookies jsou cookies, které nepatří do žádné jiné kategorie nebo jsou ve fázi klasifikace.',
                    buttonApply: 'Použít',
                    buttonCancel: 'Zrušit',
                },
                denmark: {
                    'cookie-consent-msg':
                        'Denne hjemmeside bruger cookies og tredjepartstjenester.',
                    'cookie-consent-more-info': 'Mere info',
                    'button-accept-all': 'Accepter alle',
                    'button-accept-necessary': 'Accepter nødvendige',
                    labelCustomize: 'Tilpas',
                    popupCookieH3: 'Cookieindstillinger',
                    labelNecessary:
                        'Nødvendige. Disse sporingsenheder bruges til aktiviteter, der er strengt nødvendige for at drive eller levere den service, du har anmodet om fra os, og kræver derfor ikke dit samtykke.',
                    labelPreferences:
                        'Præferencer. Disse sporingsenheder gør det muligt at udføre grundlæggende interaktioner og funktionaliteter, der giver dig adgang til udvalgte funktioner i vores service og letter din kommunikation med os.',
                    labelStatistics:
                        'Statistik. Disse sporingsenheder hjælper os med at måle trafik og analysere din adfærd for at forbedre vores service.',
                    labelMarketing:
                        'Marketing. Disse sporingsenheder hjælper os med at levere personaliserede annoncer eller marketingindhold til dig og måle deres ydeevne.',
                    labelOthers:
                        'Andre. Ikke-klassificerede cookies er cookies, der ikke hører til nogen anden kategori eller er i processen med kategorisering.',
                    buttonApply: 'Anvend',
                    buttonCancel: 'Annuller',
                },
                estonia: {
                    'cookie-consent-msg':
                        'See veebisait kasutab küpsiseid ja kolmanda osapoole teenuseid.',
                    'cookie-consent-more-info': 'Rohkem teavet',
                    'button-accept-all': 'Nõustu kõigiga',
                    'button-accept-necessary': 'Nõustu vajalikega',
                    labelCustomize: 'Kohandamine',
                    popupCookieH3: 'Küpsiste seaded',
                    labelNecessary:
                        'Vajalikud. Need jälgimisseadmed on vajalikud tegevuste jaoks, mis on rangelt vajalikud meie teenuse tööks või pakkumiseks, mida te meilt palusite, ning seetõttu ei vaja teie nõusolekut.',
                    labelPreferences:
                        'Eelistused. Need jälgimisseadmed võimaldavad põhilisi suhtlusi ja funktsioone, mis võimaldavad teil juurdepääsu valitud omadustele meie teenuses ning hõlbustavad suhtlemist meiega.',
                    labelStatistics:
                        'Statistika. Need jälgimisseadmed aitavad meil mõõta liiklust ja analüüsida teie käitumist, et parandada meie teenust.',
                    labelMarketing:
                        'Turundus. Need jälgimisseadmed aitavad meil pakkuda teile isikupärastatud reklaame või turundussisu ning mõõta nende tulemuslikkust.',
                    labelOthers:
                        'Muu. Klassifitseerimata küpsised on küpsised, mis ei kuulu ühtegi teise kategooriasse või on klassifitseerimise protsessis.',
                    buttonApply: 'Kohalda',
                    buttonCancel: 'Tühista',
                },
                finland: {
                    'cookie-consent-msg':
                        'Tämä verkkosivusto käyttää evästeitä ja kolmannen osapuolen palveluita.',
                    'cookie-consent-more-info': 'Lisätietoja',
                    'button-accept-all': 'Hyväksy kaikki',
                    'button-accept-necessary': 'Hyväksy välttämättömät',
                    labelCustomize: 'Mukauta',
                    popupCookieH3: 'Evästeasetukset',
                    labelNecessary:
                        'Välttämättömät. Nämä seurantalaitteet ovat välttämättömiä toiminnallisuuksia varten, jotka ovat ehdottoman tarpeellisia pyytämäsi palvelun toimittamiseen tai toimintaan, eikä niiden käyttö vaadi suostumustasi.',
                    labelPreferences:
                        'Asetukset. Nämä seurantalaitteet mahdollistavat perustoimintojen ja -ominaisuuksien käytön, joiden avulla voit käyttää valittuja ominaisuuksia palvelussamme ja helpottaa kommunikointiasi kanssamme.',
                    labelStatistics:
                        'Tilastot. Nämä seurantalaitteet auttavat meitä mittaamaan liikennettä ja analysoimaan käyttäytymistäsi palvelumme parantamiseksi.',
                    labelMarketing:
                        'Markkinointi. Nämä seurantalaitteet auttavat meitä toimittamaan sinulle personoituja mainoksia tai markkinointisisältöä sekä mittaamaan niiden suorituskykyä.',
                    labelOthers:
                        'Muut. Luokittelemattomat evästeet ovat evästeitä, jotka eivät kuulu mihinkään muuhun kategoriaan tai ovat luokittelemisprosessissa.',
                    buttonApply: 'Hyväksy',
                    buttonCancel: 'Peruuta',
                },
                france: {
                    'cookie-consent-msg':
                        'Ce site utilise des cookies et des services tiers.',
                    'cookie-consent-more-info': "Plus d'informations",
                    'button-accept-all': 'Tout accepter',
                    'button-accept-necessary':
                        'Accepter les cookies nécessaires',
                    labelCustomize: 'Personnaliser',
                    popupCookieH3: 'Paramètres des cookies',
                    labelNecessary:
                        'Nécessaires. Ces traceurs sont utilisés pour les activités strictement nécessaires au fonctionnement ou à la fourniture du service que vous avez demandé, et ne nécessitent donc pas votre consentement.',
                    labelPreferences:
                        'Préférences. Ces traceurs permettent des interactions de base et des fonctionnalités qui vous donnent accès à des fonctionnalités sélectionnées de notre service et facilitent votre communication avec nous.',
                    labelStatistics:
                        "Statistiques. Ces traceurs nous aident à mesurer le trafic et à analyser votre comportement afin d'améliorer notre service.",
                    labelMarketing:
                        'Marketing. Ces traceurs nous aident à diffuser des publicités personnalisées ou du contenu marketing et à mesurer leur performance.',
                    labelOthers:
                        "Autres. Les cookies non classifiés sont des cookies qui n'appartiennent à aucune autre catégorie ou sont en cours de classification.",
                    buttonApply: 'Appliquer',
                    buttonCancel: 'Annuler',
                },
                germany: {
                    'cookie-consent-msg':
                        'Diese Website verwendet Cookies und Dienste von Drittanbietern.',
                    'cookie-consent-more-info': 'Mehr Informationen',
                    'button-accept-all': 'Alle akzeptieren',
                    'button-accept-necessary': 'Nur notwendige akzeptieren',
                    labelCustomize: 'Anpassen',
                    popupCookieH3: 'Cookie-Einstellungen',
                    labelNecessary:
                        'Notwendig. Diese Tracker werden für Aktivitäten verwendet, die für den Betrieb oder die Bereitstellung des von Ihnen angeforderten Dienstes unbedingt erforderlich sind und daher keiner Zustimmung bedürfen.',
                    labelPreferences:
                        'Präferenzen. Diese Tracker ermöglichen grundlegende Interaktionen und Funktionen, die Ihnen den Zugriff auf ausgewählte Funktionen unseres Dienstes ermöglichen und Ihre Kommunikation mit uns erleichtern.',
                    labelStatistics:
                        'Statistiken. Diese Tracker helfen uns dabei, den Datenverkehr zu messen und Ihr Verhalten zu analysieren, um unseren Service zu verbessern.',
                    labelMarketing:
                        'Marketing. Diese Tracker unterstützen uns dabei, personalisierte Werbung oder Marketinginhalte bereitzustellen und deren Leistung zu messen.',
                    labelOthers:
                        'Sonstige. Nicht klassifizierte Cookies sind Cookies, die keiner anderen Kategorie zugeordnet sind oder sich in der Klassifizierung befinden.',
                    buttonApply: 'Anwenden',
                    buttonCancel: 'Abbrechen',
                },
                greece: {
                    'cookie-consent-msg':
                        'Αυτός ο ιστότοπος χρησιμοποιεί cookies και υπηρεσίες τρίτων.',
                    'cookie-consent-more-info': 'Περισσότερες πληροφορίες',
                    'button-accept-all': 'Αποδοχή όλων',
                    'button-accept-necessary': 'Αποδοχή απαραίτητων',
                    labelCustomize: 'Προσαρμογή',
                    popupCookieH3: 'Ρυθμίσεις cookies',
                    labelNecessary:
                        'Απαραίτητα. Αυτά τα ανιχνευτικά χρησιμοποιούνται για δραστηριότητες που είναι απόλυτα απαραίτητες για τη λειτουργία ή την παροχή της υπηρεσίας που ζητήσατε από εμάς και, συνεπώς, δεν απαιτούν τη συναίνεσή σας.',
                    labelPreferences:
                        'Προτιμήσεις. Αυτά τα ανιχνευτικά επιτρέπουν βασικές αλληλεπιδράσεις και λειτουργίες που σας επιτρέπουν να έχετε πρόσβαση σε επιλεγμένα χαρακτηριστικά της υπηρεσίας μας και ευκολία στην επικοινωνία σας μαζί μας.',
                    labelStatistics:
                        'Στατιστικά. Αυτά τα ανιχνευτικά μας βοηθούν να μετρήσουμε την επισκεψιμότητα και να αναλύσουμε τη συμπεριφορά σας για τη βελτίωση της υπηρεσίας μας.',
                    labelMarketing:
                        'Μάρκετινγκ. Αυτά τα ανιχνευτικά μας βοηθούν να σας παρέχουμε εξατομικευμένες διαφημίσεις ή περιεχόμενο μάρκετινγκ και να μετρήσουμε την απόδοσή τους.',
                    labelOthers:
                        'Άλλα. Οι μη κατηγοριοποιημένοι cookies είναι cookies που δεν ανήκουν σε καμία άλλη κατηγορία ή βρίσκονται σε διαδικασία κατηγοριοποίησης.',
                    buttonApply: 'Εφαρμογή',
                    buttonCancel: 'Ακύρωση',
                },
                'czech republic': {
                    'cookie-consent-msg':
                        'Tato webová stránka používá cookies a služby třetích stran.',
                    'cookie-consent-more-info': 'Více informací',
                    'button-accept-all': 'Přijmout vše',
                    'button-accept-necessary': 'Přijmout nutné',
                    labelCustomize: 'Přizpůsobit',
                    popupCookieH3: 'Nastavení souborů cookie',
                    labelNecessary:
                        'Nutné. Tyto sledovací prvky se používají pro činnosti, které jsou přísně nutné pro provoz nebo poskytování služby, kterou jste požádali, a proto nevyžadují váš souhlas.',
                    labelPreferences:
                        'Předvolby. Tyto sledovací prvky umožňují základní interakce a funkce, které vám umožní přístup k vybraným funkcím naší služby a usnadňují vaši komunikaci s námi.',
                    labelStatistics:
                        'Statistiky. Tyto sledovací prvky nám pomáhají měřit provoz a analyzovat vaše chování pro zlepšení naší služby.',
                    labelMarketing:
                        'Marketing. Tyto sledovací prvky nám pomáhají poskytovat vám personalizovanou reklamu nebo marketingový obsah a měřit jejich výkon.',
                    labelOthers:
                        'Ostatní. Nezařazené cookies jsou cookies, které nespadají do žádné jiné kategorie nebo jsou ve fázi zařazování.',
                    buttonApply: 'Použít',
                    buttonCancel: 'Zrušit',
                },
                hungary: {
                    'cookie-consent-msg':
                        'Ez a weboldal cookie-kat és harmadik fél szolgáltatásait használja.',
                    'cookie-consent-more-info': 'További információ',
                    'button-accept-all': 'Összes elfogadása',
                    'button-accept-necessary': 'Szükségesek elfogadása',
                    labelCustomize: 'Testreszabás',
                    popupCookieH3: 'Cookie beállítások',
                    labelNecessary:
                        'Szükséges. Ezek a követők olyan tevékenységekhez szükségesek, amelyek a kért szolgáltatás működtetéséhez vagy nyújtásához szükségesek, és ezért nem igényelnek beleegyezését.',
                    labelPreferences:
                        'Preferenciák. Ezek a követők alapvető interakciókat és funkciókat tesznek lehetővé, amelyek lehetővé teszik a kiválasztott funkciók elérését szolgáltatásunkban, és megkönnyítik a kommunikációt velünk.',
                    labelStatistics:
                        'Statisztikák. Ezek a követők segítenek mérni a forgalmat és elemezni a viselkedését a szolgáltatásunk fejlesztése érdekében.',
                    labelMarketing:
                        'Marketing. Ezek a követők segítenek személyre szabott hirdetések vagy marketing tartalmak megjelenítésében, valamint a teljesítményük mérésében.',
                    labelOthers:
                        'Egyéb. Nem besorolt sütik olyan sütik, amelyek nem tartoznak egyéb kategóriába, vagy besorolás alatt állnak.',
                    buttonApply: 'Alkalmaz',
                    buttonCancel: 'Mégse',
                },
                italy: {
                    'cookie-consent-msg':
                        'Questo sito web utilizza cookie e servizi di terze parti.',
                    'cookie-consent-more-info': 'Maggiori informazioni',
                    'button-accept-all': 'Accetta tutto',
                    'button-accept-necessary': 'Accetta solo necessari',
                    labelCustomize: 'Personalizza',
                    popupCookieH3: 'Impostazioni cookie',
                    labelNecessary:
                        'Necessari. Questi tracker sono utilizzati per attività strettamente necessarie per il funzionamento o la fornitura del servizio richiesto e, pertanto, non richiedono il tuo consenso.',
                    labelPreferences:
                        'Preferenze. Questi tracker consentono interazioni e funzionalità di base che ti consentono di accedere a determinate caratteristiche del nostro servizio e agevolano la comunicazione con noi.',
                    labelStatistics:
                        'Statistiche. Questi tracker ci aiutano a misurare il traffico e analizzare il tuo comportamento per migliorare il nostro servizio.',
                    labelMarketing:
                        'Marketing. Questi tracker ci aiutano a fornirti annunci pubblicitari o contenuti di marketing personalizzati e a misurarne le prestazioni.',
                    labelOthers:
                        "Altro. I cookie non classificati sono cookie che non appartengono a nessun'altra categoria o sono in fase di classificazione.",
                    buttonApply: 'Applica',
                    buttonCancel: 'Annulla',
                },
                latvia: {
                    'cookie-consent-msg':
                        'Šī vietne izmanto sīkdatnes un trešo pušu pakalpojumus.',
                    'cookie-consent-more-info': 'Vairāk informācijas',
                    'button-accept-all': 'Pieņemt visu',
                    'button-accept-necessary': 'Pieņemt tikai nepieciešamos',
                    labelCustomize: 'Pielāgot',
                    popupCookieH3: 'Sīkdatņu iestatījumi',
                    labelNecessary:
                        'Nepieciešamie. Šie izsekošanas līdzekļi tiek izmantoti darbībām, kas ir stingri nepieciešamas, lai darbotos vai nodrošinātu jums pieprasīto pakalpojumu, tāpēc nav nepieciešams jūsu piekrišanas.',
                    labelPreferences:
                        'Iespējas. Šie izsekošanas līdzekļi ļauj pamata mijiedarbību un funkcijas, kas ļauj piekļūt izvēlētajām mūsu pakalpojumu funkcijām un atvieglo jūsu saziņu ar mums.',
                    labelStatistics:
                        'Statistika. Šie izsekošanas līdzekļi palīdz mums mērīt trafiku un analizēt jūsu uzvedību, lai uzlabotu mūsu pakalpojumu.',
                    labelMarketing:
                        'Mārketinga. Šie izsekošanas līdzekļi palīdz mums sniegt personalizētas reklāmas vai mārketinga saturu un mērīt to veiktspēju.',
                    labelOthers:
                        'Citi. Nešķirotās sīkdatnes ir sīkdatnes, kas nepieder nevienai citai kategorijai vai atrodas kategorizācijas procesā.',
                    buttonApply: 'Pielietot',
                    buttonCancel: 'Atcelt',
                },
                lithuania: {
                    'cookie-consent-msg':
                        'Šis tinklapis naudoja slapukus ir trečiųjų šalių paslaugas.',
                    'cookie-consent-more-info': 'Daugiau informacijos',
                    'button-accept-all': 'Sutinku su visais',
                    'button-accept-necessary': 'Priimti tik būtinuosius',
                    labelCustomize: 'Prisitaikyti',
                    popupCookieH3: 'Slapukų nustatymai',
                    labelNecessary:
                        'Būtini. Šie stebėjimo įrankiai naudojami veiklai, kuri yra griežtai būtina norint veikti arba teikti jums reikalingą paslaugą, ir todėl jūsų sutikimo nereikia.',
                    labelPreferences:
                        'Nuostatos. Šie stebėjimo įrankiai įgalina pagrindinius sąveikos ir funkcijų, kurios leidžia pasiekti pasirinktas mūsų paslaugų funkcijas ir palengvina bendravimą su mumis.',
                    labelStatistics:
                        'Statistika. Šie stebėjimo įrankiai padeda matuoti srautą ir analizuoti jūsų elgesį, kad pagerintų mūsų paslaugą.',
                    labelMarketing:
                        'Marketingas. Šie stebėjimo įrankiai padeda mums pateikti jums asmeniškus skelbimus arba rinkodaros turinį ir matuoti jų veiksmingumą.',
                    labelOthers:
                        'Kita. Nepriskirti slapukai yra slapukai, kurie nepriklauso jokiai kitai kategorijai arba yra kategorizavimo procese.',
                    buttonApply: 'Taikyti',
                    buttonCancel: 'Atšaukti',
                },
                luxembourg: {
                    'cookie-consent-msg':
                        'Dëse Websäit benotzt Cookien an Drëtt-Anbieter Servicer.',
                    'cookie-consent-more-info': 'Méi Informatiounen',
                    'button-accept-all': 'All akzeptéieren',
                    'button-accept-necessary': 'Nëmmen néideg akzeptéieren',
                    labelCustomize: 'Umfroen',
                    popupCookieH3: 'Cookie Astellungen',
                    labelNecessary:
                        'Néideg. Dës Tracker ginn fir Aktivitéiten benotzt déi streng néideg sinn fir de Service ze betreiwen oder de Service ze liwweren deen Dir vu eis ugefrot hutt, an doriwwer musst Dir net zoustëmmen.',
                    labelPreferences:
                        'Präferenze. Dës Tracker erméigleche Grondinteraktiounen an Funktiounalitéiten déi et Iech erméigleche sécher ausgewielte Featurë vun eisem Service ze benotzen an Iech den Austausch mat eis ze fäerdeg machen.',
                    labelStatistics:
                        'Statistiken. Dës Tracker hëllefen eis de Verkéier ze messen an Äre Comportement ze analyséieren fir eisen Service ze verbesseren.',
                    labelMarketing:
                        'Marketing. Dës Tracker hëllefen eis personaliséiert Annoncen oder Marketinginhalter un Iech ze ofréieren a méi erfueren iwwert hiert Performance ze ginn.',
                    labelOthers:
                        'Aner. Onklassifizéiert Cookies sinn Cookies déi net an eng aner Kategorie gehéieren oder sech an der Klassifikatiounsbestëmmung befannen.',
                    buttonApply: 'Uwenden',
                    buttonCancel: 'Ofbriechen',
                },
                malta: {
                    'cookie-consent-msg':
                        'This website uses cookies & third party services.',
                    'cookie-consent-more-info': 'Privacy Policy',
                    'button-accept-all': 'Accept All',
                    'button-accept-necessary': 'Accept Necessary',
                    labelCustomize: 'Personalizza',
                    popupCookieH3: 'Cookie Settings',
                    labelNecessary:
                        'Necessary. These trackers are used for activities that are strictly necessary to operate or deliver the service you requested from us and, therefore, do not require you to consent.',
                    labelPreferences:
                        'Preferences. These trackers enable basic interactions and functionalities that allow you to access selected features of our service and facilitate your communication with us.',
                    labelStatistics:
                        'Statistics. These trackers help us to measure traffic and analyze your behavior to improve our service.',
                    labelMarketing:
                        'Marketing. These trackers help us to deliver personalized ads or marketing content to you, and to measure their performance.',
                    labelOthers:
                        'Others. Unclassified cookies are cookies that do not belong to any other category or are in the process of categorization.',
                    buttonApply: 'Apply',
                    buttonCancel: 'Cancel',
                },
                netherlands: {
                    'cookie-consent-msg':
                        'Deze website maakt gebruik van cookies en externe diensten.',
                    'cookie-consent-more-info': 'Meer informatie',
                    'button-accept-all': 'Accepteer alles',
                    'button-accept-necessary': 'Accepteer noodzakelijk',
                    labelCustomize: 'Aanpassen',
                    popupCookieH3: 'Cookie-instellingen',
                    labelNecessary:
                        'Noodzakelijk. Deze trackers worden gebruikt voor activiteiten die strikt noodzakelijk zijn voor het functioneren of leveren van de door u gevraagde dienst en vereisen daarom geen toestemming.',
                    labelPreferences:
                        'Voorkeuren. Deze trackers stellen basisinteracties en functionaliteiten in staat waarmee u geselecteerde functies van onze dienst kunt benaderen en uw communicatie met ons kunt vergemakkelijken.',
                    labelStatistics:
                        'Statistieken. Deze trackers helpen ons het verkeer te meten en uw gedrag te analyseren om onze dienst te verbeteren.',
                    labelMarketing:
                        'Marketing. Deze trackers helpen ons om gepersonaliseerde advertenties of marketinginhoud aan u te leveren en de prestaties ervan te meten.',
                    labelOthers:
                        'Overige. Niet-geclassificeerde cookies zijn cookies die niet tot een andere categorie behoren of nog in het categoriseringsproces zitten.',
                    buttonApply: 'Toepassen',
                    buttonCancel: 'Annuleren',
                },
                poland: {
                    'cookie-consent-msg':
                        'Ta witryna używa plików cookie i usług osób trzecich.',
                    'cookie-consent-more-info': 'Więcej informacji',
                    'button-accept-all': 'Akceptuj wszystko',
                    'button-accept-necessary': 'Akceptuj niezbędne',
                    labelCustomize: 'Dostosuj',
                    popupCookieH3: 'Ustawienia plików cookie',
                    labelNecessary:
                        'Niezbędne. Te śledzacze są wykorzystywane do działań, które są ściśle niezbędne do działania lub dostarczenia żądanej przez ciebie usługi i dlatego nie wymagają twojej zgody.',
                    labelPreferences:
                        'Preferencje. Te śledzacze umożliwiają podstawowe interakcje i funkcje, które pozwalają na dostęp do wybranych funkcji naszej usługi i ułatwiają komunikację z nami.',
                    labelStatistics:
                        'Statystyki. Te śledzacze pomagają nam mierzyć ruch i analizować twoje zachowanie w celu poprawy naszej usługi.',
                    labelMarketing:
                        'Marketing. Te śledzacze pomagają nam dostarczać spersonalizowane reklamy lub treści marketingowe oraz mierzyć ich skuteczność.',
                    labelOthers:
                        'Inne. Nieklasyfikowane pliki cookie to pliki cookie, które nie należą do żadnej innej kategorii lub są w trakcie kategoryzacji.',
                    buttonApply: 'Zastosuj',
                    buttonCancel: 'Anuluj',
                },
                portugal: {
                    'cookie-consent-msg':
                        'Este site utiliza cookies e serviços de terceiros.',
                    'cookie-consent-more-info': 'Mais informações',
                    'button-accept-all': 'Aceitar Todos',
                    'button-accept-necessary': 'Aceitar Necessários',
                    labelCustomize: 'Personalizar',
                    popupCookieH3: 'Configurações de Cookies',
                    labelNecessary:
                        'Necessário. Esses rastreadores são usados para atividades estritamente necessárias para operar ou fornecer o serviço solicitado por você e, portanto, não exigem o seu consentimento.',
                    labelPreferences:
                        'Preferências. Esses rastreadores permitem interações básicas e funcionalidades que permitem acessar recursos selecionados do nosso serviço e facilitar a comunicação conosco.',
                    labelStatistics:
                        'Estatísticas. Esses rastreadores nos ajudam a medir o tráfego e analisar seu comportamento para melhorar nosso serviço.',
                    labelMarketing:
                        'Marketing. Esses rastreadores nos ajudam a fornecer anúncios personalizados ou conteúdo de marketing para você e medir seu desempenho.',
                    labelOthers:
                        'Outros. Os cookies não classificados são cookies que não pertencem a nenhuma outra categoria ou estão em processo de categorização.',
                    buttonApply: 'Aplicar',
                    buttonCancel: 'Cancelar',
                },
                romania: {
                    'cookie-consent-msg':
                        'Acest site utilizează cookie-uri și servicii terțe.',
                    'cookie-consent-more-info': 'Mai multe informații',
                    'button-accept-all': 'Acceptă toate',
                    'button-accept-necessary': 'Acceptă necesare',
                    labelCustomize: 'Personalizați',
                    popupCookieH3: 'Setări cookie-uri',
                    labelNecessary:
                        'Necesare. Aceste urmăritori sunt folosiți pentru activități strict necesare pentru a opera sau a furniza serviciul solicitat de la noi și, prin urmare, nu necesită consimțământul tău.',
                    labelPreferences:
                        'Preferințe. Acești urmăritori permit interacțiuni și funcționalități de bază care vă permit să accesați funcții selectate ale serviciului nostru și să vă facilitați comunicarea cu noi.',
                    labelStatistics:
                        'Statistici. Acești urmăritori ne ajută să măsurăm traficul și să analizăm comportamentul dvs. pentru a îmbunătăți serviciul nostru.',
                    labelMarketing:
                        'Marketing. Acești urmăritori ne ajută să furnizăm reclame personalizate sau conținut de marketing și să măsurăm performanța acestora.',
                    labelOthers:
                        'Altele. Cookie-urile neclasificate sunt cookie-uri care nu aparțin unei alte categorii sau sunt în proces de categorizare.',
                    buttonApply: 'Aplică',
                    buttonCancel: 'Anulează',
                },
                slovakia: {
                    'cookie-consent-msg':
                        'Tento web používa súbory cookie a služby tretích strán.',
                    'cookie-consent-more-info': 'Viac informácií',
                    'button-accept-all': 'Súhlasiť so všetkými',
                    'button-accept-necessary': 'Súhlasiť so základnými',
                    labelCustomize: 'Prispôsobiť',
                    popupCookieH3: 'Nastavenia súborov cookie',
                    labelNecessary:
                        'Základné. Tieto sledovače sa používajú pre činnosti, ktoré sú úplne nevyhnutné pre prevádzku alebo poskytnutie služby, ktorú ste od nás požadovali, a preto nevyžadujú váš súhlas.',
                    labelPreferences:
                        'Predvoľby. Tieto sledovače umožňujú základné interakcie a funkcionality, ktoré vám umožňujú prístup k vybraným funkciam našej služby a uľahčujú komunikáciu s nami.',
                    labelStatistics:
                        'Štatistiky. Tieto sledovače nám pomáhajú merať premávku a analyzovať váš správanie na zlepšenie našej služby.',
                    labelMarketing:
                        'Marketing. Tieto sledovače nám pomáhajú poskytovať vám personalizovanú reklamu alebo marketingový obsah a merať ich výkon.',
                    labelOthers:
                        'Ostatné. Nezaradené súbory cookie sú súbory cookie, ktoré nepatria do žiadnej inej kategórie alebo sú v procese kategorizácie.',
                    buttonApply: 'Použiť',
                    buttonCancel: 'Zrušiť',
                },
                slovenia: {
                    'cookie-consent-msg':
                        'Ta spletna stran uporablja piškotke in storitve tretjih oseb.',
                    'cookie-consent-more-info': 'Več informacij',
                    'button-accept-all': 'Sprejmi vse',
                    'button-accept-necessary': 'Sprejmi nujno',
                    labelCustomize: 'Prilagodi',
                    popupCookieH3: 'Nastavitve piškotkov',
                    labelNecessary:
                        'Nujno. Ti sledilci se uporabljajo za dejavnosti, ki so strogo potrebne za delovanje ali zagotavljanje storitve, ki ste jo zahtevali od nas, zato ne zahtevajo vašega soglasja.',
                    labelPreferences:
                        'Nastavitve. Ti sledilci omogočajo osnovne interakcije in funkcionalnosti, ki vam omogočajo dostop do izbranih funkcij naše storitve in olajšajo komunikacijo z nami.',
                    labelStatistics:
                        'Statistika. Ti sledilci nam pomagajo meriti promet in analizirati vaše vedenje za izboljšanje naše storitve.',
                    labelMarketing:
                        'Marketing. Ti sledilci nam pomagajo dostaviti personalizirane oglase ali marketinško vsebino ter meriti njihovo uspešnost.',
                    labelOthers:
                        'Drugo. Nepoznani piškotki so piškotki, ki ne spadajo v nobeno drugo kategorijo ali so v postopku kategorizacije.',
                    buttonApply: 'Uporabi',
                    buttonCancel: 'Prekliči',
                },
                spain: {
                    'cookie-consent-msg':
                        'Este sitio web utiliza cookies y servicios de terceros.',
                    'cookie-consent-more-info': 'Más información',
                    'button-accept-all': 'Aceptar todo',
                    'button-accept-necessary': 'Aceptar necesarias',
                    labelCustomize: 'Personalizar',
                    popupCookieH3: 'Configuración de cookies',
                    labelNecessary:
                        'Necesarias. Estos rastreadores se utilizan para actividades que son estrictamente necesarias para operar o entregar el servicio que nos has solicitado, por lo tanto, no requieren tu consentimiento.',
                    labelPreferences:
                        'Preferencias. Estos rastreadores permiten interacciones y funcionalidades básicas que te permiten acceder a características seleccionadas de nuestro servicio y facilitar la comunicación con nosotros.',
                    labelStatistics:
                        'Estadísticas. Estos rastreadores nos ayudan a medir el tráfico y analizar tu comportamiento para mejorar nuestro servicio.',
                    labelMarketing:
                        'Marketing. Estos rastreadores nos ayudan a ofrecerte anuncios personalizados o contenido de marketing, y medir su rendimiento.',
                    labelOthers:
                        'Otros. Las cookies no clasificadas son cookies que no pertenecen a ninguna otra categoría o están en proceso de clasificación.',
                    buttonApply: 'Aplicar',
                    buttonCancel: 'Cancelar',
                },
                sweden: {
                    'cookie-consent-msg':
                        'Denna webbplats använder cookies och tjänster från tredje part.',
                    'cookie-consent-more-info': 'Mer information',
                    'button-accept-all': 'Acceptera alla',
                    'button-accept-necessary': 'Acceptera nödvändiga',
                    labelCustomize: 'Anpassa',
                    popupCookieH3: 'Cookie-inställningar',
                    labelNecessary:
                        'Nödvändiga. Dessa spårare används för aktiviteter som är strikt nödvändiga för att driva eller leverera den tjänst du har begärt från oss och kräver därför inte ditt samtycke.',
                    labelPreferences:
                        'Inställningar. Dessa spårare möjliggör grundläggande interaktioner och funktionaliteter som låter dig komma åt utvalda funktioner i vår tjänst och underlätta kommunikationen med oss.',
                    labelStatistics:
                        'Statistik. Dessa spårare hjälper oss att mäta trafik och analysera ditt beteende för att förbättra vår tjänst.',
                    labelMarketing:
                        'Marknadsföring. Dessa spårare hjälper oss att leverera anpassade annonser eller marknadsföringsinnehåll till dig och mäta deras prestanda.',
                    labelOthers:
                        'Övriga. Ej klassificerade cookies är cookies som inte tillhör någon annan kategori eller är under kategorisering.',
                    buttonApply: 'Tillämpa',
                    buttonCancel: 'Avbryt',
                },
            }

            // Function for swapping dictionaries
            set_lang = function (dictionary) {
                jQuery('[data-translate]').text(function () {
                    var key = jQuery(this).data('translate')
                    if (dictionary.hasOwnProperty(key)) {
                        return dictionary[key]
                    }
                })
            }

            // Swap languages when menu changes
            jQuery('#lang').on('change', function () {
                var language = jQuery(this).val().toLowerCase()

                if (dictionary.hasOwnProperty(language)) {
                    set_lang(dictionary[language])
                }
            })

            // Set initial language to English
            set_lang(dictionary.english)
        })
        //END SET LANGUAGE
    }
}

new GdprWidgetRenderer()

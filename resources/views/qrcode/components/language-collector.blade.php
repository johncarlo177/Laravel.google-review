<script>
    (function() {
        const pattern = '{!! $composer->getLanguageCollectionUrl() !!}';

        if (!pattern.length) {
            return;
        }

        const url = pattern.replace('LANGUAGE', navigator.language);

        const tag = document.createElement('link');

        tag.rel = 'stylesheet';
        tag.href = url;
        tag.type = 'text/css';

        document.head.appendChild(tag);
        // 
    })()
</script>

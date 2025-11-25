<script>
    (function() {
        const pattern = '<?php echo $composer->getLanguageCollectionUrl(); ?>';

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
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/qrcode/components/language-collector.blade.php ENDPATH**/ ?>
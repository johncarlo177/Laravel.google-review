<script>
    (function() {
        const VCARD_STRING = `{!! $generator->vcard() !!}`;

        const VCARD_FILE_NAME = '{{ $generator->vcardFileName() }}';

        function downloadFile(text, filename) {
            // 
            var element = document.createElement('a');

            element.setAttribute('href', 'data:text/x-vcard;charset=utf-8,' + encodeURIComponent(text));

            element.setAttribute('download', filename);

            element.style.display = 'none';

            document.body.appendChild(element);

            element.click();

            document.body.removeChild(element);
        }

        // This function forces Chrome on iOS to download the file
        // rather than opening the contact dialog.
        function download(content, mimeType, filename) {
            const a = document.createElement('a') // Create "a" element
            const blob = new Blob([content], {
                type: mimeType
            }) // Create a blob (file-like object)
            const url = URL.createObjectURL(blob) // Create an object URL from blob
            a.setAttribute('href', url) // Set "a" element link
            a.setAttribute('download', filename) // Set download filename
            a.click() // Start downloading

            setTimeout(() => {
                URL.revokeObjectURL(url)
            }, 100);
        }

        function $(selector) {
            return document.querySelector(selector);
        }

        function $$(selector) {
            return Array.from(
                document.querySelectorAll(selector)
            );
        }

        function downloadVcard() {
            downloadFile(VCARD_STRING, VCARD_FILE_NAME);
        }

        function main() {
            $$('.add-contact').forEach(button => {
                button.addEventListener('click', downloadVcard)
            })

            document.addEventListener('vcard-file-generator::request-download', downloadVcard);
        }

        document.addEventListener('DOMContentLoaded', main)
    })();
</script>

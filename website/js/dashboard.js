document.addEventListener('DOMContentLoaded', function() {
    const versionSelect = document.getElementById('version-select');
    const freeDownloadButton = document.getElementById('free-download-button');

    if (versionSelect) {
        versionSelect.addEventListener('change', function() {
            if (this.value === 'free') {
                freeDownloadButton.style.display = 'block';
            } else {
                freeDownloadButton.style.display = 'none';
            }
        });
    }
});
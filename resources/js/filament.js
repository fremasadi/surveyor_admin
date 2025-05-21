// File: resources/js/app.js atau resources/js/filament.js

// Tambahkan event listener untuk download file
document.addEventListener('livewire:init', () => {
    Livewire.on('download-file', ({ url }) => {
        // Membuat elemen anchor tersembunyi untuk download
        const link = document.createElement('a');
        link.href = url;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
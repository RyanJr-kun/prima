import $ from 'jquery';
import 'select2/dist/css/select2.css'; // Import CSS langsung di sini agar tidak lupa

// 1. DEFINISIKAN JQUERY GLOBAL DULU
// Kita tangkap jQuery dan pasang ke window sebelum memanggil plugin apa pun
window.$ = window.jQuery = $;

// 2. FUNGSI INISIALISASI ASYNC
// Kita bungkus dalam fungsi async untuk mengontrol urutan loading
const initSelect2 = async () => {
    try {
        // Langkah A: Load Select2 Core
        // Kita gunakan await import() agar kode di bawahnya tidak jalan sebelum ini selesai
        await import('select2');

        // Langkah B: Cek apakah Select2 berhasil nempel ke jQuery
        if (!$.fn.select2) {
            console.error('Select2 gagal dimuat. Mencoba inisialisasi manual...');
            // Fallback manual jika perlu (jarang terjadi jika langkah A sukses)
            const s2 = await import('select2');
            if (typeof s2.default === 'function') s2.default($);
        }

        // Langkah C: Load Bahasa Indonesia (Hanya jika Select2 sudah siap)
        // PENTING: Kita cek dulu ketersediaan 'amd' yang dicari oleh file bahasa
        if ($.fn.select2 && $.fn.select2.amd) {
            await import('select2/dist/js/i18n/id.js');
            console.log('Select2 & Bahasa Indonesia siap digunakan');
        } else {
            console.warn('Select2 dimuat tapi fitur AMD tidak tersedia. Bahasa default (EN) akan digunakan.');
        }

        // Langkah D: Event Global untuk memberi tahu script lain (opsional)
        // document.dispatchEvent(new Event('select2:loaded'));

    } catch (e) {
        console.error('Gagal memuat Select2:', e);
    }
};

// Jalankan fungsi
initSelect2();

// Export jQuery agar tetap bisa dipakai import lain jika perlu
export { $ };
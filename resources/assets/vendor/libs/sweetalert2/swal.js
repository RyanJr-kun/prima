import Swal from 'sweetalert2/dist/sweetalert2.js'
import 'sweetalert2/src/sweetalert2.scss';

// Definisi fungsi konfirmasi standar
const swalConfirm = ({
    title = 'Apakah Anda Yakin?',
    text = 'Data yang dihapus tidak dapat dikembalikan!',
    confirmText = 'Ya, Hapus!',
    cancelText = 'Batal',
    icon = 'warning'
} = {}) => {
    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    });
};

// Definisi fungsi sukses standar
const swalSuccess = (text) => {
    return Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: text,
        showConfirmButton: false,
        timer: 1500,
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
};

// Assign ke window agar bisa dipanggil di tag <script> Blade
window.Swal = Swal;
window.swalConfirm = swalConfirm;
window.swalSuccess = swalSuccess;

export { Swal, swalConfirm, swalSuccess };
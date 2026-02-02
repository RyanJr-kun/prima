<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Prodi;
use Hamcrest\NullDescription;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil Role yang sudah dibuat
        $roleAdmin = Role::where('name', 'admin')->first();
        $roleBaak = Role::where('name', 'baak')->first();
        $roleDirektur = Role::where('name', 'direktur')->first();
        $roleWadir1 = Role::where('name', 'wadir1')->first();
        $roleWadir2 = Role::where('name', 'wadir2')->first();
        $roleWadir3 = Role::where('name', 'wadir3')->first();
        $roleKaprodi = Role::where('name', 'kaprodi')->first();
        $roleDosen = Role::where('name', 'dosen')->first();

        $admin = User::firstOrCreate([
            'email' => 'admin@poltek.ac.id'
        ], [
            'name' => 'Super Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleAdmin) $admin->assignRole($roleAdmin);

        $direktur = User::firstOrCreate([
            'email' => 'suci.purwandari@poltek.ac.id'
        ], [
            'name' => 'Ir. Suci Purwandari, MM, Ph.D',
            'username' => 'suci.purwandari',
            'nidn' => '0630076601',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleDirektur) $direktur->assignRole($roleDirektur, $roleDosen);

        $wadir1 = User::firstOrCreate([
            'email' => 'edy.susena@poltek.ac.id'
        ], [
            'name' => 'Edy Susena, M.Kom',
            'nidn' => '0623097702',
            'username' => 'edy.susena',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleWadir1) $wadir1->assignRole($roleWadir1, $roleDosen);

        $wadir2 = User::firstOrCreate([
            'email' => 'canggih.ajika@poltek.ac.id'
        ], [
            'name' => 'Canggih Ajika P, M.Kom, Ph.D',
            'username' => 'canggih.ajika',
            'nidn' => '0623097703',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleWadir2) $wadir2->assignRole($roleWadir2, $roleDosen);

        $wadir3 = User::firstOrCreate([
            'email' => 'wachid.yahya@poltek.ac.id'
        ], [
            'name' => 'Wachid Yahya, M.Pd., Ph.D',
            'username' => 'wachid.yahya',
            'nidn' => '0623097702',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleWadir3) $wadir3->assignRole($roleWadir3, $roleDosen);


        $kaprodiList = [
            ['Dody Mulyanto, MM', 'dody.mulyanto'],
            ['Agustyarum Pradiska Budi, ME', 'agustyarum.pradiska'],
            ['Markus Utomo Sukendar, S.Sos., M.I.Kom', 'markus.utomo'],
            ['Wahyu Tri Hastiningsih, S.Pd., MM.', 'wahyu.tri'],
            ['Dwi Iskandar, M.Kom', 'dwi.iskandar'],
            ['Sudiro, ST, M.Si', 'sudiro'],
            ['apt. Iin Suhesti, M.Farm.', 'iin.suhesti'],
            ['Emma Ismawatie, S.ST., M.Kes', 'emma.ismawatie'],
            ['Frestiany Regina Putri, M.Kom', 'frestiany.regina'],
        ];

        $dosenBiasaList = [
            ['Norma Puspitasari, M.Pd', 'norma.puspita'],
            ['Edy Susanto, M.Kom', 'edy.susanto'],
            ['Sri Wulandari, S.Si., M.M., M.Kom', 'sri.wulandari'],
            ['Suparmini, M,Pd.I', 'suparmini'],
            ['Muhammad Nurfauzi Sahono, M.Kom', 'muhammad.nurfauzi'],
            ['Wasis Waluyo, S.Kom', 'wasis.waluyo'],
            ['Nawangsih Eddyna Putri, S.Hum, M.A.', 'nawangsih.edynna'],
            ['Agung Wibiyanto, SS., MM', 'agung.wibiyanto'],
            ['Makmun Syaifudin, M.Pd', 'makmun.syaifudin'],
            ['Utomo Ramelan, ST, M.Pd', 'utomo.ramelan'],
            ['Onery Andy Saputra, M.Pd, M.T', 'onery.andy'],
            ['Dewandono Bayu Seto, M.T', 'dewandono.bayu'],
            ['Arif Surono, ST, MT', 'arif.surono'],
            ['Drs Sudarmaji, M.M', 'sudarmaji'],
            ['Perguruan Tinggi (PT)', 'perguruan.tinggi'],
            ['Fendi (KIA MOTOR)', 'fendi.kia'],
            ['DAIHATSU ZIRANG', 'daihatsu.zirang'],
            ['Yulita Maulani, S.Tr.Kes., M.Kes', 'yulita.maulani'],
            ['Yulia Ratna Dewi, S.Tr.A.K., M.Biomed', 'yulia.ratna'],
            ['Resi Tondho Jimat, S.Tr. M.Kes M.Kes', 'resi.tondho'],
            ['Arum Kusuma Putri, S.Tr. Kes., M.Kes', 'arum.kusuma'],
            ['Dr. Ratna Susanti, S.S., M.Pd', 'ratna.susanti'],
            ['Sri Widiyanti, M.Kom', 'sri.widiyanti'],
            ['Yoki Setyaji, S.ST., M.Sc', 'yoki.setyaji'],
            ['Hendro Prayitno, S.Tr. kes., M.Sc', 'hendro.prayitno'],
            ['Eko Sumargiyanto, S.Tr.Kes', 'eko.sumargiyanto'],
            ['Renita Yuliana, M.Sc', 'renita.yuliana'],
            ['dr. Astri Riana Sari', 'astri.riana'],
            ['Maharani Ayuning Tyas, M.I.Kom', 'maharani.ayuning'],
            ['A.Anditha Sari, M.I.Kom', 'anditha.sari'],
            ['Eni Lestari, M.I.Kom ', 'eni.lestari'],
            ['Widiyanto, S.S.', 'widiyanto'],
            ['Dr. Agus Susanto, S.Th., M.I.Kom', 'agus.susanto'],
            ['Jahid Syaifullah, M.I.Kom', 'jahid.syaifullah'],
            ['Nugraha Andi Kusuma, S.E.', 'nugraha.andi'],
            ['Laily Malatyas Laydievasari, S.T.', 'laily.malatyas'],
            ['Wahyu Ratri Sukmaningsih, M.KM.', 'wahyu.ratri'],
            ['Wahyu Wijaya Widiyanto, M.Kom', 'wahyu.wijaya'],
            ['Sri Suparti, S.KM., M.Kes', 'sri.suparti'],
            ['Rizka Licia, S.KM., M.PH.', 'rizka.licia'],
            ['Ahmad Sunandar, S.S., M.Pd', 'ahmad.sunandar'],
            ['Resia Perwirani, S.MIK., M.PH', 'resia.perwirani'],
            ['Aries Widiyoko, S.Kom., M.M', 'aries.widiyoko'],
            ['Sinta Novratilova, S.KM., M.PH', 'sinta.novratilova'],
            ['Faizqinthar Bima Nugraha, S.Kes., M.KM', 'faizqinthar.bima'],
            ['Aliansi Wicaksono, S.Tr.RMIK', 'aliansi.wicaksono'],
            ['Dr. Matnuri, S.H., M.Kn', 'matnuri'],
            ['Miswanto, S.Tr.RMIK', 'miswanto'],
            ['Sholahuddin Sanjaya, S.KM., M.PH', 'sholahuddin.sanjaya'],
            ['apt. Riyan Setiyanto, M.Farm', 'riyan.setiyanto'],
            ['Suryanto Nugroho, M.Kom', 'suryanto.nugroho'],
            ['Nuryati, S.Far, M.PH', 'nuryati'],
            ['Dr. Tedy Hidayat, S.ST.RMIK., M.MRS', 'tedy.hidayat'],
            ['Yuliyani Siyamtining Tyas, M.Cs', 'yuliyani.siyamtining'],
            ['Alek Dwi Santoso, S.ST.Par', 'alek.dwi'],
            ['Kusmiyatun (The Sunan)', 'kusmiyatun'],
            ['Prasiwi Citra Resmi, M.Par', 'prasiwi.citra'],
            ['Yohanes Martono Widagdo, S.ST.MM.Par', 'yohanes.martono'],
            ['Ari Setiawan (The Sunan)', 'ari.setiawan'],
            ['HITA (Aldi)', 'hita.aldi'],
            ['Ichwan Prastowo, S.Pd., M.Par.', 'ichwan.prastowo'],
            ['Topan Sulityanto, S.ST.Par', 'topan.sulityanto'],
            ['Arie Restama, MM.Par', 'arie.restama'],
            ['Dr. Johny Subarkah, S.E.,MM', 'johny.subarkah'],
            ['Dewi Amelia Lestari, S.Kom.,MM.Par', 'dewi.amelia'],
            ['Ersyafaat Huda, S.Kom.,MM.Par', 'ersyafaat.huda'],
            ['Aptika Oktaviana T.D., M.Si', 'aptika.oktaviana'],
            ['apt. Umi Nafisah, M.M., M.Sc', 'umi.nafisah'],
            ['apt. Vania Santika, M.Pharm.Sci', 'vania.santika'],
            ['Purwaningsih, M.Farm', 'purwaningsih'],
            ['Istiara Subekti, M.Farm', 'istiara.subekti'],
            ['apt. Raihana Nurul Izzah, M.Pharm.', 'raihana.nurul'],
            ['Adnan Nur Avif, M.Sc', 'adnan.nur'],
            ['Diyan Sakti P., M.Farm', 'diyan.sakti'],
            ['apt. Ester Dwi Antari, M.Farm', 'ester.dwi'],
            ['apt. Annora Rizky Amalia, M.Farm', 'annora.rizky'],
            ['apt. Yunita Dian P.S., M.Farm', 'yunita.dian'],
            ['apt. Diski Wahyu Wijianto, M.Farm', 'diski.wahyu'],
            ['Fitri Nur Aini, M.Si', 'firti.nur'],
            ['Faizqintar Bima Nugraha, S.Kes., M.K.M', 'faizqintar.bima'],
            ['Muhammad Kais, SE, M.Ak', 'muhammad.kais'],
            ['Nur Ilham Febrianto, S.Ak., M.Ak', 'nur.ilham'],
            ['Bhakti Sri Rahayu, S.Pd., M.M.', 'bhakti.rahayu'],
            ['Ryanto, M.M.', 'ryanto'],
            ['Satria Agung Wibowo, SE, MM', 'satria.agung'],
            ['Aditya Ardian', 'adit'],
            ['Anista Yulia Ratnawati, S.Kom, M.M.', 'anista.yulia'],
            ['Enil Lestari, M.I.Kom', 'enil.lestari'],
            ['Suwarmin Mulyadi, S.Sos, M.M.', 'suwarmi.mulyadi'],
            ['Adnan Nur Afiv, M.Sc', 'adnan.nur'],
            ['apt. Dewi Weni Sari, M.Farm', 'dewi.weni'],
            ['Ersyfaat Huda, S.Kom.,MM.Par', 'ersyfaat.huda'],
            ['Muhammad Haris Nasrulloh, M.Pd', 'haris.nasrulloh'],
            ['Galih Ayu Sartika, SE., M.Si, Ph.D', 'galih.ayu'],

        ];

        // 1. Loop untuk Kaprodi (Dapat 2 role: Dosen & Kaprodi)
        foreach ($kaprodiList as $data) {
            $user = User::firstOrCreate(
                ['email' => $data[1] . '@poltekindonusa.ac.id'],
                [
                    'name'     => $data[0],
                    'username' => $data[1],
                    'password' => Hash::make('password'),
                    'nidn'     => null,
                ]
            );

            // Pastikan role sudah ada di table roles
            $user->assignRole('dosen');
            $user->assignRole('kaprodi');
        }

        // 2. Loop untuk Dosen Biasa
        foreach ($dosenBiasaList as $data) {
            $user = User::firstOrCreate(
                ['email' => $data[1] . '@poltekindonusa.ac.id'],
                [
                    'name'     => $data[0],
                    'username' => $data[1],
                    'password' => Hash::make('password'),
                    'nidn'     => null,
                ]
            );

            $user->assignRole('dosen');
        }

        $baak = User::firstOrCreate([
            'email' => 'baak@poltekindonusa.ac.id'
        ], [
            'name' => 'Dewi Amelia, M.Kom',
            'username' => 'baak',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleBaak) $baak->assignRole($roleBaak);
    }
}

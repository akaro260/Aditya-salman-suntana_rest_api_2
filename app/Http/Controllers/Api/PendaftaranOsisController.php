<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PendaftaranOsisResource;
use App\Models\PendaftaranOsis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PendaftaranOsisController extends Controller
{
    /**
     * Menampilkan daftar seluruh pendaftaran OSIS.
     */
    public function index(): AnonymousResourceCollection
    {
        // TODO LATIHAN L1:
        // GET daftar pendaftaran seharusnya menyertakan data kelas dan jenis kelamin melalui relasi siswa.
        // Jalankan GET /api/pendaftaran-osis di Postman dan periksa object siswa pada setiap item.
        // Mengapa data kelas dan jenis kelamin belum muncul pada daftar, padahal pada GET detail sudah lengkap?
        $pendaftaran = PendaftaranOsis::with(
            'siswa.kelas',
            'siswa.jenisKelamin',
        )->get();

        return PendaftaranOsisResource::collection($pendaftaran);
    }

    /**
     * Menampilkan detail satu pendaftaran OSIS berdasarkan ID.
     */
    public function show(string|int $id): JsonResponse|PendaftaranOsisResource
    {
        $pendaftaran = PendaftaranOsis::with(['siswa.kelas', 'siswa.jenisKelamin'])->find($id);

        if (!$pendaftaran) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
            ], 404);
        }

        return new PendaftaranOsisResource($pendaftaran);
    }

    /**
     * Menyimpan data pendaftaran OSIS baru.
     */
    public function store(Request $request): JsonResponse
    {

$validated = $request->validate([
    'siswa_id' => [
        'required',
        'integer',
        'exists:siswa,id',
        'unique:pendaftaran_osis,siswa_id',
    ],
    'pilihan_bidang' => [
        'required',
        'string',
        'in:kepemimpinan,keagamaan,olahraga,seni',
    ],
    'motivasi' => [
        'required',
        'string',
        'min:20',
        'max:300',
    ],
], [
    'siswa_id.required' => 'Siswa wajib dipilih.',
    'siswa_id.integer' => 'ID siswa harus berupa angka.',
    'siswa_id.exists' => 'Siswa tidak ditemukan.',
    'siswa_id.unique' => 'Siswa sudah terdaftar sebagai pendaftar OSIS.',
    'pilihan_bidang.required' => 'Pilihan bidang wajib diisi.',
    'pilihan_bidang.in' => 'Pilihan bidang tidak valid.',
    'motivasi.required' => 'Motivasi wajib diisi.',
    'motivasi.min' => 'Motivasi minimal 20 karakter.',
    'motivasi.max' => 'Motivasi maksimal 300 karakter.',
]);

        $pendaftaran = PendaftaranOsis::create([
            'siswa_id' => $validated['siswa_id'],
            'pilihan_bidang' => $validated['pilihan_bidang'],
            'motivasi' => $validated['motivasi'],
        ]);

        $pendaftaran->load(['siswa.kelas', 'siswa.jenisKelamin']);

        return response()->json([
            'message' => 'Pendaftaran berhasil dibuat.',
            'data' => new PendaftaranOsisResource($pendaftaran),
        ], 201);
    }

    /**
     * Memperbarui data pendaftaran OSIS yang sudah ada.
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $pendaftaran = PendaftaranOsis::find($id);

        if (!$pendaftaran) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'siswa_id' => [
                'required',
                'integer',
                'exists:siswa,id',
                'unique:pendaftaran_osis,siswa_id,' . $pendaftaran->id,
            ],
            'pilihan_bidang' => [
                'required',
                'string',
                'in:kepemimpinan,keagamaan,olahraga,seni',
            ],
            'motivasi' => [
                'required',
                'string',
                'min:20',
                'max:300',
            ],
        ]);
        

        // TODO LATIHAN L8:
        // Request PUT yang valid seharusnya memperbarui semua data pendaftaran (termasuk pilihan_bidang dan motivasi) ke database.
        // Kirim PUT valid dengan mengubah pilihan_bidang dan motivasi, lalu periksa isi tabel di database atau lakukan GET detail.
        // Mengapa nilai pilihan_bidang dan motivasi di database tidak berubah meskipun API mengembalikan pesan berhasil?
        // Field apa saja yang diteruskan pada proses update model?

        $pendaftaran->update([
            'siswa_id' => $validated['siswa_id'],
            'pilihan_bidang' => $validated['pilihan_bidang'],
            'motivasi' => $validated['motivasi'],
        ]);


        $pendaftaran->load(['siswa.kelas', 'siswa.jenisKelamin']);

        return response()->json([
            'message' => 'Pendaftaran berhasil diperbarui.',
            'data' => new PendaftaranOsisResource($pendaftaran),
        ], 200);
    }

    /**
     * Menghapus data pendaftaran OSIS.
     */
    public function destroy(string|int $id): JsonResponse|Response
    {
        $pendaftaran = PendaftaranOsis::find($id);

        if (!$pendaftaran) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
            ], 404);
        }

        $pendaftaran->delete();

        return response()->noContent();
    }
}

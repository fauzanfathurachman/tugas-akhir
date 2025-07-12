<?php
/**
 * PSB Online - Validation Rules Configuration
 * 
 * This file contains validation rules for forms and data validation
 * throughout the PSB Online system.
 * 
 * @author PSB Online Team
 * @version 1.0
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

/**
 * Validation Rules Array
 * 
 * Contains validation rules for different forms and data types
 */
$VALIDATION_RULES = [
    
    // =====================================================
    // USER MANAGEMENT VALIDATION
    // =====================================================
    
    'user_login' => [
        'username' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 3,
            'max_length' => 50,
            'pattern' => '/^[a-zA-Z0-9_]+$/',
            'message' => 'Username harus 3-50 karakter dan hanya boleh berisi huruf, angka, dan underscore'
        ],
        'password' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 6,
            'message' => 'Password minimal 6 karakter'
        ]
    ],
    
    'user_create' => [
        'username' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 3,
            'max_length' => 50,
            'pattern' => '/^[a-zA-Z0-9_]+$/',
            'unique' => 'users.username',
            'message' => 'Username harus 3-50 karakter dan hanya boleh berisi huruf, angka, dan underscore'
        ],
        'password' => [
            'required' => true,
            'type' => 'string',
            'min_length' => config('PASSWORD_MIN_LENGTH', 8),
            'pattern' => config('PASSWORD_REQUIRE_SPECIAL', true) ? '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/' : null,
            'message' => 'Password harus memenuhi kriteria keamanan'
        ],
        'email' => [
            'required' => true,
            'type' => 'email',
            'unique' => 'users.email',
            'message' => 'Email harus valid dan belum terdaftar'
        ],
        'role' => [
            'required' => true,
            'type' => 'enum',
            'values' => ['admin', 'operator', 'viewer'],
            'message' => 'Role harus dipilih'
        ],
        'nama_lengkap' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Nama lengkap harus 2-100 karakter'
        ]
    ],
    
    'user_update' => [
        'id' => [
            'required' => true,
            'type' => 'integer',
            'exists' => 'users.id',
            'message' => 'User tidak ditemukan'
        ],
        'username' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 3,
            'max_length' => 50,
            'pattern' => '/^[a-zA-Z0-9_]+$/',
            'unique' => 'users.username',
            'ignore' => 'id',
            'message' => 'Username harus 3-50 karakter dan hanya boleh berisi huruf, angka, dan underscore'
        ],
        'email' => [
            'required' => true,
            'type' => 'email',
            'unique' => 'users.email',
            'ignore' => 'id',
            'message' => 'Email harus valid dan belum terdaftar'
        ],
        'role' => [
            'required' => true,
            'type' => 'enum',
            'values' => ['admin', 'operator', 'viewer'],
            'message' => 'Role harus dipilih'
        ],
        'nama_lengkap' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Nama lengkap harus 2-100 karakter'
        ]
    ],
    
    // =====================================================
    // CALON SISWA VALIDATION
    // =====================================================
    
    'calon_siswa_create' => [
        'nomor_daftar' => [
            'required' => true,
            'type' => 'string',
            'pattern' => '/^PSB-\d{4}-\d{3}$/',
            'unique' => 'calon_siswa.nomor_daftar',
            'message' => 'Nomor daftar harus sesuai format PSB-YYYY-XXX'
        ],
        'nama_lengkap' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Nama lengkap harus 2-100 karakter'
        ],
        'tempat_lahir' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 50,
            'message' => 'Tempat lahir harus 2-50 karakter'
        ],
        'tanggal_lahir' => [
            'required' => true,
            'type' => 'date',
            'format' => 'Y-m-d',
            'min_date' => '1990-01-01',
            'max_date' => date('Y-m-d', strtotime('-10 years')),
            'message' => 'Tanggal lahir harus valid dan minimal 10 tahun yang lalu'
        ],
        'jenis_kelamin' => [
            'required' => true,
            'type' => 'enum',
            'values' => ['L', 'P'],
            'message' => 'Jenis kelamin harus dipilih'
        ],
        'agama' => [
            'required' => true,
            'type' => 'string',
            'max_length' => 20,
            'message' => 'Agama harus diisi'
        ],
        'alamat' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 10,
            'max_length' => 500,
            'message' => 'Alamat harus 10-500 karakter'
        ],
        'telepon' => [
            'required' => true,
            'type' => 'string',
            'pattern' => '/^(\+62|62|0)8[1-9][0-9]{6,9}$/',
            'message' => 'Nomor telepon harus valid'
        ],
        'email' => [
            'required' => false,
            'type' => 'email',
            'message' => 'Email harus valid jika diisi'
        ],
        'asal_sekolah' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Asal sekolah harus 2-100 karakter'
        ],
        'nisn' => [
            'required' => true,
            'type' => 'string',
            'pattern' => '/^\d{10}$/',
            'unique' => 'calon_siswa.nisn',
            'message' => 'NISN harus 10 digit angka'
        ],
        'nama_ayah' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Nama ayah harus 2-100 karakter'
        ],
        'pekerjaan_ayah' => [
            'required' => true,
            'type' => 'string',
            'max_length' => 50,
            'message' => 'Pekerjaan ayah harus diisi'
        ],
        'nama_ibu' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Nama ibu harus 2-100 karakter'
        ],
        'pekerjaan_ibu' => [
            'required' => true,
            'type' => 'string',
            'max_length' => 50,
            'message' => 'Pekerjaan ibu harus diisi'
        ],
        'penghasilan_ortu' => [
            'required' => true,
            'type' => 'numeric',
            'min' => 0,
            'max' => 999999999999,
            'message' => 'Penghasilan orang tua harus valid'
        ]
    ],
    
    'calon_siswa_update' => [
        'id' => [
            'required' => true,
            'type' => 'integer',
            'exists' => 'calon_siswa.id',
            'message' => 'Calon siswa tidak ditemukan'
        ],
        'nomor_daftar' => [
            'required' => true,
            'type' => 'string',
            'pattern' => '/^PSB-\d{4}-\d{3}$/',
            'unique' => 'calon_siswa.nomor_daftar',
            'ignore' => 'id',
            'message' => 'Nomor daftar harus sesuai format PSB-YYYY-XXX'
        ],
        'nama_lengkap' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Nama lengkap harus 2-100 karakter'
        ],
        'tempat_lahir' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 50,
            'message' => 'Tempat lahir harus 2-50 karakter'
        ],
        'tanggal_lahir' => [
            'required' => true,
            'type' => 'date',
            'format' => 'Y-m-d',
            'min_date' => '1990-01-01',
            'max_date' => date('Y-m-d', strtotime('-10 years')),
            'message' => 'Tanggal lahir harus valid dan minimal 10 tahun yang lalu'
        ],
        'jenis_kelamin' => [
            'required' => true,
            'type' => 'enum',
            'values' => ['L', 'P'],
            'message' => 'Jenis kelamin harus dipilih'
        ],
        'agama' => [
            'required' => true,
            'type' => 'string',
            'max_length' => 20,
            'message' => 'Agama harus diisi'
        ],
        'alamat' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 10,
            'max_length' => 500,
            'message' => 'Alamat harus 10-500 karakter'
        ],
        'telepon' => [
            'required' => true,
            'type' => 'string',
            'pattern' => '/^(\+62|62|0)8[1-9][0-9]{6,9}$/',
            'message' => 'Nomor telepon harus valid'
        ],
        'email' => [
            'required' => false,
            'type' => 'email',
            'message' => 'Email harus valid jika diisi'
        ],
        'asal_sekolah' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Asal sekolah harus 2-100 karakter'
        ],
        'nisn' => [
            'required' => true,
            'type' => 'string',
            'pattern' => '/^\d{10}$/',
            'unique' => 'calon_siswa.nisn',
            'ignore' => 'id',
            'message' => 'NISN harus 10 digit angka'
        ],
        'nama_ayah' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Nama ayah harus 2-100 karakter'
        ],
        'pekerjaan_ayah' => [
            'required' => true,
            'type' => 'string',
            'max_length' => 50,
            'message' => 'Pekerjaan ayah harus diisi'
        ],
        'nama_ibu' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Nama ibu harus 2-100 karakter'
        ],
        'pekerjaan_ibu' => [
            'required' => true,
            'type' => 'string',
            'max_length' => 50,
            'message' => 'Pekerjaan ibu harus diisi'
        ],
        'penghasilan_ortu' => [
            'required' => true,
            'type' => 'numeric',
            'min' => 0,
            'max' => 999999999999,
            'message' => 'Penghasilan orang tua harus valid'
        ]
    ],
    
    // =====================================================
    // PENDAFTARAN VALIDATION
    // =====================================================
    
    'pendaftaran_create' => [
        'calon_siswa_id' => [
            'required' => true,
            'type' => 'integer',
            'exists' => 'calon_siswa.id',
            'message' => 'Calon siswa tidak ditemukan'
        ],
        'tahun_ajaran' => [
            'required' => true,
            'type' => 'string',
            'pattern' => '/^\d{4}\/\d{4}$/',
            'message' => 'Tahun ajaran harus sesuai format YYYY/YYYY'
        ],
        'jalur_pendaftaran' => [
            'required' => true,
            'type' => 'enum',
            'values' => ['reguler', 'prestasi', 'afirmasi', 'perpindahan'],
            'message' => 'Jalur pendaftaran harus dipilih'
        ],
        'pilihan_jurusan' => [
            'required' => true,
            'type' => 'string',
            'max_length' => 50,
            'message' => 'Pilihan jurusan harus diisi'
        ],
        'nilai_un_matematika' => [
            'required' => false,
            'type' => 'numeric',
            'min' => 0,
            'max' => 100,
            'message' => 'Nilai UN Matematika harus 0-100'
        ],
        'nilai_un_ipa' => [
            'required' => false,
            'type' => 'numeric',
            'min' => 0,
            'max' => 100,
            'message' => 'Nilai UN IPA harus 0-100'
        ],
        'nilai_un_bindo' => [
            'required' => false,
            'type' => 'numeric',
            'min' => 0,
            'max' => 100,
            'message' => 'Nilai UN Bahasa Indonesia harus 0-100'
        ],
        'nilai_un_bing' => [
            'required' => false,
            'type' => 'numeric',
            'min' => 0,
            'max' => 100,
            'message' => 'Nilai UN Bahasa Inggris harus 0-100'
        ],
        'prestasi_akademik' => [
            'required' => false,
            'type' => 'string',
            'max_length' => 500,
            'message' => 'Prestasi akademik maksimal 500 karakter'
        ],
        'prestasi_non_akademik' => [
            'required' => false,
            'type' => 'string',
            'max_length' => 500,
            'message' => 'Prestasi non-akademik maksimal 500 karakter'
        ]
    ],
    
    // =====================================================
    // PENGUMUMAN VALIDATION
    // =====================================================
    
    'pengumuman_create' => [
        'judul' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 5,
            'max_length' => 200,
            'message' => 'Judul pengumuman harus 5-200 karakter'
        ],
        'konten' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 10,
            'max_length' => 10000,
            'message' => 'Konten pengumuman harus 10-10000 karakter'
        ],
        'jenis' => [
            'required' => true,
            'type' => 'enum',
            'values' => ['umum', 'seleksi', 'pengumuman'],
            'message' => 'Jenis pengumuman harus dipilih'
        ],
        'tanggal_publish' => [
            'required' => true,
            'type' => 'datetime',
            'format' => 'Y-m-d H:i:s',
            'min_date' => date('Y-m-d H:i:s'),
            'message' => 'Tanggal publish harus di masa depan'
        ],
        'tanggal_berakhir' => [
            'required' => false,
            'type' => 'datetime',
            'format' => 'Y-m-d H:i:s',
            'min_date' => date('Y-m-d H:i:s'),
            'message' => 'Tanggal berakhir harus di masa depan'
        ],
        'status' => [
            'required' => true,
            'type' => 'enum',
            'values' => ['draft', 'published', 'archived'],
            'message' => 'Status pengumuman harus dipilih'
        ]
    ],
    
    // =====================================================
    // PENGATURAN VALIDATION
    // =====================================================
    
    'pengaturan_create' => [
        'nama_setting' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 3,
            'max_length' => 100,
            'pattern' => '/^[a-zA-Z0-9_]+$/',
            'unique' => 'pengaturan.nama_setting',
            'message' => 'Nama setting harus 3-100 karakter dan hanya boleh berisi huruf, angka, dan underscore'
        ],
        'nilai' => [
            'required' => true,
            'type' => 'string',
            'max_length' => 1000,
            'message' => 'Nilai setting maksimal 1000 karakter'
        ],
        'deskripsi' => [
            'required' => false,
            'type' => 'string',
            'max_length' => 500,
            'message' => 'Deskripsi maksimal 500 karakter'
        ],
        'kategori' => [
            'required' => true,
            'type' => 'enum',
            'values' => ['sistem', 'pendaftaran', 'seleksi', 'pengumuman', 'email'],
            'message' => 'Kategori setting harus dipilih'
        ]
    ],
    
    // =====================================================
    // FILE UPLOAD VALIDATION
    // =====================================================
    
    'file_upload' => [
        'file' => [
            'required' => true,
            'type' => 'file',
            'max_size' => config('UPLOAD_MAX_SIZE', 5 * 1024 * 1024),
            'allowed_types' => config('UPLOAD_ALLOWED_TYPES', []),
            'message' => 'File harus valid dan sesuai dengan ketentuan'
        ]
    ],
    
    'image_upload' => [
        'image' => [
            'required' => true,
            'type' => 'image',
            'max_size' => 2 * 1024 * 1024, // 2MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
            'max_width' => config('IMAGE_MAX_WIDTH', 1920),
            'max_height' => config('IMAGE_MAX_HEIGHT', 1080),
            'message' => 'Gambar harus valid dan sesuai dengan ketentuan'
        ]
    ],
    
    'document_upload' => [
        'document' => [
            'required' => true,
            'type' => 'file',
            'max_size' => 10 * 1024 * 1024, // 10MB
            'allowed_types' => ['pdf', 'doc', 'docx'],
            'message' => 'Dokumen harus valid dan sesuai dengan ketentuan'
        ]
    ],
    
    // =====================================================
    // SEARCH VALIDATION
    // =====================================================
    
    'search' => [
        'keyword' => [
            'required' => false,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'message' => 'Kata kunci pencarian harus 2-100 karakter'
        ],
        'page' => [
            'required' => false,
            'type' => 'integer',
            'min' => 1,
            'default' => 1,
            'message' => 'Halaman harus angka positif'
        ],
        'limit' => [
            'required' => false,
            'type' => 'integer',
            'min' => 1,
            'max' => 100,
            'default' => 20,
            'message' => 'Limit harus 1-100'
        ]
    ],
    
    // =====================================================
    // PASSWORD CHANGE VALIDATION
    // =====================================================
    
    'password_change' => [
        'current_password' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 6,
            'message' => 'Password saat ini harus diisi'
        ],
        'new_password' => [
            'required' => true,
            'type' => 'string',
            'min_length' => config('PASSWORD_MIN_LENGTH', 8),
            'pattern' => config('PASSWORD_REQUIRE_SPECIAL', true) ? '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/' : null,
            'message' => 'Password baru harus memenuhi kriteria keamanan'
        ],
        'confirm_password' => [
            'required' => true,
            'type' => 'string',
            'match' => 'new_password',
            'message' => 'Konfirmasi password harus sama dengan password baru'
        ]
    ]
];

/**
 * Get validation rules for a specific form
 * 
 * @param string $form_name Form name
 * @return array|null
 */
function get_validation_rules($form_name)
{
    global $VALIDATION_RULES;
    return isset($VALIDATION_RULES[$form_name]) ? $VALIDATION_RULES[$form_name] : null;
}

/**
 * Get all validation rules
 * 
 * @return array
 */
function get_all_validation_rules()
{
    global $VALIDATION_RULES;
    return $VALIDATION_RULES;
}

/**
 * Check if validation rule exists
 * 
 * @param string $form_name Form name
 * @return bool
 */
function has_validation_rules($form_name)
{
    global $VALIDATION_RULES;
    return isset($VALIDATION_RULES[$form_name]);
}

/**
 * Get validation rule for specific field
 * 
 * @param string $form_name Form name
 * @param string $field_name Field name
 * @return array|null
 */
function get_field_validation_rule($form_name, $field_name)
{
    $rules = get_validation_rules($form_name);
    return isset($rules[$field_name]) ? $rules[$field_name] : null;
} 
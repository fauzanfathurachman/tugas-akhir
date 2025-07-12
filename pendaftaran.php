<?php
// Include database configuration
define('SECURE_ACCESS', true);
require_once 'config/config.php';

// Initialize variables
$errors = [];
$success = false;
$nomor_daftar = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        
        // Validate and sanitize input data
        $data = [
            'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
            'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
            'agama' => trim($_POST['agama'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'telepon' => trim($_POST['telepon'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'nama_ayah' => trim($_POST['nama_ayah'] ?? ''),
            'pekerjaan_ayah' => trim($_POST['pekerjaan_ayah'] ?? ''),
            'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
            'pekerjaan_ibu' => trim($_POST['pekerjaan_ibu'] ?? ''),
            'penghasilan_ortu' => (float)($_POST['penghasilan_ortu'] ?? 0),
            'asal_sekolah' => trim($_POST['asal_sekolah'] ?? ''),
            'alamat_sekolah' => trim($_POST['alamat_sekolah'] ?? ''),
            'nisn' => trim($_POST['nisn'] ?? ''),
            'tahun_lulus' => $_POST['tahun_lulus'] ?? ''
        ];
        
        // Validation
        if (empty($data['nama_lengkap'])) $errors[] = 'Nama lengkap harus diisi';
        if (empty($data['tempat_lahir'])) $errors[] = 'Tempat lahir harus diisi';
        if (empty($data['tanggal_lahir'])) $errors[] = 'Tanggal lahir harus diisi';
        if (empty($data['jenis_kelamin'])) $errors[] = 'Jenis kelamin harus dipilih';
        if (empty($data['agama'])) $errors[] = 'Agama harus diisi';
        if (empty($data['alamat'])) $errors[] = 'Alamat harus diisi';
        if (empty($data['telepon'])) $errors[] = 'Telepon harus diisi';
        if (empty($data['nama_ayah'])) $errors[] = 'Nama ayah harus diisi';
        if (empty($data['pekerjaan_ayah'])) $errors[] = 'Pekerjaan ayah harus diisi';
        if (empty($data['nama_ibu'])) $errors[] = 'Nama ibu harus diisi';
        if (empty($data['pekerjaan_ibu'])) $errors[] = 'Pekerjaan ibu harus diisi';
        if (empty($data['asal_sekolah'])) $errors[] = 'Asal sekolah harus diisi';
        if (empty($data['nisn'])) $errors[] = 'NISN harus diisi';
        if (empty($data['tahun_lulus'])) $errors[] = 'Tahun lulus harus diisi';
        
        // Validate NISN format (10 digits)
        if (!empty($data['nisn']) && !preg_match('/^\d{10}$/', $data['nisn'])) {
            $errors[] = 'NISN harus 10 digit angka';
        }
        
        // Validate phone number
        if (!empty($data['telepon']) && !preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $data['telepon'])) {
            $errors[] = 'Format nomor telepon tidak valid';
        }
        
        // Validate email if provided
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }
        
        // Check if NISN already exists
        if (!empty($data['nisn'])) {
            $existing = $db->fetchOne("SELECT id FROM calon_siswa WHERE nisn = ?", [$data['nisn']]);
            if ($existing) {
                $errors[] = 'NISN sudah terdaftar';
            }
        }
        
        // File upload validation
        $uploaded_files = [];
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_fields = ['foto', 'ijazah', 'kartu_keluarga'];
        foreach ($file_fields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$field];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                // Validate file type
                if (!in_array($file_extension, $allowed_types)) {
                    $errors[] = "File {$field} harus berformat JPG, JPEG, PNG, atau PDF";
                    continue;
                }
                
                // Validate file size
                if ($file['size'] > $max_size) {
                    $errors[] = "File {$field} maksimal 5MB";
                    continue;
                }
                
                // Generate unique filename
                $filename = uniqid() . '_' . $field . '.' . $file_extension;
                $upload_path = 'uploads/' . $filename;
                
                // Create uploads directory if not exists
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0755, true);
                }
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $uploaded_files[$field] = $upload_path;
                } else {
                    $errors[] = "Gagal mengupload file {$field}";
                }
            } else {
                $errors[] = "File {$field} harus diupload";
            }
        }
        
        // If no errors, save to database
        if (empty($errors)) {
            // Generate nomor daftar
            $tahun_ajaran = '2024/2025';
            $db->query("CALL GenerateNomorDaftar(?, @nomor_daftar)", [$tahun_ajaran]);
            $nomor_daftar = $db->fetchValue("SELECT @nomor_daftar");
            
            // Begin transaction
            $db->beginTransaction();
            
            try {
                // Insert calon_siswa
                $siswa_id = $db->insert(
                    "INSERT INTO calon_siswa (
                        nomor_daftar, nama_lengkap, tempat_lahir, tanggal_lahir, 
                        jenis_kelamin, agama, alamat, telepon, email, 
                        nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, 
                        penghasilan_ortu, asal_sekolah, nisn, foto, ijazah, 
                        kartu_keluarga, status_verifikasi, status_seleksi
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')",
                    [
                        $nomor_daftar, $data['nama_lengkap'], $data['tempat_lahir'], $data['tanggal_lahir'],
                        $data['jenis_kelamin'], $data['agama'], $data['alamat'], $data['telepon'], $data['email'],
                        $data['nama_ayah'], $data['pekerjaan_ayah'], $data['nama_ibu'], $data['pekerjaan_ibu'],
                        $data['penghasilan_ortu'], $data['asal_sekolah'], $data['nisn'], 
                        $uploaded_files['foto'] ?? null, $uploaded_files['ijazah'] ?? null, $uploaded_files['kartu_keluarga'] ?? null
                    ]
                );
                
                // Insert pendaftaran
                $db->insert(
                    "INSERT INTO pendaftaran (
                        calon_siswa_id, tahun_ajaran, jalur_pendaftaran, 
                        pilihan_jurusan, status_pendaftaran, tanggal_submit
                    ) VALUES (?, ?, 'reguler', 'IPA', 'submitted', NOW())",
                    [$siswa_id, $tahun_ajaran]
                );
                
                $db->commit();
                $success = true;
                
            } catch (Exception $e) {
                $db->rollback();
                $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
                
                // Clean up uploaded files
                foreach ($uploaded_files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        $errors[] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran PSB - MTs Ulul Albab</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .registration-container {
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #2563eb 0%, #06b6d4 100%);
            padding: 1.5rem;
            color: #fff;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background: #fbbf24;
            color: #222;
        }
        
        .step.completed .step-number {
            background: #10b981;
            color: #fff;
        }
        
        .step-label {
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
        }
        
        .form-container {
            padding: 2rem;
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .file-upload {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: border-color 0.2s;
            cursor: pointer;
        }
        
        .file-upload:hover {
            border-color: #2563eb;
        }
        
        .file-upload input[type="file"] {
            display: none;
        }
        
        .file-upload-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #2563eb 0%, #06b6d4 100%);
            color: #fff;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        
        .btn-success {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            color: #fff;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        
        .error-message {
            color: #dc2626;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        
        .success-container {
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #fff;
            font-size: 2rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .progress-steps {
                flex-direction: column;
                gap: 1rem;
            }
            
            .step {
                flex-direction: row;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-flex">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Logo MTs Ulul Albab" />
                <span>MTs Ulul Albab</span>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="pendaftaran.php" class="active">Pendaftaran</a></li>
                    <li><a href="#pengumuman">Pengumuman</a></li>
                    <li><a href="#cekstatus">Cek Status</a></li>
                    <li><a href="admin/login.php" class="btn-login">Login Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="registration-container">
        <?php if ($success): ?>
            <!-- Success Page -->
            <div class="success-container">
                <div class="success-icon">✓</div>
                <h2>Pendaftaran Berhasil!</h2>
                <p>Nomor Pendaftaran Anda: <strong><?php echo htmlspecialchars($nomor_daftar); ?></strong></p>
                <p>Silakan simpan nomor pendaftaran ini untuk mengecek status pendaftaran Anda.</p>
                <div class="btn-group" style="justify-content: center;">
                    <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
                    <a href="#cekstatus" class="btn btn-success">Cek Status</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Progress Bar -->
            <div class="progress-bar">
                <h2>Formulir Pendaftaran PSB</h2>
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Data Pribadi</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Data Orang Tua</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Data Pendidikan</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Upload Berkas</div>
                    </div>
                </div>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <form id="registrationForm" method="POST" enctype="multipart/form-data">
                    <!-- Step 1: Data Pribadi -->
                    <div class="form-step active" data-step="1">
                        <h3>Data Pribadi</h3>
                        
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap *</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tempat_lahir">Tempat Lahir *</label>
                                <input type="text" id="tempat_lahir" name="tempat_lahir" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir *</label>
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin *</label>
                                <select id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="agama">Agama *</label>
                                <input type="text" id="agama" name="agama" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="alamat">Alamat Lengkap *</label>
                            <textarea id="alamat" name="alamat" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telepon">Nomor Telepon *</label>
                                <input type="tel" id="telepon" name="telepon" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email">
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Data Orang Tua -->
                    <div class="form-step" data-step="2">
                        <h3>Data Orang Tua</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_ayah">Nama Ayah *</label>
                                <input type="text" id="nama_ayah" name="nama_ayah" required>
                            </div>
                            <div class="form-group">
                                <label for="pekerjaan_ayah">Pekerjaan Ayah *</label>
                                <input type="text" id="pekerjaan_ayah" name="pekerjaan_ayah" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_ibu">Nama Ibu *</label>
                                <input type="text" id="nama_ibu" name="nama_ibu" required>
                            </div>
                            <div class="form-group">
                                <label for="pekerjaan_ibu">Pekerjaan Ibu *</label>
                                <input type="text" id="pekerjaan_ibu" name="pekerjaan_ibu" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="penghasilan_ortu">Penghasilan Orang Tua (per bulan)</label>
                            <input type="number" id="penghasilan_ortu" name="penghasilan_ortu" min="0" step="100000">
                        </div>
                    </div>

                    <!-- Step 3: Data Pendidikan -->
                    <div class="form-step" data-step="3">
                        <h3>Data Pendidikan</h3>
                        
                        <div class="form-group">
                            <label for="asal_sekolah">Asal Sekolah *</label>
                            <input type="text" id="asal_sekolah" name="asal_sekolah" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="alamat_sekolah">Alamat Sekolah</label>
                            <textarea id="alamat_sekolah" name="alamat_sekolah" rows="2"></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nisn">NISN *</label>
                                <input type="text" id="nisn" name="nisn" maxlength="10" required>
                            </div>
                            <div class="form-group">
                                <label for="tahun_lulus">Tahun Lulus *</label>
                                <select id="tahun_lulus" name="tahun_lulus" required>
                                    <option value="">Pilih Tahun</option>
                                    <?php for($i = date('Y'); $i >= date('Y')-5; $i--): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Upload Berkas -->
                    <div class="form-step" data-step="4">
                        <h3>Upload Berkas</h3>
                        
                        <div class="form-group">
                            <label>Foto 3x4 *</label>
                            <div class="file-upload" onclick="document.getElementById('foto').click()">
                                <input type="file" id="foto" name="foto" accept="image/*" required>
                                <div class="file-upload-label">Klik untuk upload foto 3x4 (JPG/PNG, max 5MB)</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Ijazah/SKL SD *</label>
                            <div class="file-upload" onclick="document.getElementById('ijazah').click()">
                                <input type="file" id="ijazah" name="ijazah" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="file-upload-label">Klik untuk upload ijazah (PDF/JPG/PNG, max 5MB)</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Kartu Keluarga *</label>
                            <div class="file-upload" onclick="document.getElementById('kartu_keluarga').click()">
                                <input type="file" id="kartu_keluarga" name="kartu_keluarga" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="file-upload-label">Klik untuk upload kartu keluarga (PDF/JPG/PNG, max 5MB)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Sebelumnya</button>
                        <button type="button" class="btn btn-primary" id="nextBtn">Selanjutnya</button>
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">Daftar Sekarang</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            updateStepDisplay();
            setupFileUploads();
            setupValidation();
        });

        // Navigation
        document.getElementById('nextBtn').addEventListener('click', nextStep);
        document.getElementById('prevBtn').addEventListener('click', prevStep);

        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateStepDisplay();
                }
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepDisplay();
            }
        }

        function updateStepDisplay() {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(step => {
                step.classList.remove('active');
            });

            // Show current step
            document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');

            // Update progress bar
            document.querySelectorAll('.step').forEach((step, index) => {
                const stepNum = index + 1;
                step.classList.remove('active', 'completed');
                if (stepNum < currentStep) {
                    step.classList.add('completed');
                } else if (stepNum === currentStep) {
                    step.classList.add('active');
                }
            });

            // Update buttons
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');

            prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
            nextBtn.style.display = currentStep === totalSteps ? 'none' : 'inline-block';
            submitBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
        }

        function validateCurrentStep() {
            const currentStepElement = document.querySelector(`[data-step="${currentStep}"]`);
            const requiredFields = currentStepElement.querySelectorAll('[required]');
            let isValid = true;
            let errorMessage = '';

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc2626';
                    errorMessage = 'Mohon lengkapi semua field yang wajib diisi';
                } else {
                    field.style.borderColor = '#e5e7eb';
                }
            });

            // Additional validation
            if (currentStep === 1) {
                // Validate phone number
                const phone = document.getElementById('telepon').value;
                if (phone && !/^(\+62|62|0)8[1-9][0-9]{6,9}$/.test(phone)) {
                    isValid = false;
                    document.getElementById('telepon').style.borderColor = '#dc2626';
                    errorMessage = 'Format nomor telepon tidak valid';
                }

                // Validate email
                const email = document.getElementById('email').value;
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    isValid = false;
                    document.getElementById('email').style.borderColor = '#dc2626';
                    errorMessage = 'Format email tidak valid';
                }
            }

            if (currentStep === 3) {
                // Validate NISN
                const nisn = document.getElementById('nisn').value;
                if (nisn && !/^\d{10}$/.test(nisn)) {
                    isValid = false;
                    document.getElementById('nisn').style.borderColor = '#dc2626';
                    errorMessage = 'NISN harus 10 digit angka';
                }
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: errorMessage
                });
            }

            return isValid;
        }

        function setupFileUploads() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const maxSize = 5 * 1024 * 1024; // 5MB
                        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                        
                        if (file.size > maxSize) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Terlalu Besar',
                                text: 'Ukuran file maksimal 5MB'
                            });
                            this.value = '';
                            return;
                        }
                        
                        if (!allowedTypes.includes(file.type)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Tipe File Tidak Didukung',
                                text: 'Hanya file JPG, PNG, dan PDF yang diperbolehkan'
                            });
                            this.value = '';
                            return;
                        }
                        
                        // Update label
                        const label = this.parentElement.querySelector('.file-upload-label');
                        label.textContent = `File dipilih: ${file.name}`;
                        label.style.color = '#10b981';
                    }
                });
            });
        }

        function setupValidation() {
            // Real-time validation
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        this.style.borderColor = '#dc2626';
                    } else {
                        this.style.borderColor = '#e5e7eb';
                    }
                });
            });
        }

        // Form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            if (!validateCurrentStep()) {
                e.preventDefault();
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Menyimpan Data...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        <?php if (!empty($errors)): ?>
        // Show errors
        Swal.fire({
            icon: 'error',
            title: 'Pendaftaran Gagal',
            html: '<?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>'
        });
        <?php endif; ?>
    </script>
</body>
</html> 
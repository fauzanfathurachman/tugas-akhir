<?php
// Include required files
define('SECURE_ACCESS', true);
require_once '../config/config.php';
require_once 'auth_check.php';

// Set page variables
$page_title = 'Verifikasi Berkas';
$current_page = 'verifikasi';

// Get filter parameters
$status_filter = $_GET['status'] ?? 'pending';
$search = $_GET['search'] ?? '';

// Get database instance
$db = Database::getInstance();

// Get verification statistics
try {
    $stats = [
        'pending' => $db->fetchValue("SELECT COUNT(*) FROM calon_siswa WHERE status_verifikasi = 'pending'"),
        'verified' => $db->fetchValue("SELECT COUNT(*) FROM calon_siswa WHERE status_verifikasi = 'verified'"),
        'rejected' => $db->fetchValue("SELECT COUNT(*) FROM calon_siswa WHERE status_verifikasi = 'rejected'"),
        'total' => $db->fetchValue("SELECT COUNT(*) FROM calon_siswa")
    ];
} catch (Exception $e) {
    $stats = ['pending' => 0, 'verified' => 0, 'rejected' => 0, 'total' => 0];
}

// Include header
include 'includes/header.php';
?>

<!-- Main Content -->
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-check-circle"></i>
                Verifikasi Berkas
            </h1>
            <p class="page-subtitle">Verifikasi dokumen pendaftar siswa baru</p>
        </div>
        
        <div class="header-actions">
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['pending']; ?></span>
                        <span class="stat-label">Menunggu</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon verified">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['verified']; ?></span>
                        <span class="stat-label">Diverifikasi</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon rejected">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['rejected']; ?></span>
                        <span class="stat-label">Ditolak</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
        <div class="filters-left">
            <div class="filter-group">
                <label for="status-filter">Status:</label>
                <select id="status-filter" class="form-select">
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                    <option value="verified" <?php echo $status_filter === 'verified' ? 'selected' : ''; ?>>Diverifikasi</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                </select>
            </div>
        </div>
        
        <div class="filters-right">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Cari nama, NISN, atau nomor pendaftaran..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="button" id="search-btn" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <button type="button" id="refresh-btn" class="btn btn-secondary">
                <i class="fas fa-refresh"></i>
                Refresh
            </button>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="verification-grid">
        <!-- Student List -->
        <div class="student-list-section">
            <div class="section-header">
                <h3><i class="fas fa-users"></i> Daftar Pendaftar</h3>
                <div class="list-actions">
                    <button type="button" id="select-all-btn" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-check-square"></i> Pilih Semua
                    </button>
                    <button type="button" id="bulk-verify-btn" class="btn btn-sm btn-success" disabled>
                        <i class="fas fa-check-double"></i> Verifikasi Massal
                    </button>
                </div>
            </div>
            
            <div class="student-list" id="student-list">
                <!-- Student list will be loaded here -->
                <div class="loading-placeholder">
                    <div class="spinner"></div>
                    <p>Memuat data pendaftar...</p>
                </div>
            </div>
        </div>

        <!-- Document Preview -->
        <div class="document-preview-section">
            <div class="section-header">
                <h3><i class="fas fa-file-alt"></i> Preview Dokumen</h3>
                <div class="preview-actions">
                    <button type="button" id="download-all-btn" class="btn btn-sm btn-outline-primary" disabled>
                        <i class="fas fa-download"></i> Download Semua
                    </button>
                    <button type="button" id="download-zip-btn" class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="fas fa-file-archive"></i> Download ZIP
                    </button>
                </div>
            </div>
            
            <div class="document-preview" id="document-preview">
                <div class="no-selection">
                    <i class="fas fa-file-alt"></i>
                    <h4>Pilih pendaftar untuk melihat dokumen</h4>
                    <p>Klik pada nama pendaftar di daftar sebelah kiri untuk melihat dokumen mereka</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div class="modal" id="verification-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-check-circle"></i>
                        Verifikasi Berkas
                    </h4>
                    <button type="button" class="modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="student-info" id="modal-student-info">
                        <!-- Student info will be loaded here -->
                    </div>
                    
                    <div class="verification-checklist">
                        <h5><i class="fas fa-clipboard-check"></i> Checklist Verifikasi</h5>
                        
                        <div class="checklist-grid">
                            <div class="checklist-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="check-foto" class="verification-check">
                                    <span class="checkmark"></span>
                                    <span class="label-text">Foto (3x4)</span>
                                </label>
                                <div class="checklist-notes">
                                    <textarea placeholder="Catatan untuk foto..." class="form-control"></textarea>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="check-kk" class="verification-check">
                                    <span class="checkmark"></span>
                                    <span class="label-text">Kartu Keluarga</span>
                                </label>
                                <div class="checklist-notes">
                                    <textarea placeholder="Catatan untuk KK..." class="form-control"></textarea>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="check-akta" class="verification-check">
                                    <span class="checkmark"></span>
                                    <span class="label-text">Akta Kelahiran</span>
                                </label>
                                <div class="checklist-notes">
                                    <textarea placeholder="Catatan untuk akta..." class="form-control"></textarea>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="check-ijazah" class="verification-check">
                                    <span class="checkmark"></span>
                                    <span class="label-text">Ijazah/SKL</span>
                                </label>
                                <div class="checklist-notes">
                                    <textarea placeholder="Catatan untuk ijazah..." class="form-control"></textarea>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="check-skhun" class="verification-check">
                                    <span class="checkmark"></span>
                                    <span class="label-text">SKHUN</span>
                                </label>
                                <div class="checklist-notes">
                                    <textarea placeholder="Catatan untuk SKHUN..." class="form-control"></textarea>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="check-nisn" class="verification-check">
                                    <span class="checkmark"></span>
                                    <span class="label-text">NISN Valid</span>
                                </label>
                                <div class="checklist-notes">
                                    <textarea placeholder="Catatan untuk NISN..." class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="verification-decision">
                        <h5><i class="fas fa-gavel"></i> Keputusan Verifikasi</h5>
                        
                        <div class="decision-options">
                            <label class="radio-label">
                                <input type="radio" name="verification_decision" value="approved" id="decision-approved">
                                <span class="radio-custom"></span>
                                <span class="label-text">
                                    <i class="fas fa-check-circle text-success"></i>
                                    Diterima
                                </span>
                            </label>
                            
                            <label class="radio-label">
                                <input type="radio" name="verification_decision" value="rejected" id="decision-rejected">
                                <span class="radio-custom"></span>
                                <span class="label-text">
                                    <i class="fas fa-times-circle text-danger"></i>
                                    Ditolak
                                </span>
                            </label>
                        </div>
                        
                        <div class="verification-comments">
                            <label for="verification-comments">Komentar Verifikasi:</label>
                            <textarea id="verification-comments" class="form-control" rows="4" 
                                      placeholder="Berikan komentar detail tentang verifikasi berkas ini..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" id="save-verification-btn" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Verifikasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div class="modal" id="image-viewer-modal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-image"></i>
                        <span id="image-title">Preview Dokumen</span>
                    </h4>
                    <button type="button" class="modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="image-viewer">
                        <div class="image-container">
                            <img id="viewer-image" src="" alt="Document Preview">
                        </div>
                        <div class="image-controls">
                            <button type="button" id="zoom-in" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" id="zoom-out" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" id="rotate-left" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" id="rotate-right" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button type="button" id="download-image" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Custom CSS for Verification Page -->
<style>
/* Verification Page Styles */
.verification-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.student-list-section,
.document-preview-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.section-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.section-header h3 i {
    margin-right: 0.5rem;
    color: #667eea;
}

.list-actions,
.preview-actions {
    display: flex;
    gap: 0.5rem;
}

.student-list {
    max-height: 600px;
    overflow-y: auto;
}

.student-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.student-item:hover {
    background: #f8fafc;
}

.student-item.selected {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
}

.student-item input[type="checkbox"] {
    margin: 0;
}

.student-info {
    flex: 1;
}

.student-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.student-details {
    font-size: 0.875rem;
    color: #6b7280;
}

.student-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-verified {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.document-preview {
    padding: 1.5rem;
    min-height: 400px;
}

.no-selection {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

.no-selection i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

.no-selection h4 {
    margin-bottom: 0.5rem;
    color: #374151;
}

.document-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.document-card {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s;
}

.document-card:hover {
    border-color: #667eea;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.document-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #667eea;
}

.document-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.document-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    margin-top: 0.5rem;
}

.document-actions .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-dialog {
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

/* Checklist Styles */
.verification-checklist {
    margin: 2rem 0;
}

.verification-checklist h5 {
    margin-bottom: 1rem;
    color: #1f2937;
    font-weight: 600;
}

.checklist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.checklist-item {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    position: relative;
    transition: all 0.3s;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background: #667eea;
    border-color: #667eea;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.label-text {
    font-weight: 500;
    color: #1f2937;
}

.checklist-notes textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    padding: 0.5rem;
    font-size: 0.875rem;
    resize: vertical;
    min-height: 60px;
}

/* Decision Styles */
.verification-decision {
    margin-top: 2rem;
}

.verification-decision h5 {
    margin-bottom: 1rem;
    color: #1f2937;
    font-weight: 600;
}

.decision-options {
    display: flex;
    gap: 2rem;
    margin-bottom: 1rem;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.3s;
}

.radio-label:hover {
    border-color: #667eea;
}

.radio-label input[type="radio"] {
    display: none;
}

.radio-custom {
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    position: relative;
    transition: all 0.3s;
}

.radio-label input[type="radio"]:checked + .radio-custom {
    border-color: #667eea;
}

.radio-label input[type="radio"]:checked + .radio-custom::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: #667eea;
    border-radius: 50%;
}

.label-text {
    font-weight: 500;
    color: #1f2937;
}

.verification-comments {
    margin-top: 1rem;
}

.verification-comments label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #1f2937;
}

/* Image Viewer Styles */
.image-viewer {
    text-align: center;
}

.image-container {
    margin-bottom: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    background: #f8fafc;
}

.image-container img {
    max-width: 100%;
    max-height: 500px;
    object-fit: contain;
}

.image-controls {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .verification-grid {
        grid-template-columns: 1fr;
    }
    
    .checklist-grid {
        grid-template-columns: 1fr;
    }
    
    .decision-options {
        flex-direction: column;
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .list-actions,
    .preview-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .modal-dialog {
        width: 95%;
        margin: 1rem;
    }
}

/* Loading States */
.loading-placeholder {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Stats Cards */
.stats-cards {
    display: flex;
    gap: 1rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
}

.stat-icon.pending {
    background: #f59e0b;
}

.stat-icon.verified {
    background: #10b981;
}

.stat-icon.rejected {
    background: #ef4444;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* Filters Section */
.filters-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.filters-left,
.filters-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 500;
    color: #374151;
    white-space: nowrap;
}

.form-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    color: #1f2937;
    font-size: 0.875rem;
    min-width: 150px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-box input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    min-width: 300px;
    font-size: 0.875rem;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

@media (max-width: 768px) {
    .filters-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filters-left,
    .filters-right {
        justify-content: center;
    }
    
    .search-box input {
        min-width: 200px;
    }
}
</style>

<!-- Custom JavaScript for Verification Page -->
<script>
$(document).ready(function() {
    let currentStudent = null;
    let selectedStudents = [];
    
    // Initialize page
    loadStudents();
    
    // Event listeners
    $('#status-filter, #search-input').on('change keyup', function() {
        loadStudents();
    });
    
    $('#search-btn').on('click', function() {
        loadStudents();
    });
    
    $('#refresh-btn').on('click', function() {
        loadStudents();
    });
    
    $('#select-all-btn').on('click', function() {
        if (selectedStudents.length === $('.student-item').length) {
            // Deselect all
            selectedStudents = [];
            $('.student-item input[type="checkbox"]').prop('checked', false);
            $('.student-item').removeClass('selected');
        } else {
            // Select all
            selectedStudents = [];
            $('.student-item').each(function() {
                const studentId = $(this).data('id');
                selectedStudents.push(studentId);
                $(this).find('input[type="checkbox"]').prop('checked', true);
                $(this).addClass('selected');
            });
        }
        updateBulkActions();
    });
    
    $('#bulk-verify-btn').on('click', function() {
        if (selectedStudents.length > 0) {
            bulkVerify();
        }
    });
    
    // Load students
    function loadStudents() {
        const status = $('#status-filter').val();
        const search = $('#search-input').val();
        
        $('#student-list').html(`
            <div class="loading-placeholder">
                <div class="spinner"></div>
                <p>Memuat data pendaftar...</p>
            </div>
        `);
        
        $.ajax({
            url: 'get_verification_students.php',
            method: 'POST',
            data: {
                status: status,
                search: search
            },
            success: function(response) {
                if (response.success) {
                    $('#student-list').html(response.html);
                    initializeStudentList();
                } else {
                    $('#student-list').html(`
                        <div class="no-selection">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h4>Error</h4>
                            <p>${response.message}</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#student-list').html(`
                    <div class="no-selection">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Error</h4>
                        <p>Terjadi kesalahan saat memuat data</p>
                    </div>
                `);
            }
        });
    }
    
    // Initialize student list
    function initializeStudentList() {
        $('.student-item').on('click', function(e) {
            if (!$(e.target).is('input[type="checkbox"]')) {
                const studentId = $(this).data('id');
                selectStudent(studentId);
            }
        });
        
        $('.student-item input[type="checkbox"]').on('change', function() {
            const studentId = $(this).closest('.student-item').data('id');
            const isChecked = $(this).is(':checked');
            
            if (isChecked) {
                if (!selectedStudents.includes(studentId)) {
                    selectedStudents.push(studentId);
                }
                $(this).closest('.student-item').addClass('selected');
            } else {
                selectedStudents = selectedStudents.filter(id => id !== studentId);
                $(this).closest('.student-item').removeClass('selected');
            }
            
            updateBulkActions();
        });
        
        $('.verify-btn').on('click', function(e) {
            e.stopPropagation();
            const studentId = $(this).closest('.student-item').data('id');
            openVerificationModal(studentId);
        });
    }
    
    // Select student
    function selectStudent(studentId) {
        $('.student-item').removeClass('selected');
        $(`.student-item[data-id="${studentId}"]`).addClass('selected');
        
        currentStudent = studentId;
        loadStudentDocuments(studentId);
    }
    
    // Load student documents
    function loadStudentDocuments(studentId) {
        $('#document-preview').html(`
            <div class="loading-placeholder">
                <div class="spinner"></div>
                <p>Memuat dokumen...</p>
            </div>
        `);
        
        $.ajax({
            url: 'get_student_documents.php',
            method: 'POST',
            data: { id: studentId },
            success: function(response) {
                if (response.success) {
                    $('#document-preview').html(response.html);
                    initializeDocumentActions();
                } else {
                    $('#document-preview').html(`
                        <div class="no-selection">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h4>Error</h4>
                            <p>${response.message}</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#document-preview').html(`
                    <div class="no-selection">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Error</h4>
                        <p>Terjadi kesalahan saat memuat dokumen</p>
                    </div>
                `);
            }
        });
    }
    
    // Initialize document actions
    function initializeDocumentActions() {
        $('.view-document').on('click', function() {
            const documentUrl = $(this).data('url');
            const documentTitle = $(this).data('title');
            openImageViewer(documentUrl, documentTitle);
        });
        
        $('.download-document').on('click', function() {
            const documentUrl = $(this).data('url');
            const documentName = $(this).data('name');
            downloadDocument(documentUrl, documentName);
        });
    }
    
    // Open image viewer
    function openImageViewer(url, title) {
        $('#image-title').text(title);
        $('#viewer-image').attr('src', url);
        $('#image-viewer-modal').addClass('show');
        
        // Initialize viewer controls
        let zoom = 1;
        let rotation = 0;
        
        $('#zoom-in').off('click').on('click', function() {
            zoom = Math.min(zoom * 1.2, 3);
            updateImageTransform();
        });
        
        $('#zoom-out').off('click').on('click', function() {
            zoom = Math.max(zoom / 1.2, 0.5);
            updateImageTransform();
        });
        
        $('#rotate-left').off('click').on('click', function() {
            rotation -= 90;
            updateImageTransform();
        });
        
        $('#rotate-right').off('click').on('click', function() {
            rotation += 90;
            updateImageTransform();
        });
        
        $('#download-image').off('click').on('click', function() {
            downloadDocument(url, title);
        });
        
        function updateImageTransform() {
            $('#viewer-image').css('transform', `scale(${zoom}) rotate(${rotation}deg)`);
        }
    }
    
    // Download document
    function downloadDocument(url, filename) {
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Open verification modal
    function openVerificationModal(studentId) {
        $.ajax({
            url: 'get_student_verification_data.php',
            method: 'POST',
            data: { id: studentId },
            success: function(response) {
                if (response.success) {
                    $('#modal-student-info').html(response.html);
                    $('#verification-modal').addClass('show');
                    currentStudent = studentId;
                    
                    // Reset form
                    $('.verification-check').prop('checked', false);
                    $('input[name="verification_decision"]').prop('checked', false);
                    $('#verification-comments').val('');
                    $('.checklist-notes textarea').val('');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat memuat data siswa'
                });
            }
        });
    }
    
    // Save verification
    $('#save-verification-btn').on('click', function() {
        const decision = $('input[name="verification_decision"]:checked').val();
        
        if (!decision) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Pilih keputusan verifikasi terlebih dahulu'
            });
            return;
        }
        
        const comments = $('#verification-comments').val();
        const checklist = {};
        
        $('.verification-check').each(function() {
            const id = $(this).attr('id');
            const isChecked = $(this).is(':checked');
            const notes = $(this).closest('.checklist-item').find('textarea').val();
            
            checklist[id] = {
                checked: isChecked,
                notes: notes
            };
        });
        
        $.ajax({
            url: 'save_verification.php',
            method: 'POST',
            data: {
                student_id: currentStudent,
                decision: decision,
                comments: comments,
                checklist: checklist
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message
                    }).then(() => {
                        $('#verification-modal').removeClass('show');
                        loadStudents();
                        if (currentStudent) {
                            loadStudentDocuments(currentStudent);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat menyimpan verifikasi'
                });
            }
        });
    });
    
    // Bulk verify
    function bulkVerify() {
        Swal.fire({
            title: 'Verifikasi Massal',
            text: `Apakah Anda yakin ingin memverifikasi ${selectedStudents.length} pendaftar?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Verifikasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'bulk_verify.php',
                    method: 'POST',
                    data: { student_ids: selectedStudents },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(() => {
                                selectedStudents = [];
                                loadStudents();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat verifikasi massal'
                        });
                    }
                });
            }
        });
    }
    
    // Update bulk actions
    function updateBulkActions() {
        const hasSelection = selectedStudents.length > 0;
        $('#bulk-verify-btn').prop('disabled', !hasSelection);
        $('#download-all-btn').prop('disabled', !hasSelection);
        $('#download-zip-btn').prop('disabled', !hasSelection);
        
        if (hasSelection) {
            $('#select-all-btn').html('<i class="fas fa-square"></i> Hapus Pilihan');
        } else {
            $('#select-all-btn').html('<i class="fas fa-check-square"></i> Pilih Semua');
        }
    }
    
    // Modal close handlers
    $('.modal-close, [data-dismiss="modal"]').on('click', function() {
        $(this).closest('.modal').removeClass('show');
    });
    
    // Close modal on backdrop click
    $('.modal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('show');
        }
    });
    
    // Close modal on escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.modal').removeClass('show');
        }
    });
});
</script> 
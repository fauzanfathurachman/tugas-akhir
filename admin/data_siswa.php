<?php
require_once '../config/database.php';
require_once 'auth_check.php';

// Set current page for sidebar
$current_page = 'data-siswa';

// Get database connection
$db = Database::getInstance();

// Handle AJAX requests for DataTables
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_data':
            $draw = $_POST['draw'];
            $start = $_POST['start'];
            $length = $_POST['length'];
            $search = $_POST['search']['value'];
            $order_column = $_POST['order'][0]['column'];
            $order_dir = $_POST['order'][0]['dir'];
            $status_filter = $_POST['status_filter'] ?? '';
            $verifikasi_filter = $_POST['verifikasi_filter'] ?? '';
            
            // Build query
            $where_conditions = [];
            $params = [];
            
            if (!empty($search)) {
                $where_conditions[] = "(cs.nama LIKE ? OR cs.nisn LIKE ? OR cs.nomor_pendaftaran LIKE ? OR cs.asal_sekolah LIKE ?)";
                $search_param = "%$search%";
                $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
            }
            
            if (!empty($status_filter)) {
                $where_conditions[] = "p.status_seleksi = ?";
                $params[] = $status_filter;
            }
            
            if (!empty($verifikasi_filter)) {
                $where_conditions[] = "p.status_verifikasi = ?";
                $params[] = $verifikasi_filter;
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Count total records
            $count_query = "SELECT COUNT(*) as total FROM calon_siswa cs 
                           LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
                           $where_clause";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->execute($params);
            $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get filtered data
            $columns = ['cs.id', 'cs.nama', 'cs.nisn', 'cs.asal_sekolah', 'p.status_verifikasi', 'p.status_seleksi'];
            $order_by = $columns[$order_column] . ' ' . $order_dir;
            
            $data_query = "SELECT cs.*, p.status_verifikasi, p.status_seleksi, p.nomor_pendaftaran, p.tanggal_daftar 
                          FROM calon_siswa cs 
                          LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
                          $where_clause 
                          ORDER BY $order_by 
                          LIMIT ?, ?";
            
            $data_stmt = $db->prepare($data_query);
            $params[] = (int)$start;
            $params[] = (int)$length;
            $data_stmt->execute($params);
            $data = $data_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format data for DataTables
            $formatted_data = [];
            foreach ($data as $row) {
                $formatted_data[] = [
                    '', // No - will be added by DataTables
                    '<img src="../uploads/foto/' . ($row['foto'] ?: 'default.jpg') . '" class="student-photo" alt="Foto">',
                    $row['nama'],
                    $row['nisn'],
                    $row['asal_sekolah'],
                    getStatusBadge($row['status_verifikasi']),
                    getStatusBadge($row['status_seleksi']),
                    getActionButtons($row['id'])
                ];
            }
            
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $total_records,
                'recordsFiltered' => $total_records,
                'data' => $formatted_data
            ]);
            exit;
            
        case 'delete':
            $id = $_POST['id'];
            try {
                $db->beginTransaction();
                
                // Delete from pendaftaran first (foreign key)
                $delete_pendaftaran = $db->prepare("DELETE FROM pendaftaran WHERE calon_siswa_id = ?");
                $delete_pendaftaran->execute([$id]);
                
                // Delete from calon_siswa
                $delete_siswa = $db->prepare("DELETE FROM calon_siswa WHERE id = ?");
                $delete_siswa->execute([$id]);
                
                $db->commit();
                
                // Log activity
                logActivity('DELETE', 'calon_siswa', $id, 'Deleted student data');
                
                echo json_encode(['success' => true, 'message' => 'Data siswa berhasil dihapus']);
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
            }
            exit;
    }
}

function getStatusBadge($status) {
    $badges = [
        'Menunggu' => '<span class="badge badge-warning">Menunggu</span>',
        'Diverifikasi' => '<span class="badge badge-success">Diverifikasi</span>',
        'Ditolak' => '<span class="badge badge-danger">Ditolak</span>',
        'Diterima' => '<span class="badge badge-success">Diterima</span>',
        'Cadangan' => '<span class="badge badge-info">Cadangan</span>',
        'Tidak Diterima' => '<span class="badge badge-danger">Tidak Diterima</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">-</span>';
}

function getActionButtons($id) {
    return '
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-info" onclick="viewSiswa(' . $id . ')" title="View">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-warning" onclick="editSiswa(' . $id . ')" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSiswa(' . $id . ')" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    ';
}

function logActivity($action, $table, $record_id, $description) {
    global $db;
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'] ?? 1,
        $action,
        $table,
        $record_id,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Admin PSB MTs Ulul Albab</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    
    <style>
        /* Custom Styles */
        .student-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        
        .badge-warning {
            background-color: #fbbf24;
            color: #92400e;
        }
        
        .badge-success {
            background-color: #10b981;
            color: white;
        }
        
        .badge-danger {
            background-color: #ef4444;
            color: white;
        }
        
        .badge-info {
            background-color: #3b82f6;
            color: white;
        }
        
        .badge-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        .filters-section {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .advanced-search {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        
        .modal-xl {
            max-width: 90%;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
        }
        
        .required {
            color: #ef4444;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
        
        .dt-buttons {
            margin-bottom: 1rem;
        }
        
        .bulk-actions {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .student-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .detail-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .detail-section h6 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .detail-label {
            font-weight: 500;
            color: #6b7280;
        }
        
        .detail-value {
            font-weight: 600;
            color: #374151;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="page-title">
                            <i class="fas fa-users"></i>
                            Data Siswa
                        </h1>
                        <p class="page-subtitle">Kelola data calon siswa dan pendaftaran</p>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" onclick="showAdvancedSearch()">
                            <i class="fas fa-search-plus"></i>
                            Pencarian Lanjutan
                        </button>
                        <button class="btn btn-success" onclick="exportData('excel')">
                            <i class="fas fa-file-excel"></i>
                            Export Excel
                        </button>
                        <button class="btn btn-danger" onclick="exportData('pdf')">
                            <i class="fas fa-file-pdf"></i>
                            Export PDF
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Advanced Search Section -->
            <div class="advanced-search" id="advancedSearch">
                <h5><i class="fas fa-search"></i> Pencarian Lanjutan</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" class="form-control" id="searchNama" placeholder="Cari nama...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NISN</label>
                        <input type="text" class="form-control" id="searchNisn" placeholder="Cari NISN...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Asal Sekolah</label>
                        <input type="text" class="form-control" id="searchSekolah" placeholder="Cari sekolah...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor Pendaftaran</label>
                        <input type="text" class="form-control" id="searchNomor" placeholder="Cari nomor...">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label class="form-label">Status Verifikasi</label>
                        <select class="form-select" id="searchVerifikasi">
                            <option value="">Semua Status</option>
                            <option value="Menunggu">Menunggu</option>
                            <option value="Diverifikasi">Diverifikasi</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Seleksi</label>
                        <select class="form-select" id="searchSeleksi">
                            <option value="">Semua Status</option>
                            <option value="Diterima">Diterima</option>
                            <option value="Cadangan">Cadangan</option>
                            <option value="Tidak Diterima">Tidak Diterima</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Daftar</label>
                        <input type="date" class="form-control" id="searchTanggal">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button class="btn btn-primary" onclick="applyAdvancedSearch()">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            <button class="btn btn-secondary" onclick="clearAdvancedSearch()">
                                <i class="fas fa-times"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="filters-section">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label">Filter Status Verifikasi:</label>
                        <select class="form-select" id="statusVerifikasiFilter">
                            <option value="">Semua Status</option>
                            <option value="Menunggu">Menunggu</option>
                            <option value="Diverifikasi">Diverifikasi</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filter Status Seleksi:</label>
                        <select class="form-select" id="statusSeleksiFilter">
                            <option value="">Semua Status</option>
                            <option value="Diterima">Diterima</option>
                            <option value="Cadangan">Cadangan</option>
                            <option value="Tidak Diterima">Tidak Diterima</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bulk Actions:</label>
                        <select class="form-select" id="bulkAction">
                            <option value="">Pilih Aksi</option>
                            <option value="delete">Hapus Terpilih</option>
                            <option value="export">Export Terpilih</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button class="btn btn-warning" onclick="applyBulkAction()" id="applyBulkBtn" disabled>
                                <i class="fas fa-check"></i> Terapkan
                            </button>
                            <button class="btn btn-info" onclick="selectAll()">
                                <i class="fas fa-check-square"></i> Pilih Semua
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bulk Actions Section -->
            <div class="bulk-actions" id="bulkActions">
                <div class="row align-items-center">
                    <div class="col">
                        <span id="selectedCount">0 item dipilih</span>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                            <i class="fas fa-times"></i> Batal Pilihan
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Data Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataSiswaTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="selectAllCheckbox">
                                    </th>
                                    <th width="50">No</th>
                                    <th width="60">Foto</th>
                                    <th>Nama</th>
                                    <th>NISN</th>
                                    <th>Asal Sekolah</th>
                                    <th>Status Verifikasi</th>
                                    <th>Status Seleksi</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- View Detail Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user"></i>
                        Detail Siswa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-warning" onclick="editFromView()">
                        <i class="fas fa-edit"></i> Edit Data
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i>
                        Edit Data Siswa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId" name="id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Data Pribadi</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="editNama" name="nama" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">NISN <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="editNisn" name="nisn" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" class="form-control" id="editTempatLahir" name="tempat_lahir">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="editTanggalLahir" name="tanggal_lahir">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" id="editJenisKelamin" name="jenis_kelamin">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Agama</label>
                                    <select class="form-select" id="editAgama" name="agama">
                                        <option value="">Pilih Agama</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Konghucu">Konghucu</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">Data Kontak & Sekolah</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" id="editAlamat" name="alamat" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="editTelepon" name="telepon">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editEmail" name="email">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Asal Sekolah</label>
                                    <input type="text" class="form-control" id="editAsalSekolah" name="asal_sekolah">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status Pendaftaran</label>
                                    <select class="form-select" id="editStatusVerifikasi" name="status_verifikasi">
                                        <option value="Menunggu">Menunggu</option>
                                        <option value="Diverifikasi">Diverifikasi</option>
                                        <option value="Ditolak">Ditolak</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status Seleksi</label>
                                    <select class="form-select" id="editStatusSeleksi" name="status_seleksi">
                                        <option value="">Belum Diseleksi</option>
                                        <option value="Diterima">Diterima</option>
                                        <option value="Cadangan">Cadangan</option>
                                        <option value="Tidak Diterima">Tidak Diterima</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Data Orang Tua</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Ayah</label>
                                    <input type="text" class="form-control" id="editNamaAyah" name="nama_ayah">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pekerjaan Ayah</label>
                                    <input type="text" class="form-control" id="editPekerjaanAyah" name="pekerjaan_ayah">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pendapatan Ayah</label>
                                    <select class="form-select" id="editPendapatanAyah" name="pendapatan_ayah">
                                        <option value="">Pilih Pendapatan</option>
                                        <option value="< 1 juta">Kurang dari 1 juta</option>
                                        <option value="1-2 juta">1-2 juta</option>
                                        <option value="2-3 juta">2-3 juta</option>
                                        <option value="3-5 juta">3-5 juta</option>
                                        <option value="> 5 juta">Lebih dari 5 juta</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">Data Ibu</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Ibu</label>
                                    <input type="text" class="form-control" id="editNamaIbu" name="nama_ibu">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pekerjaan Ibu</label>
                                    <input type="text" class="form-control" id="editPekerjaanIbu" name="pekerjaan_ibu">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pendapatan Ibu</label>
                                    <select class="form-select" id="editPendapatanIbu" name="pendapatan_ibu">
                                        <option value="">Pilih Pendapatan</option>
                                        <option value="< 1 juta">Kurang dari 1 juta</option>
                                        <option value="1-2 juta">1-2 juta</option>
                                        <option value="2-3 juta">2-3 juta</option>
                                        <option value="3-5 juta">3-5 juta</option>
                                        <option value="> 5 juta">Lebih dari 5 juta</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveEdit()">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        let dataTable;
        let currentEditId = null;
        
        $(document).ready(function() {
            initializeDataTable();
            initializeFilters();
        });
        
        function initializeDataTable() {
            dataTable = $('#dataSiswaTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'data_siswa.php',
                    type: 'POST',
                    data: function(d) {
                        d.action = 'get_data';
                        d.status_filter = $('#statusSeleksiFilter').val();
                        d.verifikasi_filter = $('#statusVerifikasiFilter').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return '<input type="checkbox" class="row-checkbox" value="' + row[8] + '">';
                        }
                    },
                    { data: null, orderable: false, searchable: false },
                    { data: 1, orderable: false, searchable: false },
                    { data: 2 },
                    { data: 3 },
                    { data: 4 },
                    { data: 5, orderable: true, searchable: false },
                    { data: 6, orderable: true, searchable: false },
                    { data: 7, orderable: false, searchable: false }
                ],
                order: [[3, 'asc']],
                pageLength: 25,
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                drawCallback: function(settings) {
                    // Update row numbers
                    this.api().rows().every(function(rowIdx, tableLoop, rowLoop) {
                        var data = this.data();
                        var api = this.api();
                        var pageInfo = api.page.info();
                        var rowNumber = pageInfo.start + rowIdx + 1;
                        $(api.cell(rowIdx, 1).node()).html(rowNumber);
                    });
                }
            });
        }
        
        function initializeFilters() {
            // Status filters
            $('#statusVerifikasiFilter, #statusSeleksiFilter').change(function() {
                dataTable.ajax.reload();
            });
            
            // Select all checkbox
            $('#selectAllCheckbox').change(function() {
                $('.row-checkbox').prop('checked', this.checked);
                updateBulkActions();
            });
            
            // Individual row checkboxes
            $(document).on('change', '.row-checkbox', function() {
                updateBulkActions();
            });
        }
        
        function updateBulkActions() {
            const checkedBoxes = $('.row-checkbox:checked');
            const count = checkedBoxes.length;
            
            if (count > 0) {
                $('#bulkActions').show();
                $('#selectedCount').text(count + ' item dipilih');
                $('#applyBulkBtn').prop('disabled', false);
            } else {
                $('#bulkActions').hide();
                $('#applyBulkBtn').prop('disabled', true);
            }
        }
        
        function selectAll() {
            $('.row-checkbox').prop('checked', true);
            $('#selectAllCheckbox').prop('checked', true);
            updateBulkActions();
        }
        
        function clearSelection() {
            $('.row-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
            updateBulkActions();
        }
        
        function applyBulkAction() {
            const action = $('#bulkAction').val();
            const selectedIds = $('.row-checkbox:checked').map(function() {
                return this.value;
            }).get();
            
            if (!action) {
                Swal.fire('Error', 'Pilih aksi terlebih dahulu', 'error');
                return;
            }
            
            if (selectedIds.length === 0) {
                Swal.fire('Error', 'Pilih data terlebih dahulu', 'error');
                return;
            }
            
            if (action === 'delete') {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Yakin ingin menghapus ${selectedIds.length} data siswa?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteMultiple(selectedIds);
                    }
                });
            } else if (action === 'export') {
                exportSelected(selectedIds);
            }
        }
        
        function deleteMultiple(ids) {
            $.ajax({
                url: 'data_siswa.php',
                type: 'POST',
                data: {
                    action: 'delete_multiple',
                    ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Sukses', response.message, 'success');
                        dataTable.ajax.reload();
                        clearSelection();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                }
            });
        }
        
        function exportSelected(ids) {
            // Implementation for exporting selected data
            Swal.fire('Info', 'Fitur export data terpilih akan segera tersedia', 'info');
        }
        
        function showAdvancedSearch() {
            const searchDiv = $('#advancedSearch');
            if (searchDiv.is(':visible')) {
                searchDiv.slideUp();
            } else {
                searchDiv.slideDown();
            }
        }
        
        function applyAdvancedSearch() {
            // Apply advanced search filters
            dataTable.ajax.reload();
        }
        
        function clearAdvancedSearch() {
            $('#advancedSearch input, #advancedSearch select').val('');
            dataTable.ajax.reload();
        }
        
        function exportData(type) {
            if (type === 'excel') {
                window.open('export_siswa.php?type=excel', '_blank');
            } else if (type === 'pdf') {
                window.open('export_siswa.php?type=pdf', '_blank');
            }
        }
        
        function viewSiswa(id) {
            $('#viewModal').modal('show');
            $('#viewModalBody').html('<div class="text-center"><div class="spinner-border"></div></div>');
            
            $.ajax({
                url: 'get_siswa_detail.php',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        $('#viewModalBody').html(response.html);
                    } else {
                        $('#viewModalBody').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#viewModalBody').html('<div class="alert alert-danger">Terjadi kesalahan sistem</div>');
                }
            });
        }
        
        function editSiswa(id) {
            currentEditId = id;
            $('#editModal').modal('show');
            
            // Load student data
            $.ajax({
                url: 'get_siswa_data.php',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        fillEditForm(response.data);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                }
            });
        }
        
        function fillEditForm(data) {
            $('#editId').val(data.id);
            $('#editNama').val(data.nama);
            $('#editNisn').val(data.nisn);
            $('#editTempatLahir').val(data.tempat_lahir);
            $('#editTanggalLahir').val(data.tanggal_lahir);
            $('#editJenisKelamin').val(data.jenis_kelamin);
            $('#editAgama').val(data.agama);
            $('#editAlamat').val(data.alamat);
            $('#editTelepon').val(data.telepon);
            $('#editEmail').val(data.email);
            $('#editAsalSekolah').val(data.asal_sekolah);
            $('#editStatusVerifikasi').val(data.status_verifikasi);
            $('#editStatusSeleksi').val(data.status_seleksi);
            $('#editNamaAyah').val(data.nama_ayah);
            $('#editPekerjaanAyah').val(data.pekerjaan_ayah);
            $('#editPendapatanAyah').val(data.pendapatan_ayah);
            $('#editNamaIbu').val(data.nama_ibu);
            $('#editPekerjaanIbu').val(data.pekerjaan_ibu);
            $('#editPendapatanIbu').val(data.pendapatan_ibu);
        }
        
        function saveEdit() {
            const formData = new FormData($('#editForm')[0]);
            formData.append('action', 'update');
            
            $.ajax({
                url: 'update_siswa.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Sukses', response.message, 'success');
                        $('#editModal').modal('hide');
                        dataTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                }
            });
        }
        
        function deleteSiswa(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Yakin ingin menghapus data siswa ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'data_siswa.php',
                        type: 'POST',
                        data: {
                            action: 'delete',
                            id: id
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Sukses', response.message, 'success');
                                dataTable.ajax.reload();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        }
                    });
                }
            });
        }
        
        function editFromView() {
            $('#viewModal').modal('hide');
            if (currentEditId) {
                editSiswa(currentEditId);
            }
        }
    </script>
</body>
</html> 
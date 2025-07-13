<?php
// admin/laporan.php
// Laporan & export data pendaftar (Excel/PDF/Statistik)

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/../config/database.php';
// Pastikan PhpSpreadsheet & TCPDF sudah diinstall via Composer
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use TCPDF;

// ... Logic backend export/report akan ditambahkan di bawah ...
?>
<div class="container mt-4">
    <h2>Laporan & Export Data Pendaftar</h2>
    <ul class="nav nav-tabs" id="laporanTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="excel-tab" data-bs-toggle="tab" data-bs-target="#excel" type="button" role="tab">Excel Export</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pdf-tab" data-bs-toggle="tab" data-bs-target="#pdf" type="button" role="tab">PDF Report</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="statistik-tab" data-bs-toggle="tab" data-bs-target="#statistik" type="button" role="tab">Statistik & Demografi</button>
        </li>
    </ul>
    <div class="tab-content" id="laporanTabContent">
        <!-- Tab 1: Excel Export -->
        <div class="tab-pane fade show active" id="excel" role="tabpanel">
            <form method="get" action="" class="row g-3 mt-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Daftar</label>
                    <input type="date" name="tanggal_mulai" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">s/d</label>
                    <input type="date" name="tanggal_akhir" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Seleksi</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="Diterima">Diterima</option>
                        <option value="Cadangan">Cadangan</option>
                        <option value="Tidak Diterima">Tidak Diterima</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori/Jurusan</label>
                    <input type="text" name="kategori" class="form-control" placeholder="(Opsional)">
                </div>
                <div class="col-12">
                    <button type="submit" name="export_excel" class="btn btn-success">Export Excel</button>
                </div>
            </form>
            <?php // TODO: Proses export Excel di backend ?>
        </div>
        <!-- Tab 2: PDF Report -->
        <div class="tab-pane fade" id="pdf" role="tabpanel">
            <form method="get" action="" class="row g-3 mt-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Daftar</label>
                    <input type="date" name="tanggal_mulai" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">s/d</label>
                    <input type="date" name="tanggal_akhir" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Seleksi</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="Diterima">Diterima</option>
                        <option value="Cadangan">Cadangan</option>
                        <option value="Tidak Diterima">Tidak Diterima</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori/Jurusan</label>
                    <input type="text" name="kategori" class="form-control" placeholder="(Opsional)">
                </div>
                <div class="col-12">
                    <button type="submit" name="export_pdf" class="btn btn-danger">Generate PDF</button>
                </div>
            </form>
            <?php // TODO: Proses export PDF di backend ?>
        </div>
        <!-- Tab 3: Statistik & Demografi -->
        <div class="tab-pane fade" id="statistik" role="tabpanel">
            <div class="mt-3">
                <h5>Statistik Pendaftar & Analisis Demografi</h5>
                <div id="statistikChart" style="height:350px;"></div>
                <?php // TODO: Tampilkan pivot table statistik dan analisis demografi ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Contoh chart statistik (data dinamis dari backend via AJAX)
const ctx = document.getElementById('statistikChart').getContext('2d');
let statistikChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Laki-laki', 'Perempuan'],
        datasets: [{
            label: 'Jumlah Siswa',
            data: [0, 0], // Ganti dengan data dinamis
            backgroundColor: ['#3b82f6', '#f472b6']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: { display: true, text: 'Statistik Gender' }
        }
    }
});
// TODO: AJAX untuk update chart dan pivot table
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

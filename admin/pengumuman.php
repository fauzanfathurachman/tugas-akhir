<?php
// admin/pengumuman.php
// Halaman pengelolaan hasil seleksi siswa

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/../config/database.php';
// Pastikan PhpSpreadsheet sudah diinstall dan autoload
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// ... Logic backend akan ditambahkan di bawah ...
?>
<div class="container mt-4">
    <h2>Kelola Hasil Seleksi & Pengumuman</h2>
    <ul class="nav nav-tabs" id="pengumumanTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="input-manual-tab" data-bs-toggle="tab" data-bs-target="#input-manual" type="button" role="tab">Input Manual</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="import-excel-tab" data-bs-toggle="tab" data-bs-target="#import-excel" type="button" role="tab">Import Excel</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab">Preview & Publish</button>
        </li>
    </ul>
    <div class="tab-content" id="pengumumanTabContent">
        <!-- Tab 1: Input Manual -->
        <div class="tab-pane fade show active" id="input-manual" role="tabpanel">
            <form id="bulkStatusForm" method="post" action="">
                <div class="table-responsive mt-3">
                    <table class="table table-bordered" id="siswaTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Nama</th>
                                <th>NISN</th>
                                <th>Status Seleksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php // TODO: Tampilkan data siswa dari database ?>
                        </tbody>
                    </table>
                </div>
                <div class="mb-3">
                    <select name="bulk_status" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="diterima">Diterima</option>
                        <option value="cadangan">Cadangan</option>
                        <option value="tidak_lulus">Tidak Lulus</option>
                    </select>
                    <button type="submit" class="btn btn-primary mt-2">Set Status Terpilih</button>
                </div>
            </form>
        </div>
        <!-- Tab 2: Import Excel -->
        <div class="tab-pane fade" id="import-excel" role="tabpanel">
            <form method="post" enctype="multipart/form-data" action="">
                <div class="mb-3 mt-3">
                    <label for="excelFile" class="form-label">Upload File Excel (format: NISN, Status)</label>
                    <input type="file" name="excel_file" id="excelFile" class="form-control" accept=".xlsx,.xls" required>
                </div>
                <button type="submit" name="import_excel" class="btn btn-success">Import</button>
            </form>
            <?php // TODO: Tampilkan hasil validasi dan parsing Excel ?>
        </div>
        <!-- Tab 3: Preview & Publish -->
        <div class="tab-pane fade" id="preview" role="tabpanel">
            <form method="post" action="">
                <div class="mb-3 mt-3">
                    <label for="pengumumanContent" class="form-label">Konten Pengumuman</label>
                    <textarea id="pengumumanContent" name="pengumuman_content" class="form-control" rows="8"></textarea>
                </div>
                <div class="mb-3">
                    <label for="publishDate" class="form-label">Jadwalkan Publikasi</label>
                    <input type="datetime-local" name="publish_date" id="publishDate" class="form-control" required>
                </div>
                <button type="submit" name="publish_pengumuman" class="btn btn-warning">Publish Pengumuman</button>
            </form>
            <div class="mt-4">
                <h5>History Publikasi</h5>
                <?php // TODO: Tampilkan history publikasi pengumuman ?>
            </div>
        </div>
    </div>
</div>
<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: '#pengumumanContent',
  plugins: 'lists link image table code',
  toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
  menubar: false
});
</script>
<script>
// Select all checkbox
const selectAll = document.getElementById('selectAll');
if(selectAll) {
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('#siswaTable tbody input[type="checkbox"]').forEach(cb => cb.checked = this.checked);
    });
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

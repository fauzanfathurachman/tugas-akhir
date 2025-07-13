<?php
// admin/analytics.php - Analytics Dashboard
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/../config/database.php';
?>
<div class="container mt-4">
  <h2>Analytics Dashboard</h2>
  <div class="row mb-3">
    <div class="col-md-6">
      <input type="text" id="daterange" class="form-control" placeholder="Pilih rentang tanggal">
    </div>
    <div class="col-md-6 text-end">
      <button id="exportChartBtn" class="btn btn-success">Export Chart</button>
    </div>
  </div>
  <div class="row g-4">
    <div class="col-md-8">
      <canvas id="trendChart" height="120"></canvas>
    </div>
    <div class="col-md-4">
      <canvas id="genderChart" height="120"></canvas>
      <canvas id="funnelChart" height="120" class="mt-4"></canvas>
    </div>
  </div>
  <div class="row g-4 mt-3">
    <div class="col-md-6">
      <canvas id="schoolChart" height="120"></canvas>
    </div>
    <div class="col-md-6">
      <canvas id="heatmapChart" height="120"></canvas>
    </div>
  </div>
  <div class="row mt-4">
    <div class="col-12">
      <div id="map" style="height:320px;width:100%;background:#e5e7eb;border-radius:8px;"></div>
    </div>
  </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
// Dummy data, replace with AJAX fetch from PHP
let trendData = { labels: [], data: [] };
let genderData = { labels: ['Laki-laki','Perempuan'], data: [0,0] };
let funnelData = { labels: ['Daftar','Verifikasi','Lulus'], data: [0,0,0] };
let schoolData = { labels: [], data: [] };
let heatmapData = { labels: [], data: [] };
let geoData = [];

// Date range picker
$('#daterange').daterangepicker({
  locale: { format: 'YYYY-MM-DD' },
  opens: 'left'
}, function(start, end) {
  // Fetch data for selected range
  fetchAnalytics(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
});

function fetchAnalytics(start, end) {
  // TODO: AJAX call to PHP for analytics data
}

// Chart.js init
const trendChart = new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: { labels: trendData.labels, datasets: [{ label: 'Pendaftaran', data: trendData.data, borderColor:'#2563eb', fill:false }] },
  options: { responsive:true, plugins:{ legend:{display:true} } }
});
const genderChart = new Chart(document.getElementById('genderChart'), {
  type: 'pie',
  data: { labels: genderData.labels, datasets: [{ data: genderData.data, backgroundColor:['#3b82f6','#f472b6'] }] },
  options: { plugins:{ legend:{position:'bottom'} } }
});
const funnelChart = new Chart(document.getElementById('funnelChart'), {
  type: 'bar',
  data: { labels: funnelData.labels, datasets: [{ data: funnelData.data, backgroundColor:['#fbbf24','#10b981','#2563eb'] }] },
  options: { plugins:{ legend:{display:false} } }
});
const schoolChart = new Chart(document.getElementById('schoolChart'), {
  type: 'bar',
  data: { labels: schoolData.labels, datasets: [{ label:'Asal Sekolah', data: schoolData.data, backgroundColor:'#60a5fa' }] },
  options: { plugins:{ legend:{display:false} } }
});
const heatmapChart = new Chart(document.getElementById('heatmapChart'), {
  type: 'matrix',
  data: { labels: heatmapData.labels, datasets: [{ label:'Peak Times', data: heatmapData.data }] },
  options: { plugins:{ legend:{display:false} } }
});
// Map: use Leaflet or Google Maps API for geoData
// Export chart
$('#exportChartBtn').on('click', function() {
  const url = trendChart.toBase64Image();
  const a = document.createElement('a');
  a.href = url;
  a.download = 'trend_chart.png';
  a.click();
});
// Auto-refresh
setInterval(() => fetchAnalytics(), 60000);
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
declare(strict_types=1);
$ctrl  = new DashboardController();
$stats = $ctrl->stats();

$deptLabels = array_column($stats['dept_stats'], 'name');
$deptData   = array_column($stats['dept_stats'], 'emp_count');
$attDates   = array_column($stats['attendance_trend'], 'date');
$attPresent = array_column($stats['attendance_trend'], 'present');
$attAbsent  = array_column($stats['attendance_trend'], 'absent');
$payMonths  = array_column($stats['payroll_trend'], 'month');
$payTotals  = array_column($stats['payroll_trend'], 'total');
?>

<div class="page-header">
  <h1>Dashboard</h1>
  <div class="breadcrumb"><a href="#">Home</a><i class="fa-solid fa-chevron-right" style="font-size:.6rem"></i>Dashboard</div>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['label'=>'Total Employees', 'value'=>$stats['total_employees'],   'icon'=>'fa-users',          'color'=>'#6366f1','rgb'=>'99,102,241',  'sub'=>'Active headcount'],
    ['label'=>'Present Today',   'value'=>$stats['present_today'],     'icon'=>'fa-calendar-check', 'color'=>'#22c55e','rgb'=>'34,197,94',   'sub'=>'Attendance today'],
    ['label'=>'Pending Leaves',  'value'=>$stats['pending_leaves'],    'icon'=>'fa-umbrella-beach', 'color'=>'#f59e0b','rgb'=>'245,158,11',  'sub'=>'Awaiting approval'],
    ['label'=>'Pending Payroll', 'value'=>$stats['pending_salary'],    'icon'=>'fa-wallet',         'color'=>'#ef4444','rgb'=>'239,68,68',   'sub'=>'Salary not paid'],
  ];
  foreach ($cards as $card): ?>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="--card-color:<?= $card['color'] ?>;--card-rgb:<?= $card['rgb'] ?>">
      <div class="stat-icon"><i class="fa-solid <?= $card['icon'] ?>"></i></div>
      <div>
        <div class="stat-label"><?= $card['label'] ?></div>
        <div class="stat-value"><?= $card['value'] ?></div>
        <div class="stat-sub"><?= $card['sub'] ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts row -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h5><i class="fa-solid fa-chart-area me-2 text-primary"></i>Attendance Trend (14 Days)</h5>
      </div>
      <div class="card-body"><div class="chart-container"><canvas id="attChart"></canvas></div></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header"><h5><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Dept Distribution</h5></div>
      <div class="card-body"><div class="chart-container"><canvas id="deptChart"></canvas></div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header"><h5><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Monthly Payroll</h5></div>
      <div class="card-body"><div class="chart-container"><canvas id="payChart"></canvas></div></div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header"><h5><i class="fa-solid fa-user-plus me-2 text-primary"></i>Recent Hires</h5></div>
      <div class="card-body p-0">
        <table class="wf-table">
          <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Hire Date</th></tr></thead>
          <tbody>
          <?php foreach ($stats['recent_hires'] as $h): ?>
          <tr>
            <td><code><?= e($h['emp_code']) ?></code></td>
            <td><?= e($h['first_name'].' '.$h['last_name']) ?></td>
            <td><?= e($h['dept']) ?></td>
            <td><?= e($h['hire_date']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
const chartDefaults = { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ labels:{ color:'#94a3b8', font:{size:12} } } } };

// Attendance trend chart
new Chart(document.getElementById('attChart'), {
  type:'line',
  data:{
    labels:<?= json_encode($attDates) ?>,
    datasets:[
      { label:'Present', data:<?= json_encode($attPresent) ?>, borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.1)', tension:.4, fill:true },
      { label:'Absent',  data:<?= json_encode($attAbsent) ?>,  borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.1)',  tension:.4, fill:true }
    ]
  },
  options:{ ...chartDefaults, scales:{ x:{ ticks:{color:'#94a3b8'}, grid:{color:'#1e293b'} }, y:{ ticks:{color:'#94a3b8'}, grid:{color:'#1e293b'}, beginAtZero:true } } }
});

// Dept distribution chart
new Chart(document.getElementById('deptChart'), {
  type:'doughnut',
  data:{
    labels:<?= json_encode($deptLabels) ?>,
    datasets:[{ data:<?= json_encode($deptData) ?>, backgroundColor:['#6366f1','#22c55e','#f59e0b','#38bdf8','#ec4899'], borderWidth:0 }]
  },
  options:{ ...chartDefaults, cutout:'65%' }
});

// Payroll chart
new Chart(document.getElementById('payChart'), {
  type:'bar',
  data:{
    labels:<?= json_encode($payMonths) ?>,
    datasets:[{ label:'Net Payroll (₹)', data:<?= json_encode($payTotals) ?>, backgroundColor:'rgba(99,102,241,.7)', borderRadius:6 }]
  },
  options:{ ...chartDefaults, scales:{ x:{ ticks:{color:'#94a3b8'}, grid:{color:'#1e293b'} }, y:{ ticks:{color:'#94a3b8'}, grid:{color:'#1e293b'}, beginAtZero:true } } }
});
</script>

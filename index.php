<?php 
include('templates/header.php'); 
include('config/db_config.php'); 
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .main-container { display: grid; grid-template-columns: 320px 1fr 320px; gap: 25px; padding: 25px; margin-top: 10px; font-family: 'Tajawal', sans-serif; }
    .card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 24px; padding: 20px; box-shadow: 0 10px 40px rgba(2, 48, 71, 0.08); color: var(--primary); border: 1px solid #eee; }
    .monitor-stack { display: flex; flex-direction: column; gap: 20px; }
    
    .map-wrapper { position: relative; border-radius: 24px; height: 480px; border: 2px solid var(--primary); overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    #live-mini-map { width: 100%; height: 100%; z-index: 1; }

    .kpi-box { background: white; padding: 15px; border-radius: 20px; text-align: center; border-bottom: 4px solid var(--secondary); box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .kpi-box strong { font-size: 1.8rem; color: var(--primary); display: block; transition: all 0.2s ease; }

    #live-alerts-list { height: 500px; overflow-y: auto; padding-right: 5px; }
    .worker-alert { background: #fff5f5; border-right: 5px solid #D63031; padding: 12px; border-radius: 15px; margin-bottom: 12px; animation: slideIn 0.4s ease-out; text-align: right; }
    @keyframes slideIn { from { transform: translateX(30px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    .mini-worker-marker { text-align: center; }
    .mini-worker-icon { font-size: 1.8rem; filter: drop-shadow(0 3px 6px rgba(0,0,0,0.16)); transition: all 0.3s ease; }
    .mini-worker-label { background: var(--primary); color: white; padding: 1px 4px; border-radius: 3px; font-size: 0.6rem; display: block; white-space: nowrap; }
</style>

<main class="main-container">
    <aside class="card">
        <h3 style="display:flex; align-items:center; gap:10px; margin-top:0;">👷 رصد حي لسلامة العمال <span style="background:#D63031; width:10px; height:10px; border-radius:50%; display:inline-block; animation: pulse 1s infinite;"></span></h3>
        <div id="live-alerts-list"></div>
    </aside>

    <section class="monitor-stack">
        <div class="map-wrapper">
            <div id="live-mini-map"></div>
        </div>

        <div class="kpi-row" style="display:grid; grid-template-columns: repeat(3,1fr); gap:15px;">
            <div class="kpi-box"><span>مخاطر صحية/بدنية</span><strong id="val-violations" style="color:#D63031">4</strong></div>
            <div class="kpi-box"><span>معدل الالتزام</span><strong id="val-compliance" style="color:#00B894">97%</strong></div>
            <div class="kpi-box"><span>إجمالي المؤشرات</span><strong id="val-workers">150</strong></div>
        </div>
    </section>

    <aside class="card">
        <h3>📊 التزام PPE </h3>
        <div style="position: relative; height: 180px;">
            <canvas id="ppeChart"></canvas>
        </div>
        <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">
        <h3>🔍 توزيع مخاطر العمال</h3>
        <div style="position: relative; height: 180px;">
            <canvas id="workerVioChart"></canvas>
        </div>
    </aside>
</main>

<script>
let ppeChart, vioChart;
let miniMap;

const centerLat = 21.4365, centerLng = 56.1245;

let mapWorkers = [
    { id: 'W1', name: 'خالد', lat: 21.4380, lng: 56.1260, markerRef: null, alert: false },
    { id: 'W2', name: 'سالم', lat: 21.4410, lng: 56.1220, markerRef: null, alert: false },
    { id: 'W3', name: 'فهد', lat: 21.4330, lng: 56.1235, markerRef: null, alert: false },
    { id: 'W4', name: 'عمار', lat: 21.4355, lng: 56.1280, markerRef: null, alert: false },
    { id: 'W5', name: 'سعيد', lat: 21.4395, lng: 56.1210, markerRef: null, alert: false },
    { id: 'W6', name: 'مازن', lat: 21.4425, lng: 56.1250, markerRef: null, alert: false }
];

function initMiniMap() {
    miniMap = L.map('live-mini-map').setView([centerLat, centerLng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 17, minZoom: 13 }).addTo(miniMap);
    renderMiniMapWorkers();
}

function renderMiniMapWorkers() {
    mapWorkers.forEach(w => {
        const iconHTML = `
            <div class="mini-worker-marker">
                <div class="mini-worker-icon">👷</div>
                <span class="mini-worker-label">${w.name}</span>
            </div>
        `;
        const customIcon = L.divIcon({ className: 'custom-mini-icon', html: iconHTML, iconSize: [40, 50], iconAnchor: [20, 35] });
        if (!w.markerRef) {
            w.markerRef = L.marker([w.lat, w.lng], { icon: customIcon }).addTo(miniMap);
        } else {
            w.markerRef.setLatLng([w.lat, w.lng]);
        }
    });
}

function initCharts() {
    const ppeCtx = document.getElementById('ppeChart').getContext('2d');
    ppeChart = new Chart(ppeCtx, {
        type: 'doughnut',
        data: { labels: ['ملتزم', 'مخالف'], datasets: [{ data: [146, 4], backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0 }] },
        options: { 
            maintainAspectRatio: false,
            responsive: true,
            animation: { duration: 500, easing: 'easeOutQuart' }
        }
    });

    const vioCtx = document.getElementById('workerVioChart').getContext('2d');
    vioChart = new Chart(vioCtx, {
        type: 'bar',
        data: { labels: ['عالي H', 'متوسط M', 'منخفض L'], datasets: [{ label: 'العدد', data: [4, 18, 128], backgroundColor: '#f97316', borderRadius: 5 }] },
        options: { 
            maintainAspectRatio: false,
            responsive: true,
            animation: { duration: 500, easing: 'easeOutQuart' }
        }
    });
}

function updateSystemDynamics() {
    let currentTotal = 145 + (Math.floor(Math.random() * 11) - 5); 
    
    let highRisk = 8 + (Math.floor(Math.random() * 9) - 4); 
    if(highRisk < 3) highRisk = 4;

    let newCompliance = Math.round(((currentTotal - highRisk) / currentTotal) * 100);

    document.getElementById('val-workers').innerText = currentTotal;
    document.getElementById('val-violations').innerText = highRisk;
    document.getElementById('val-compliance').innerText = newCompliance + "%";

    if (ppeChart && ppeChart.data && ppeChart.data.datasets) {
        ppeChart.data.datasets[0].data = [currentTotal - highRisk, highRisk];
        ppeChart.update('none');
        ppeChart.update(); 
    }
    
    if (vioChart && vioChart.data && vioChart.data.datasets) {
        let mRisk = 20 + (Math.floor(Math.random() * 11) - 5); 
        let lRisk = Math.max(0, currentTotal - (highRisk + mRisk));
        
        vioChart.data.datasets[0].data = [highRisk, mRisk, lRisk];
        vioChart.update('none');
        vioChart.update();
    }
    
    mapWorkers.forEach(w => {
        w.lat += (Math.random() - 0.5) * 0.0003;
        w.lng += (Math.random() - 0.5) * 0.0003;
    });
    renderMiniMapWorkers();

    const dummyAlerts = [
        {t: "مخالفة كاميرا السلامة: عدم ارتداء خوذة حماية (PPE)", l: "منطقة برج الحفر رقم 2", s: "H"},
        {t: "رصد ذكي: موظف غير ملتزم بالسترة العاكسة في منطقة الشحن", l: "مستودع المعدات الرئيسي", s: "M"},
        {t: "تنبيه الجدار الجغرافي: دخول عامل منطقة الحفر المحظورة", l: "قطاع الإنتاج المشترك", s: "H"},
        {t: "رصد راداري: اقتراب موظف من نطاق حركة الرافعة الثقيلة", l: "موقع الصيانة الميدانية", s: "M"},
        {t: "مخالفة كاميرات الساحة: رصد مجموعة عمال بدون أحذية الأمان الفنية", l: "منطقة الشحن والتفريغ", s: "H"},
        {t: "تحديث الامتثال: رصد موظف متواجد بدون النظارات الواقية", l: "مختبر الفحص الميداني", s: "L"}
    ];
    let randomAlert = dummyAlerts[Math.floor(Math.random() * dummyAlerts.length)];
    addLiveAlert(randomAlert.t, randomAlert.l, randomAlert.s);
}

function addLiveAlert(title, loc, risk) {
    const list = document.getElementById('live-alerts-list');
    if(!list) return;
    const div = document.createElement('div');
    div.className = 'worker-alert';
    
    if (risk === "H") div.style.borderColor = "#D63031";
    else if (risk === "M") div.style.borderColor = "#FB8500";
    else div.style.borderColor = "#00B894";

    div.innerHTML = `<strong>⚠️ ${title}</strong><br><small>${loc} | ${new Date().toLocaleTimeString('ar-EG')}</small>`;
    list.prepend(div);
    if (list.children.length > 6) list.lastChild.remove();
}

initCharts();
initMiniMap();
updateSystemDynamics();

let systemDynamicsInterval = setInterval(() => { updateSystemDynamics(); }, 2000);
</script>

<?php include('templates/footer.php'); ?>
<?php 
include('../templates/header.php'); 

include('../config/db_config.php'); 
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .twin-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 25px;
        padding: 25px;
        max-width: 1600px;
        margin: auto;
        animation: fadeIn 0.8s ease-in-out;
    }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .map-master-card {
        background: white;
        border-radius: 30px;
        padding: 10px;
        box-shadow: 0 20px 50px rgba(2, 48, 71, 0.1);
        border: 2px solid var(--primary);
        position: relative;
        height: 700px;
        overflow: hidden;
    }

    #digital-map {
        width: 100%;
        height: 100%;
        border-radius: 20px;
        position: relative;
        z-index: 1;
    }

    .map-tools {
        position: absolute;
        top: 25px;
        right: 25px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 10px 20px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        display: flex;
        gap: 15px;
        z-index: 100;
        border: 1px solid var(--sky);
    }

    .side-panel {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .info-card {
        background: white;
        border-radius: 25px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #eee;
    }

    .info-card h3 { color: var(--primary); margin-top: 0; border-bottom: 2px solid #f0f4f8; padding-bottom: 10px; font-size: 1.1rem; }

    .custom-worker-marker {
        text-align: center;
        transition: all 0.5s ease;
    }

    .worker-icon-wrapper {
        font-size: 2.2rem;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        animation: pulse-worker 2s infinite ease-in-out;
    }

    .worker-map-label {
        background: var(--primary);
        color: white;
        padding: 1px 6px;
        border-radius: 4px;
        font-size: 0.65rem;
        font-family: 'Tajawal', sans-serif;
        white-space: nowrap;
        display: inline-block;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .status-dot {
        width: 10px; height: 10px; background: #00B894; border-radius: 50%;
        display: inline-block; margin-left: 8px; animation: pulse-status 1.5s infinite;
    }
    @keyframes pulse-status { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.4; transform: scale(1.3); } 100% { opacity: 1; transform: scale(1); } }

    #movement-log li {
        background: #f8fafc;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 8px;
        border-right: 3px solid var(--sky);
        font-size: 0.8rem;
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
</style>

<div class="twin-container">
    
    <div class="map-master-card">
        <div class="map-tools">
            <span style="font-weight: bold; color: var(--primary);"><span class="status-dot"></span> تتبع OQEP الجغرافي المباشر</span>
            <div style="border-right: 1px solid #ddd; height: 20px;"></div>
            <span style="font-size: 0.8rem; color: #666; display: flex; align-items: center;">🗺️ الخريطة العالمية لايف</span>
        </div>
        
        <div id="digital-map"></div>
    </div>

    <aside class="side-panel">
        <div class="info-card">
            <h3>📍 توزيع القطاعات</h3>
            <div id="sector-stats" style="font-size: 0.9rem; line-height: 2;">
                <p>قطاع الحفر: <strong id="drill-count" style="color:var(--secondary);">12</strong></p>
                <p>منطقة المختبر: <strong id="lab-count" style="color:var(--secondary);">5</strong></p>
                <p>بوابة الشحن: <strong id="cargo-count" style="color:var(--secondary);">8</strong></p>
            </div>
        </div>

        <div class="info-card" style="text-align: center;">
            <h3>🛡️ مؤشر السلامة المباشر</h3>
            <h1 id="total-safety" style="font-size: 3rem; color: #00B894; margin: 10px 0;">98%</h1>
            <p style="font-size:0.8rem; color:#666;">جاري التحليل بواسطة AI Engine</p>
        </div>

        <div class="info-card" style="flex: 1; overflow-y: auto; max-height: 300px;">
            <h3>📊 سجل التحركات الحية (Live Log)</h3>
            <ul id="movement-log" style="list-style:none; padding:0;">
                </ul>
        </div>
    </aside>
</div>

<script>
    const map = L.map('digital-map').setView([21.4365, 56.1245], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        minZoom: 12, 
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const sectorLocations = {
        "القطاع 4": { lat: 21.4380, lng: 56.1260 },
        "المستودع الرئيسي": { lat: 21.4410, lng: 56.1220 },
        "منطقة الشحن": { lat: 21.4330, lng: 56.1235 },
        "المختبر": { lat: 21.4355, lng: 56.1280 },
        "برج الحفر": { lat: 21.4395, lng: 56.1210 },
        "غرفة التوربينات": { lat: 21.4425, lng: 56.1250 },
        "Default": { lat: 21.4365, lng: 56.1245 }
    };

    let workers = [
        { id: 'OQ-1', name: 'خالد', lat: 21.4380, lng: 56.1260, markerRef: null, alert: false },
        { id: 'OQ-2', name: 'سالم', lat: 21.4410, lng: 56.1220, markerRef: null, alert: false },
        { id: 'OQ-3', name: 'فهد', lat: 21.4330, lng: 56.1235, markerRef: null, alert: false }
    ];

    let hseData = [];
    let dataIndex = 0;

    function loadDataFromStorage() {
        const mockData = [
            ["1", "عدم ارتداء خوذة", "H", "القطاع 4", "خالد"],
            ["2", "دخول منطقة محظورة", "M", "المستودع الرئيسي", "سالم"],
            ["3", "فحص دوري للمؤشرات حية", "L", "منطقة الشحن", "فهد"]
        ];
        hseData = mockData;
    }

    function initOrUpdateMarkers() {
        workers.forEach(w => {
            const alertShadow = w.alert ? 'filter: drop-shadow(0 0 12px #D63031) drop-shadow(0 0 4px #D63031);' : '';
            const customHTML = `
                <div class="custom-worker-marker">
                    <div class="worker-icon-wrapper" style="${alertShadow}">👷</div>
                    <span class="worker-map-label">${w.name}</span>
                </div>
            `;

            const customIcon = L.divIcon({
                className: 'leaflet-data-marker',
                html: customHTML,
                iconSize: [50, 60],
                iconAnchor: [25, 45]
            });

            if (!w.markerRef) {
                w.markerRef = L.marker([w.lat, w.lng], { icon: customIcon }).addTo(map);
                w.markerRef.bindPopup(`<b>العامل: ${w.name}</b><br>رقم المعرف: ${w.id}<br>الحالة: متصل بجهاز ESP32`);
            } else {
                w.markerRef.setLatLng([w.lat, w.lng]);
                w.markerRef.setIcon(customIcon);
            }
        });
    }

    function moveWorkersByData() {
        if(hseData.length === 0) return;

        const row = hseData[dataIndex];
        const workerName = row[4] || "خالد"; 
        const locationName = row[3]; 
        
        const coords = sectorLocations[locationName] || sectorLocations["Default"];
        
        let worker = workers.find(w => w.name.includes(workerName));
        if(worker) {
            const randomOffsetLat = (Math.random() - 0.5) * 0.0008;
            const randomOffsetLng = (Math.random() - 0.5) * 0.0008;

            worker.lat = coords.lat + randomOffsetLat;
            worker.lng = coords.lng + randomOffsetLng;
            worker.alert = (row[2] === "H"); 
            
            map.panTo([worker.lat, worker.lng]);
        }

        updateLog(workerName, locationName, row[2]);
        dataIndex = (dataIndex + 1) % hseData.length;
        
        initOrUpdateMarkers();
        updateTwinStats();
    }

    function updateLog(name, loc, risk) {
        const log = document.getElementById('movement-log');
        const time = new Date().toLocaleTimeString('ar-EG', {hour: '2-digit', minute:'2-digit'});
        const newEntry = document.createElement('li');
        
        let statusColor = risk === "H" ? "color:#D63031; font-weight:bold;" : "color:#00B894;";
        newEntry.innerHTML = `📍 <b>${name}</b> رُصد في <b>${loc}</b> <br> <small style="${statusColor}">مستوى الخطر في السجل: ${risk} [${time}]</small>`;
        
        log.prepend(newEntry);
        if (log.children.length > 5) log.lastChild.remove();
    }

    function updateTwinStats() {
        document.getElementById('drill-count').innerText = Math.floor(Math.random() * 5) + 8;
        document.getElementById('lab-count').innerText = Math.floor(Math.random() * 3) + 2;
        
        let safety = hseData.some(r => r[2] === "H") ? "85%" : "98%";
        document.getElementById('total-safety').innerText = safety;
    }

    loadDataFromStorage();
    initOrUpdateMarkers();
    setInterval(moveWorkersByData, 5000); 
</script>

<?php 
include('../templates/footer.php'); 
?>
<?php 
include('../templates/header.php'); 
include('../config/db_config.php'); 
?>

<style>
    .container { max-width: 1400px; margin: 40px auto; padding: 0 30px; font-family: 'Tajawal', sans-serif; animation: fadeIn 0.8s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .filter-section { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(2, 48, 71, 0.05); display: flex; gap: 15px; margin-bottom: 30px; border: 1px solid rgba(142, 202, 230, 0.3); }
    .filter-section input, .filter-section select { padding: 12px 15px; border: 1px solid #e0e6ed; border-radius: 12px; outline: none; flex: 1; color: var(--primary); font-family: 'Tajawal'; }

    .btn-oqep { background: var(--secondary); color: white; border: none; padding: 12px 30px; border-radius: 12px; cursor: pointer; font-weight: bold; transition: 0.3s; }
    .btn-oqep:hover { background: #e67a00; transform: translateY(-2px); }

    .table-wrapper { background: white; border-radius: 25px; overflow: hidden; box-shadow: 0 15px 45px rgba(2, 48, 71, 0.1); border: 1px solid #eee; }
    .oqep-table { width: 100%; border-collapse: collapse; text-align: right; }
    .oqep-table th { background: var(--primary); color: white; padding: 20px; }
    .oqep-table td { padding: 20px; border-bottom: 1px solid #f1f5f9; }

    .ai-box { position: relative; width: 60px; height: 60px; border-radius: 50%; border: 2px solid var(--sky); overflow: hidden; transition: 0.3s; }
    .ai-box:hover { transform: scale(1.2); }
    .ai-box img { width: 100%; height: 100%; object-fit: cover; }

    .badge { padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: bold; display: inline-flex; align-items: center; gap: 5px; }
    .badge-high { background: #fff1f2; color: #e11d48; } 
    .badge-med { background: #fff4e5; color: var(--secondary); }
    .badge-low { background: #f0fdf4; color: #16a34a; } 
    
    .worker-link { color: var(--primary); text-decoration: none; transition: 0.2s; }
    .worker-link:hover { color: var(--secondary); text-decoration: underline; }
</style>

<div class="container">
    <div class="filter-section">
        <input type="text" id="searchInput" onkeyup="filterData()" placeholder="🔍 ابحث بالاسم أو الموقع...">
        <select id="riskSelect" onchange="filterData()">
            <option value="">جميع مستويات المخاطر</option>
            <option value="H">مخاطر عالية (High)</option>
            <option value="M">مخاطر متوسطة (Medium)</option>
            <option value="L">مخاطر منخفضة (Low)</option>
        </select>
        <button class="btn-oqep" onclick="resetFilters()">تصفير الفلتر</button>
    </div>

    <div class="table-wrapper">
        <table class="oqep-table">
            <thead>
                <tr>
                    <th>AI Snapshot</th>
                    <th>العامل</th>
                    <th>الملاحظة (HSE Observation)</th>
                    <th>الموقع</th>
                    <th>مستوى الخطورة</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody id="violation-body">
                </tbody>
        </table>
    </div>
</div>

<script>
    const hseData = [
        {id: "OQ-001", worker: "خالد بن أحمد", obs: "عدم ارتداء خوذة السلامة", loc: "القطاع 4", risk: "H", img: "Engineer 001.png"},
        {id: "OQ-002", worker: "سالم المعمري", obs: "دخول منطقة غاز بدون تصريح", loc: "المختبر", risk: "M", img: "Engineer 002.png"},
        {id: "OQ-003", worker: "فهد البوسعيدي", obs: "استخدام معدات تالفة", loc: "منطقة الشحن", risk: "H", img: "Engineer 003.png"},
        {id: "OQ-004", worker: "عمار الهنائي", obs: "فحص دوري - ملتزم", loc: "بوابة 2", risk: "L", img: "Engineer 004.png"}
    ];

    function renderTable(data) {
        const body = document.getElementById('violation-body');
        body.innerHTML = "";

        data.forEach(row => {
            let riskClass = row.risk === 'H' ? 'badge-high' : (row.risk === 'M' ? 'badge-med' : 'badge-low');
            let riskText = row.risk === 'H' ? 'High Risk' : (row.risk === 'M' ? 'Medium' : 'Low');

            let profileUrl = `worker_analytics.php?name=${encodeURIComponent(row.worker)}&id=${row.id}&loc=${encodeURIComponent(row.loc)}&obs=${encodeURIComponent(row.obs)}`;

            body.innerHTML += `
                <tr class="violation-row">
                    <td><div class="ai-box"><img src="../assets/images/vios/${row.img}"></div></td>
                    <td>
                        <a href="${profileUrl}" class="worker-link"><b>${row.worker}</b></a>
                        <br><small>${row.id}</small>
                    </td>
                    <td>${row.obs}</td>
                    <td>${row.loc}</td>
                    <td><span class="badge ${riskClass}">${riskText}</span></td>
                    <td>
                        <button class="btn-oqep" style="padding:5px 15px; font-size:0.7rem;" onclick="sendRealSMS(this, '${row.worker}', '${row.obs}', '${row.loc}')">تنبيه</button>
                    </td>
                </tr>
            `;
        });
    }

    function sendRealSMS(buttonElement, workerName, observation, location) {
        const originalText = buttonElement.innerText;
        buttonElement.innerText = "⚡ جاري الإرسال...";
        buttonElement.style.background = "#b2bec3";
        buttonElement.disabled = true;

        fetch('../api/send_sms.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                worker: workerName,
                obs: observation,
                loc: location
            })
        })
        .then(response => response.json())
        .then(result => {
            buttonElement.innerText = originalText;
            buttonElement.style.background = "var(--secondary)";
            buttonElement.disabled = false;
            
            if(result.status === 'success') {
                alert("📱 [SafeWork SMS Gateway]:\nتم رصد المخالفة وإرسال رسالة نصية حقيقية (SMS) إلى هاتف المشرف بنجاح!");
            } else {
                let twilioError = result.debug && result.debug.message ? result.debug.message : 'فشل الاتصال الخارجي بالسيرفر';
                let twilioCode = result.debug && result.debug.code ? ' [Code: ' + result.debug.code + ']' : '';
                alert("❌ فشل إرسال الـ SMS من Twilio:\n" + twilioError + twilioCode);
                console.error(result.debug);
            }
        })
        .catch(error => {
            buttonElement.innerText = originalText;
            buttonElement.style.background = "var(--secondary)";
            buttonElement.disabled = false;
            alert("🌐 خطأ: لم يتم العثور على ملف send_sms.php أو حدثت مشكلة في قراءة البيانات.");
        });
    }

    function filterData() {
        const searchVal = document.getElementById('searchInput').value.toLowerCase();
        const riskVal = document.getElementById('riskSelect').value;
        
        const filtered = hseData.filter(item => {
            return (item.worker.toLowerCase().includes(searchVal) || item.loc.toLowerCase().includes(searchVal)) &&
                   (riskVal === "" || item.risk === riskVal);
        });
        renderTable(filtered);
    }

    function resetFilters() {
        document.getElementById('searchInput').value = "";
        document.getElementById('riskSelect').value = "";
        renderTable(hseData);
    }

    renderTable(hseData);
</script>

<?php include('../templates/footer.php'); ?>
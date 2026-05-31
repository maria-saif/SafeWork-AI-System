<?php 
include('../templates/header.php'); 
include('../config/db_config.php'); 
?>

<style>
    .eva-container { max-width: 1400px; margin: 40px auto; padding: 0 30px; font-family: 'Tajawal', sans-serif; animation: fadeIn 0.8s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .eva-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
    .stat-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); text-align: center; border: 1px solid #eee; }
    .stat-number { font-size: 2.5rem; font-weight: bold; margin-top: 10px; }
    
    .color-total { color: #023047; }
    .color-safe { color: #16a34a; }
    .color-danger { color: #e11d48; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

    .eva-main-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-top: 30px; }
    
    .block-card { background: white; padding: 30px; border-radius: 25px; box-shadow: 0 15px 45px rgba(2, 48, 71, 0.05); border: 1px solid #eee; }
    .block-title { margin-top: 0; color: #023047; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }

    .eva-table { width: 100%; border-collapse: collapse; text-align: right; margin-top: 15px; }
    .eva-table th { background: #f8fafc; color: #64748b; padding: 15px; font-size: 0.9rem; }
    .eva-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; }

    .btn-alert-sms { background: #e11d48; color: white; border: none; padding: 6px 15px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 0.8rem; }
    .btn-alert-sms:hover { background: #be123c; transform: translateY(-1px); }
</style>

<div class="eva-container">
    <div style="background: #FFF5F5; border-right: 5px solid #e11d48; padding: 20px; border-radius: 15px; margin-bottom: 25px;">
        <h2 style="color: #be123c; margin: 0; display: flex; align-items: center; gap: 10px;">🚨 نظام تتبع الإخلاء الذكي (AI Evacuation Tracking)</h2>
        <p style="color: #4c1d95; margin: 5px 0 0;">حالة الطوارئ: <b>نشطة (إخلاء تجريبي)</b> | رصد ذكي مستمر عبر كاميرات الرؤية الحاسوبية المثبتة في <b>نقطة التجمع (Assembly Point)</b>.</p>
    </div>

    <div class="eva-stats-grid">
        <div class="stat-card">
            <span style="color: #64748b; font-weight: bold;">👥 إجمالي الموظفين بالوردية</span>
            <div class="stat-number color-total" id="total-count">25</div>
        </div>
        <div class="stat-card" style="border-bottom: 4px solid #16a34a;">
            <span style="color: #16a34a; font-weight: bold;">🟢 رصدتهم كاميرا نقطة التجمع</span>
            <div class="stat-number color-safe" id="safe-count">22</div>
        </div>
        <div class="stat-card" style="border-bottom: 4px solid #e11d48;">
            <span style="color: #e11d48; font-weight: bold;">🚨 المفقودين (جاري البحث بالداخل)</span>
            <div class="stat-number color-danger" id="danger-count">3</div>
        </div>
    </div>

    <div class="eva-main-layout">
        <div class="block-card">
            <h4 class="block-title">🔎 قائمة الأفراد الذين لم يصلوا إلى نقطة التجمع بعد</h4>
            <table class="eva-table">
                <thead>
                    <tr>
                        <th>العامل</th>
                        <th>الموقع الأخير المرصود للكاميرات</th>
                        <th>توقيت آخر ظهور</th>
                        <th>الإجراء السريع</th>
                    </tr>
                </thead>
                <tbody id="missing-workers-body">
                    </tbody>
            </table>
        </div>

        <div class="block-card" style="display: flex; flex-direction: column; align-items: center;">
            <h4 class="block-title" style="width: 100%;">📊 مؤشر نسبة الإخلاء</h4>
            <div style="width: 220px; height: 220px; margin-top: 20px; position: relative;">
                <canvas id="evacuationChart"></canvas>
            </div>
            <div style="margin-top: 25px; font-size: 0.85rem; color: #64748b; line-height: 1.6; text-align: center; background: #f8fafc; padding: 12px; border-radius: 12px;">
                💡 <b>تحديث الرؤية الحاسوبية:</b> يتم تحديث القائمة تلقائياً بمجرد التعرف على وجه الموظف عبر الكاميرا الحرارية عند البوابة الخارجية لنقطة التجمع.
            </div>
        </div>
    </div>
</div>

<script>
    let missingWorkers = [
        { name: "فهد البوسعيدي", loc: "منطقة الشحن والتفريغ", time: "10:32 AM", id: "OQ-003" },
        { name: "سالم المعمري", loc: "مختبر الغازات الرئيسي", time: "10:28 AM", id: "OQ-002" },
        { name: "عبدالله الرحبي", loc: "ورشة الصيانة الكهربائية", time: "10:11 AM", id: "OQ-015" }
    ];

    function renderMissingTable() {
        const body = document.getElementById('missing-workers-body');
        body.innerHTML = "";
        
        document.getElementById('danger-count').innerText = missingWorkers.length;
        document.getElementById('safe-count').innerText = 25 - missingWorkers.length;

        if (missingWorkers.length === 0) {
            body.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#16a34a; font-weight:bold; padding:30px;">🟢 رائع! تم إخلاء جميع الموظفين بنجاح وتواجدهم بنقطة التجمع.</td></tr>`;
            return;
        }

        missingWorkers.forEach((worker, index) => {
            body.innerHTML += `
                <tr>
                    <td><b>${worker.name}</b><br><small style="color:#777;">ID: ${worker.id}</small></td>
                    <td>📍 ${worker.loc || 'القطاع الشمالي'}</td>
                    <td style="color:#e11d48; font-weight:bold;">${worker.time}</td>
                    <td>
                        <button class="btn-alert-sms" onclick="sendEvacSMS(this, '${worker.name}', '${worker.loc || 'المنطقة الحرة'}', ${index})">🚨 إنذار SMS طارئ</button>
                    </td>
                </tr>
            `;
        });
    }

    function sendEvacSMS(buttonElement, workerName, lastLoc, index) {
        const originalText = buttonElement.innerText;
        buttonElement.innerText = "⚡ جاري البث الدولي...";
        buttonElement.style.background = "#b2bec3";
        buttonElement.disabled = true;

        fetch('../api/send_sms.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                worker: workerName,
                obs: "🔴 [إخلاء طارئ للسلامة] - لم يصل إلى نقطة التجمع بعد!",
                loc: lastLoc
            })
        })
        .then(response => response.json())
        .then(result => {
            buttonElement.innerText = originalText;
            buttonElement.style.background = "#e11d48";
            buttonElement.disabled = false;
            
            if(result.status === 'success') {
                alert(`📱 [SafeWork Emergency SMS]:\nتم إرسال رسالة تحذيرية حقيقية لهاتف المشرف للتحرك والبحث عن الموظف: (${workerName}) في ${lastLoc}!`);
                
                alert(`📸 [محاكاة الرؤية الحاسوبية]: الكاميرا الذكية رصدت دخول ${workerName} الآن لنقطة التجمع!`);
                missingWorkers.splice(index, 1); 
                renderMissingTable(); 
                updateChart(); 
            } else {
                alert("❌ فشل الاتصال ببوابة Twilio الطارئة.");
            }
        })
        .catch(error => {
            buttonElement.innerText = originalText;
            buttonElement.style.background = "#e11d48";
            buttonElement.disabled = false;
            alert("🌐 خطأ في مسار الاتصال بملف السيرفر.");
        });
    }

    const chartCtx = document.getElementById('evacuationChart').getContext('2d');
    let evacChart;

    function initChart() {
        const safe = 25 - missingWorkers.length;
        const missing = missingWorkers.length;

        evacChart = new Chart(chartCtx, {
            type: 'doughnut',
            data: {
                labels: ['تم إجلاؤهم بنجاح', 'مفقودين بالداخل'],
                datasets: [{
                    data: [safe, missing],
                    backgroundColor: ['#16a34a', '#e11d48'],
                    borderWidth: 0
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '75%'
            }
        });
    }

    function updateChart() {
        const safe = 25 - missingWorkers.length;
        const missing = missingWorkers.length;
        evacChart.data.datasets[0].data = [safe, missing];
        evacChart.update();
    }

    renderMissingTable();
    initChart();
</script>

<?php include('../templates/footer.php'); ?>
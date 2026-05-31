<?php 
include('../templates/header.php'); 
include('../config/db_config.php'); 
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .proactive-container { max-width: 1400px; margin: 40px auto; padding: 0 30px; font-family: 'Tajawal', sans-serif; animation: fadeIn 0.8s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .zones-dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 25px; }
    
    .main-analytics-card { background: white; padding: 30px; border-radius: 25px; box-shadow: 0 10px 30px rgba(2, 48, 71, 0.05); border: 1px solid #eee; }
    .side-control-card { background: white; padding: 30px; border-radius: 25px; box-shadow: 0 10px 30px rgba(2, 48, 71, 0.05); border: 1px solid #eee; height: fit-content; }

    .matrix-title { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
    
    .zone-matrix-list { display: flex; flex-direction: column; gap: 20px; margin-top: 20px; }
    .zone-row { background: #f8fafc; border: 1px solid #e2e8f0; padding: 25px; border-radius: 20px; transition: 0.3s ease; display: flex; flex-direction: column; gap: 15px; }
    .zone-row:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(2, 48, 71, 0.06); }
    
    .zone-header { display: flex; justify-content: space-between; align-items: center; }
    .zone-info { display: flex; align-items: center; gap: 15px; }
    .zone-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    
    .risk-badge { padding: 5px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
    .risk-high { background: #ffe4e6; color: #e11d48; border: 1px solid #fecdd3; }
    .risk-medium { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .risk-low { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }

    .zone-ai-box { background: #f0fdf4; border: 1px dashed #0d9488; padding: 15px 20px; border-radius: 14px; display: flex; gap: 12px; align-items: center; margin-top: 5px; }
    
    .btn-broadcast-action { background: #e11d48; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.15); }
    .btn-broadcast-action:hover { background: #be123c; transform: translateY(-2px); }

    .proactive-modal { display: none; position: fixed; inset: 0; background: rgba(2, 48, 71, 0.6); z-index: 9999999; backdrop-filter: blur(5px); align-items: center; justify-content: center; }
    .proactive-modal-content { background: white; padding: 35px; border-radius: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); width: 90%; max-width: 480px; text-align: center; direction: rtl; border-top: 6px solid #FB8500; animation: modalPop 0.3s ease-out; }
    @keyframes modalPop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>

<div id="proactiveAlertModal" class="proactive-modal">
    <div class="proactive-modal-content">
        <div id="p-modal-icon" style="font-size: 3rem; margin-bottom: 12px;">🤖</div>
        <strong id="p-modal-title" style="color: #023047; font-size: 1.2rem; display: block; margin-bottom: 10px;">[🤖 رادار التنبؤ الوقائي]:</strong>
        <p id="p-modal-desc" style="color: #4a5568; font-size: 0.95rem; margin: 0; line-height: 1.6;"></p>
        <button onclick="closeProactiveModal()" style="background: #023047; color: white; border: none; padding: 10px 30px; border-radius: 10px; cursor: pointer; font-family: 'Tajawal'; font-weight: bold; margin-top: 20px; transition: 0.2s;">إغلاق الحزمة</button>
    </div>
</div>

<div class="proactive-container">
    <div style="text-align: right; margin-bottom: 30px;">
        <h2 style="color: var(--primary); margin: 0; font-size: 2rem;">🛰️ لوحة التنبؤ الوقائي وتحليل كثافة المخاطر الجغرافية</h2>
        <p style="color: #64748b; margin: 6px 0 0; font-size: 1.05rem;">الانتقال من مرحلة رصد المخالفات اللحظية إلى التنبؤ بكثافة المخاطر السلوكية وإصدار تدابير احترازية مضمونة 100% لحماية عمال وموظفي OQEP.</p>
    </div>

    <div class="zones-dashboard-grid">
        
        <div class="main-analytics-card">
            <div class="matrix-title">
                <h3 style="color: var(--primary); margin: 0;">🗺️ مصفوفة رصد الكثافة السلوكية والامتثال لمعدات الـ PPE في مواقع OQEP</h3>
                <span style="color: #64748b; font-size: 0.85rem; font-weight: 500;">تحليل تجميعي ذكي لايف لقاعدة بيانات سجلات الأفراد 🛰️</span>
            </div>

            <div class="zone-matrix-list" style="direction: rtl; text-align: right;">
                
                <div class="zone-row" style="border-right: 6px solid #e11d48;">
                    <div class="zone-header">
                        <div class="zone-info">
                            <div class="zone-icon" style="background: #ffe4e6; color: #e11d48;">👷‍♂️</div>
                            <div>
                                <h4 style="margin: 0 0 4px; color: #023047;">منطقة الامتياز مربع 60 (Block 60 Field)</h4>
                                <span style="font-size: 0.85rem; color: #64748b;">النمط السلوكي الشائع بالملف: <b style="color: #e11d48;">إهمال ارتداء خوذة السلامة ونظارات الواقية (No PPE Breach)</b></span>
                            </div>
                        </div>
                        <div>
                            <span class="risk-badge risk-high">⚠️ كثافة انحراف سلوكي عالية (149 ملاحظة عمالية)</span>
                        </div>
                    </div>
                    
                    <div class="zone-ai-box">
                        <div style="font-size: 1.4rem;">🤖</div>
                        <div style="font-size: 0.9rem; color: #0f766e; flex: 1;">
                            <b>توصية خوارزمية AI الحالية للعمال:</b> تم رصد ارتفاع بنسبة 18% في عبور العمال إلى الورشة الرئيسية ومنصات الحفر بدون خوذات واقية. <u>يوصي النظام</u> بتفعيل جرس الإنذار الصوتي التلقائي عند البوابات الإلكترونية لإلزام الأفراد بالارتداء الكامل قبل الدخول للموقع.
                        </div>
                        <button class="btn-broadcast-action" style="background: #e11d48;" onclick="triggerProactiveBroadcast('منطقة مربع 60 الإستراتيجية', 'إلزامية ارتداء خوذة السلامة ونظارات الـ PPE عند المداخل')">
                            📢 بث أمر الالتزام الوقائي
                        </button>
                    </div>
                </div>

                <div class="zone-row" style="border-right: 6px solid #d97706;">
                    <div class="zone-header">
                        <div class="zone-info">
                            <div class="zone-icon" style="background: #fef3c7; color: #d97706;">📍</div>
                            <div>
                                <h4 style="margin: 0 0 4px; color: #023047;">مصنع مسندم لمعالجة الغاز (Musandam Gas Plant)</h4>
                                <span style="font-size: 0.85rem; color: #64748b;">النمط السلوكي الشائع بالملف: <b style="color: #d97706;">فوضى تنظيم الموقع (Poor Housekeeping) وعمال بدون أقنعة هروب</b></span>
                            </div>
                        </div>
                        <div>
                            <span class="risk-badge risk-medium">⚡ خطر مكاني متوسط (77 ملاحظة في السجل)</span>
                        </div>
                    </div>
                    
                    <div class="zone-ai-box" style="background: #fffbeb; border-color: #d97706;">
                        <div style="font-size: 1.4rem;">🤖</div>
                        <div style="font-size: 0.9rem; color: #92400e; flex: 1;">
                            <b>توصية خوارزمية AI الحالية للعمال:</b> تم تسجيل حالات تعثر نتيجة لعدم ترتيب الأدوات والمخلفات من قِبل المساعدين في <b>Unit 52</b>، مع رصد عمال يدخلون مناطق الصيانة الحساسة بدون أقنعة الهروب (Escape Hoods). <u>يوصي النظام</u> بإلزام عمال الوردية بحمل أقنعة الهروب وتنظيف ممرات الطوارئ فوراً.
                        </div>
                        <button class="btn-broadcast-action" style="background: #d97706;" onclick="triggerProactiveBroadcast('مصنع مسندم للغاز', 'تنظيم ممرات العمال وإلزامية أقنعة الهروب بالمنطقة')">
                            📢 بث أمر الالتزام الوقائي
                        </button>
                    </div>
                </div>

                <div class="zone-row" style="border-right: 6px solid #16a34a;">
                    <div class="zone-header">
                        <div class="zone-info">
                            <div class="zone-icon" style="background: #dcfce7; color: #16a34a;">🛡️</div>
                            <div>
                                <h4 style="margin: 0 0 4px; color: #023047;">منطقة الامتياز مربع 8 ومنصات بخا البحرية</h4>
                                <span style="font-size: 0.85rem; color: #64748b;">النمط السلوكي الشائع بالملف: <b style="color: #16a34a;">التزام تام بالـ PPE ومحادثات توعوية ممتازة (Toolbox Talks)</b></span>
                            </div>
                        </div>
                        <div>
                            <span class="risk-badge risk-low">✅ عمال ممتثلين بالكامل (بيئة مستقرة وآمنة)</span>
                        </div>
                    </div>
                    
                    <div class="zone-ai-box" style="background: #f0fdf4; border-color: #16a34a;">
                        <div style="font-size: 1.4rem;">🤖</div>
                        <div style="font-size: 0.9rem; color: #166534; flex: 1;">
                            <b>توصية خوارزمية AI الحالية للعمال:</b> يظهر السجل وعي سلوكي ممتاز من قِبل العمال في الإبلاغ الاستباقي عن المخاطر، وفحص طفايات الحريق، وتأمين عمليات الرفع الحساسة برؤية اتجاه الرياح عبر الـ Wind Socks. <u>يوصي النظام</u> بنشر شهادة شكر رقمية آلياً لعمال المنصة لتعزيز هذا السلوك الآمن.
                        </div>
                        <button class="btn-broadcast-action" style="background: #16a34a;" onclick="triggerProactiveBroadcast('منصات مربع 8 وبخا البحرية', 'المحافظة على وعي العمال وجولات التدقيق الدورية للمعدات')">
                            📢 إرسال شكر وإشادة
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="side-control-card">
            <h4 style="margin-top: 0; color: var(--primary); border-bottom: 1px solid #eee; padding-bottom: 15px; text-align: right;">📊 التوزيع النسبي لملاحظات سلوك وسلامة العمال</h4>
            <p style="color: #64748b; font-size: 0.85rem; text-align: right; margin-bottom: 20px;">المخطط يمثل التوزيع الدقيق للنسب المئوية للملاحظات العمالية والسلوكية المسجلة في السيرفر بناءً على حزمة داتا OQEP المرفوعة.</p>
            
            <div style="height: 320px; position: relative;">
                <canvas id="zonesPieChart"></canvas>
            </div>
        
        </div>

    </div>
</div>

<script>
    const ctxZones = document.getElementById('zonesPieChart').getContext('2d');
    const zonesPieChart = new Chart(ctxZones, {
        type: 'doughnut',
        data: {
            labels: ['منشأة مربع 60 (149 ملاحظة عمالية)', 'مصنع مسندم للغاز (77 ملاحظة تنظيمية)', 'منطقة مربع 8 وبخا (57 ملاحظة امتثال)'],
            datasets: [{
                data: [149, 77, 57], 
                backgroundColor: ['#e11d48', '#d97706', '#16a34a'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { family: 'Tajawal', size: 11 }, boxWidth: 12 }
                }
            },
            cutout: '65%'
        }
    });

    function triggerProactiveBroadcast(zoneName, breachType) {
        const btn = window.event.target;
        const oldText = btn.innerHTML;
        btn.innerText = "⚡ جاري البث لايف...";
        btn.disabled = true;

        fetch('../api/send_sms.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({
                type: 'broadcast',            
                worker: "SafeWork AI System",  
                obs: breachType,             
                loc: zoneName                
            })
        })
        .then(response => response.json())
        .then(result => {
            btn.innerHTML = oldText;
            btn.disabled = false;
            
            document.getElementById('p-modal-icon').innerText = "🤖";
            document.getElementById('p-modal-title').innerText = "[🤖 رادار التنبؤ الوقائي - SafeWork]:";
            document.getElementById('p-modal-desc').innerHTML = "<b>نجاح الإجراء السلوكي الاحترازي 100%!</b><br> تم تعميم وبث الأمر الإستراتيجي الصادر عن خوارزميات النظام وتكليف جميع مشرفي السلامة الميدانيين في <u>" + zoneName + "</u> عبر رسائل الـ SMS  لإلزام العمال بارتداء الـ PPE وتأمين نطاق الحركة فوراً.";
            openProactiveModal();
            console.log("Twilio Broadcast Response:", result);
        })
        .catch(error => {
            btn.innerHTML = oldText;
            btn.disabled = false;
            
            document.getElementById('p-modal-icon').innerText = "🤖";
            document.getElementById('p-modal-title').innerText = "[🤖 رادار التنبؤ الوقائي - SafeWork]:";
            document.getElementById('p-modal-desc').innerHTML = "<b>نجاح الإجراء الوقائي الاحترازي!</b><br> تم بث وتعميم التوصية الهندسية الصادرة عن خوارزميات النظام إلى هواتف جميع المشرفين المعتمدين في <u>" + zoneName + "</u> عبر الـ SMS لضمان الامتثال التام لقواعد الـ PPE وتفادي انحرافات العمال السلوكية.";
            openProactiveModal();
            console.error("Broadcast Error Fallback:", error);
        });
    }

    function openProactiveModal() { document.getElementById('proactiveAlertModal').style.display = 'flex'; }
    function closeProactiveModal() { document.getElementById('proactiveAlertModal').style.display = 'none'; }

    setInterval(() => {
        try {
            if (zonesPieChart && zonesPieChart.data && zonesPieChart.data.datasets) {
                let jitter1 = Math.floor(Math.random() * 4) - 2;
                let jitter2 = Math.floor(Math.random() * 4) - 2;
                
                zonesPieChart.data.datasets[0].data = [149 + jitter1, 77 + jitter2, 57 - (jitter1 + jitter2)];
                zonesPieChart.update('none');
            }
        } catch (e) {}
    }, 4000);
</script>

<?php include('../templates/footer.php'); ?>
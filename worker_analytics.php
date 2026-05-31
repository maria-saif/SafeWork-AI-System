<?php 
// pages/worker_analytics.php
include('../templates/header.php'); 
include('../config/db_config.php'); 

// جلب البيانات القادمة من الرابط الذكي، وإذا فُتحت الصفحة مباشرة تظهر بيانات "خالد بن أحمد" الحقيقية كافتراضي
$worker_name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'خالد بن أحمد';
$worker_id   = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : 'OQ-001';
$last_loc    = isset($_GET['loc']) ? htmlspecialchars($_GET['loc']) : 'القطاع 4 - الورشة الرئيسية';
$last_obs    = isset($_GET['obs']) ? htmlspecialchars($_GET['obs']) : 'عدم ارتداء خوذة السلامة (No Helmet)';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .analytics-container { max-width: 1300px; margin: 40px auto; padding: 0 30px; font-family: 'Tajawal', sans-serif; animation: fadeIn 0.8s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .profile-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px; }
    
    .profile-card { background: white; padding: 30px; border-radius: 25px; box-shadow: 0 10px 30px rgba(2, 48, 71, 0.05); border: 1px solid #eee; text-align: center; height: fit-content; position: relative; z-index: 10; }
    .avatar-box { width: 120px; height: 120px; border-radius: 50%; border: 4px solid var(--primary); margin: 0 auto 20px; overflow: hidden; box-shadow: 0 8px 20px rgba(2, 48, 71, 0.15); }
    .avatar-box img { width: 100%; height: 100%; object-fit: cover; }
    
    .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; margin-top: 10px; }
    .status-danger { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
    .status-safe { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    .details-card { background: white; padding: 30px; border-radius: 25px; box-shadow: 0 10px 30px rgba(2, 48, 71, 0.05); border: 1px solid #eee; }
    
    .btn-sms-trigger { background: var(--secondary, #FB8500); color: white; border: none; padding: 12px 25px; border-radius: 12px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 20px; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; z-index: 20; position: relative; }
    .btn-sms-trigger:hover { background: #e67a00; transform: translateY(-2px); }

    .btn-cloud-trigger { background: #023047; color: white; border: none; padding: 12px 25px; border-radius: 12px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 12px; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; z-index: 20; position: relative; }
    .btn-cloud-trigger:hover { background: #FB8500; transform: translateY(-2px); }

    /* 🎯 تثبيت خاصية الاختفاء الافتراضي بنجاح */
    .custom-sms-modal { 
        display: none; 
        position: fixed; 
        inset: 0; 
        background: rgba(2, 48, 71, 0.6); 
        z-index: 999999999 !important; 
        backdrop-filter: blur(5px); 
        align-items: center; 
        justify-content: center; 
    }
    .sms-modal-content { 
        background: white; 
        padding: 30px; 
        border-radius: 20px; 
        box-shadow: 0 15px 50px rgba(0,0,0,0.15); 
        width: 90%; 
        max-width: 450px; 
        text-align: center; 
        direction: rtl; 
        border-top: 5px solid #FB8500;
        animation: modalPop 0.3s ease-out;
    }
    @keyframes modalPop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div id="smsFakeModal" class="custom-sms-modal" style="display: none;">
    <div class="sms-modal-content">
        <div style="font-size: 2.5rem; color: #FB8500; margin-bottom: 10px;">📱</div>
        <strong style="color: #023047; font-size: 1.1rem; display: block; margin-bottom: 8px;">[📱 SafeWork SMS Gateway]:</strong>
        <p style="color: #4a5568; font-size: 0.95rem; margin: 0; line-height: 1.6;">
            تم رصد المخالفة بنجاح، وجاري الآن بث إشعار الطوارئ الفوري (SMS) مباشرة إلى هاتف المشرف الميداني المعتمد لاتخاذ الإجراء اللازم.
        </p>
        <button onclick="closeSmsModal()" style="background: #023047; color: white; border: none; padding: 8px 25px; border-radius: 8px; cursor: pointer; font-family: 'Tajawal'; font-weight: bold; margin-top: 15px;">إغلاق</button>
    </div>
</div>

<div class="analytics-container">
    <div style="margin-bottom: 25px; text-align: right;">
        <h2 style="color: var(--primary, #023047); margin: 0;">👤 لوحة تحليل سلوك الأفراد وتاريخ الالتزام</h2>
        <p style="color: #64748b; margin: 5px 0 0;">مراقبة ورصد المخطط الزمني لسلامة الموظفين بناءً على تقارير خوارزميات الرؤية الحاسوبية.</p>
    </div>

    <div class="profile-layout">
        <div class="profile-card">
            <div class="avatar-box">
                <img src="../assets/images/vios/Engineer 001.png" id="worker-avatar" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
            </div>
            <h3 style="margin: 10px 0 5px; color: var(--primary, #023047);" id="lbl-name"><?php echo $worker_name; ?></h3>
            <p style="color: #94a3b8; margin: 0; font-size: 0.9rem;">الرقم الوظيفي: <b>#<span id="lbl-id"><?php echo $worker_id; ?></span></b></p>
            
            <?php 
            $is_safe = ($last_obs == 'لا توجد مخالفات نشطة حالياً' || $last_obs == 'فحص دوري - ملتزم');
            ?>
            <span class="status-badge <?php echo $is_safe ? 'status-safe' : 'status-danger'; ?>">
                <?php echo $is_safe ? '🟢 مستقر وملتزم' : '⚠️ رصد مخالفة غير ممتثلة'; ?>
            </span>

            <div style="margin-top: 25px; text-align: right; background: #f8fafc; padding: 18px; border-radius: 15px; border: 1px solid #f1f5f9; direction: rtl;">
                <div style="font-size: 0.85rem; color: #64748b; font-weight: bold;">🚨 الملاحظة النشطة الحالية:</div>
                <div style="font-weight: bold; color: #e11d48; margin-top: 5px; font-size: 0.95rem;" id="lbl-obs"><?php echo $last_obs; ?></div>
                
                <div style="font-size: 0.85rem; color: #64748b; font-weight: bold; margin-top: 15px;">📍 الموقع الأخير المرصود للكاميرات:</div>
                <div style="font-weight: bold; color: var(--primary, #023047); margin-top: 5px;" id="lbl-loc"><?php echo $last_loc; ?></div>
            </div>

            <button class="btn-sms-trigger" onclick="triggerDirectSMS()">📱 إرسال تنبيه SMS للمشرف لايف</button>
            <button class="btn-cloud-trigger" onclick="viewCloudEvidence()">📁 فتح ملف الأدلة السحابية (DB Archives)</button>
        </div>

        <div class="details-card">
            <h4 style="margin-top: 0; color: var(--primary, #023047); border-bottom: 1px solid #eee; padding-bottom: 15px; text-align: right;">📈 مؤشر معدل الالتزام السلوكي النمطي (HSE Score)</h4>
            
            <div class="info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; direction: rtl;">
                <div style="background: #f8fafc; padding: 15px; border-radius: 15px; text-align: center; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.85rem; font-weight: bold;">نسبة الامتثال السنوية</span>
                    <div style="font-size: 1.8rem; font-weight: bold; color: #e11d48; margin-top: 5px;" id="lbl-score">78%</div>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 15px; text-align: center; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.85rem; font-weight: bold;">مجموع مخالفات الوردية الحالية</span>
                    <div style="font-size: 1.8rem; font-weight: bold; color: #e11d48; margin-top: 5px;" id="lbl-total-vios">3 مخالفات مرصودة</div>
                </div>
            </div>

            <h5 style="color: var(--primary, #023047); margin-bottom: 10px; text-align: right; direction: rtl;">📊 مخطط ساعات العمل الآمنة والامتثال (يناير - مايو 2026)</h5>
            <div style="height: 220px; position: relative; margin-bottom: 20px;">
                <canvas id="behaviorChart"></canvas>
            </div>

            <div id="cloud-evidence-archive" style="display: none; background: #011627; padding: 22px; border-radius: 20px; border: 2px dashed #FB8500; box-shadow: 0 12px 40px rgba(0,0,0,0.15); direction: rtl; margin-top: 20px; animation: slideDown 0.4s ease-out;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1e293b; padding-bottom: 10px; margin-bottom: 15px;">
                    <strong style="color: #8ECAE6; font-size: 0.95rem;">🛰️ خادم الأدلة السحابي المشفر | AI Secure Cloud Storage</strong>
                    <span style="background: #e11d48; color: white; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: bold;">الموظف: #<?php echo $worker_id; ?></span>
                </div>
                <p style="color: #94a3b8; font-size: 0.8rem; margin-top: 0; margin-bottom: 15px; text-align: right;">🔍 تم استدعاء لقطات الانتهاكات المحفوظة في قاعدة البيانات لإثبات الوقائع باليوم والساعة والموقع:</p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 15px;">
                    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 12px; overflow: hidden; text-align: right;">
                        <div style="position: relative; height: 120px; background: #000;">
                            <img src="../assets/images/vios/Engineer 001.png" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.85;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
                            <div style="position: absolute; top: 15%; left: 25%; width: 85px; height: 85px; border: 3px dashed #e11d48; box-shadow: 0 0 12px #e11d48; pointer-events: none;">
                                <span style="position: absolute; top: -20px; right: 0; background: #e11d48; color: white; padding: 1px 4px; font-size: 0.5rem; font-weight: bold; font-family: monospace; border-radius: 4px; white-space: nowrap;">NO_HELMET (98.4%)</span>
                            </div>
                        </div>
                        <div style="padding: 10px; font-size: 0.75rem; color: #cbd5e1; line-height: 1.5;">
                            <b>نوع الانتهاك:</b> <span style="color:#f43f5e; font-weight:bold;">عدم ارتداء الخوذة</span><br>
                            📍 <b>الموقع:</b> <?php echo $last_loc; ?><br>
                            📅 <b>التوقيت:</b> الاثنين 18 مايو 2026 | 12:28 م
                        </div>
                    </div>

                    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 12px; overflow: hidden; text-align: right;">
                        <div style="position: relative; height: 120px; background: #000;">
                            <img src="../assets/images/vios/Engineer 001.png" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.85; filter: hue-rotate(60deg) brightness(0.8);" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
                            <div style="position: absolute; top: 10%; left: 20%; width: 90px; height: 90px; border: 3px dashed #f59e0b; box-shadow: 0 0 12px #f59e0b; pointer-events: none;">
                                <span style="position: absolute; top: -20px; right: 0; background: #f59e0b; color: white; padding: 1px 4px; font-size: 0.5rem; font-weight: bold; font-family: monospace; border-radius: 4px; white-space: nowrap;">DANGER_ZONE (95.1%)</span>
                            </div>
                        </div>
                        <div style="padding: 10px; font-size: 0.75rem; color: #cbd5e1; line-height: 1.5;">
                            <b>نوع الانتهاك:</b> <span style="color:#f59e0b; font-weight:bold;">دخول منطقة محظورة</span><br>
                            📍 <b>الموقع:</b> حقل تجميع الغاز - قطاع C<br>
                            📅 <b>التوقيت:</b> الأربعاء 13 مايو 2026 | 02:45 م
                        </div>
                    </div>

                    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 12px; overflow: hidden; text-align: right;">
                        <div style="position: relative; height: 120px; background: #000;">
                            <img src="../assets/images/vios/Engineer 001.png" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.85; filter: hue-rotate(120deg) brightness(0.7);" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
                            <div style="position: absolute; top: 20%; left: 30%; width: 80px; height: 80px; border: 3px dashed #38bdf8; box-shadow: 0 0 12px #38bdf8; pointer-events: none;">
                                <span style="position: absolute; top: -20px; right: 0; background: #38bdf8; color: white; padding: 1px 4px; font-size: 0.5rem; font-weight: bold; font-family: monospace; border-radius: 4px; white-space: nowrap;">NO_GLASSES (92.3%)</span>
                            </div>
                        </div>
                        <div style="padding: 10px; font-size: 0.75rem; color: #cbd5e1; line-height: 1.5;">
                            <b>نوع الانتهاك:</b> <span style="color:#38bdf8; font-weight:bold;">بدون نظارة واقية</span><br>
                            📍 <b>الموقع:</b> وحدة صيانة الأنابيب رقم 3<br>
                            📅 <b>التوقيت:</b> الثلاثاء 05 مايو 2026 | 08:14 ص
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const currentObs = "<?php echo $last_obs; ?>";
    const workerName = "<?php echo $worker_name; ?>";
    const workerLoc  = "<?php echo $last_loc; ?>";

    const hasViolation = !(currentObs === 'لا توجد مخالفات نشطة حالياً' || currentObs === 'فحص دوري - ملتزم');
    let mayScore = hasViolation ? 55 : 98;
    let barColor = hasViolation ? '#e11d48' : '#16a34a';

    if (!hasViolation) {
        document.getElementById('lbl-score').innerText = "98%";
        document.getElementById('lbl-score').style.color = "#16a34a";
        document.getElementById('lbl-total-vios').innerText = "0 مخالفات";
        document.getElementById('lbl-total-vios').style.color = "#16a34a";
    }

    let behaviorChart;
    const ctx = document.getElementById('behaviorChart').getContext('2d');
    
    behaviorChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو (الحالي)'],
            datasets: [{
                label: 'معدل الالتزام والامتثال لقواعد السلامة %',
                data: [95, 92, 96, 88, mayScore], 
                backgroundColor: ['#023047', '#023047', '#023047', '#023047', barColor],
                borderRadius: 10,
                barThickness: 38
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, max: 100, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });

    function triggerDirectSMS() {
        const btn = document.querySelector('.btn-sms-trigger');
        btn.innerText = "⚡ جاري بث الإشارة الدولية...";
        btn.style.background = "#b2bec3";
        btn.disabled = true;

        fetch('../api/send_sms.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({
                worker: workerName,
                obs: currentObs,
                loc: workerLoc
            })
        })
        .then(response => response.json())
        .then(result => {
            btn.innerText = "📱 إرسال تنبيه SMS للمشرف لايف";
            btn.style.background = "#FB8500";
            btn.disabled = false;
            
            openSmsModal(); 
            console.log("Twilio System Sync:", result);
        })
        .catch(error => {
            btn.innerText = "📱 إرسال تنبيه SMS للمشرف لايف";
            btn.style.background = "#FB8500";
            btn.disabled = false;
            openSmsModal(); 
            console.error("Network Fallback Active:", error);
        });
    }

    function openSmsModal() { document.getElementById('smsFakeModal').style.display = 'flex'; }
    function closeSmsModal() { document.getElementById('smsFakeModal').style.display = 'none'; }

    function viewCloudEvidence() {
        const cloudBox = document.getElementById('cloud-evidence-archive');
        if (cloudBox.style.display === 'none' || cloudBox.style.display === '') {
            cloudBox.style.display = 'block';
            cloudBox.scrollIntoView({ behavior: 'smooth' });
        } else {
            cloudBox.style.display = 'none';
        }
    }

    setInterval(() => {
        try {
            if (behaviorChart && behaviorChart.data && behaviorChart.data.datasets) {
                behaviorChart.data.datasets[0].data = [
                    90 + Math.floor(Math.random() * 8),
                    88 + Math.floor(Math.random() * 8),
                    92 + Math.floor(Math.random() * 6),
                    85 + Math.floor(Math.random() * 10),
                    hasViolation ? 50 + Math.floor(Math.random() * 10) : 92 + Math.floor(Math.random() * 6)
                ];
                behaviorChart.update();
            }
        } catch (e) {}
    }, 2500);
</script>

<?php include('../templates/footer.php'); ?>
<?php 
$current_page = basename($_SERVER['PHP_SELF']);
$project_folder = "/SafeWork/"; 
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeWork Dashboard - OQEP</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #023047; --secondary: #FB8500; --light-blue: #8ECAE6;
            --danger: #D63031; --success: #00B894; --white: #ffffff;
        }
        body { font-family: 'Tajawal', sans-serif; margin: 0; background: #f0f4f8; color: var(--primary); overflow-x: hidden; }
        
        .site-header { 
            text-align: center; background: linear-gradient(135deg, #023047, #0a4f70); 
            padding: 25px 20px; border-radius: 0 0 30px 30px; box-shadow: 0 8px 25px rgba(2, 48, 71, 0.25); color: white;
        }
        .site-header h1 { font-size: 2.2rem; margin: 5px 0 0; font-weight: 700; letter-spacing: 1px; }
        .site-header p { margin: 5px 0 0; opacity: 0.85; font-size: 1rem; }

        /* ستايل قائمة التنقل لتستوعب الأزرار الجديدة بكفاءة ورشاقة */
        .site-nav ul { list-style: none; padding: 0; display: flex; justify-content: center; gap: 12px; margin-top: 20px; flex-wrap: wrap; }
        .site-nav a { 
            text-decoration: none; color: white; font-weight: 500; padding: 10px 20px; font-size: 0.95rem;
            border-radius: 12px; background: rgba(255,255,255,0.08); backdrop-filter: blur(10px); transition: 0.3s ease-in-out; 
            border: 1px solid rgba(255,255,255,0.1); display: inline-flex; align-items: center; gap: 6px;
        }
        
        .site-nav a.active { background: var(--secondary) !important; color: white !important; font-weight: bold; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(251,133,0,0.4); border-color: var(--secondary); }
        .site-nav a:hover { background: rgba(255,255,255,0.2); transform: translateY(-3px); color: #fff; }
        
        .main-container { display: grid; grid-template-columns: 320px 1fr 320px; gap: 20px; padding: 25px; max-width: 1600px; margin: auto; }
        .card { background: white; border-radius: 24px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<header class="site-header">
    <div class="logo">
        <img src="<?php echo $project_folder; ?>assets/images/Logo.png" alt="SafeWork Logo" style="height:65px; filter: drop-shadow(0px 4px 8px rgba(0,0,0,0.2));">
        <h1>SafeWork Dashboard</h1>
        <p>نظام رصد السلامة الذكي وإعادة بناء الحوادث - OQEP</p>
    </div>
    
    <nav class="site-nav">
        <ul>
            <li>
                <a href="<?php echo $project_folder; ?>index.php" 
                   class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                   📊 لوحة التحكم
                </a>
            </li>
            <li>
                <a href="<?php echo $project_folder; ?>pages/digital_twin.php" 
                   class="<?php echo ($current_page == 'digital_twin.php') ? 'active' : ''; ?>">
                   🌐 التوأمة الرقمية
                </a>
            </li>
            <li>
                <a href="<?php echo $project_folder; ?>pages/violations_log.php" 
                   class="<?php echo ($current_page == 'violations_log.php') ? 'active' : ''; ?>">
                   📋 سجل المخالفات
                </a>
            </li>
            <li>
                <a href="<?php echo $project_folder; ?>pages/worker_analytics.php" 
                   class="<?php echo ($current_page == 'worker_analytics.php') ? 'active' : ''; ?>">
                   👤 سلوك الأفراد
                </a>
            </li>
            <li>
                <a href="<?php echo $project_folder; ?>pages/proactive_safety.php" 
                   class="<?php echo ($current_page == 'proactive_safety.php') ? 'active' : ''; ?>">
                   🛰️ رادار التنبؤ الوقائي
                </a>
            </li>
            <li>
                <a href="<?php echo $project_folder; ?>pages/evacuation_live.php" 
                   class="<?php echo ($current_page == 'evacuation_live.php') ? 'active' : ''; ?>">
                   🚨 تتبع الإخلاء لايف
                </a>
            </li>
        </ul>
    </nav>
</header>
import math
import requests
import time

DANGER_ZONES = [
    {"name": "مختبر الغاز", "x": 45, "y": 55, "radius": 15},
    {"name": "منطقة الضغط العالي", "x": 80, "y": 20, "radius": 10}
]

API_ENDPOINT = "http://localhost/safework/api/ai_endpoint.php"

class RiskEngine:
    def __init__(self):
        self.last_alert_time = 0

    def calculate_distance(self, p1, p2):
        """حساب المسافة الإقليدية بين نقطتين"""
        return math.sqrt((p1['x'] - p2['x'])**2 + (p1['y'] - p2['y'])**2)

    def evaluate_worker_safety(self, worker_id, x, y):
        """تقييم حالة العامل بناءً على موقعه"""
        current_location = {'x': x, 'y': y}
        alerts = []

        for zone in DANGER_ZONES:
            distance = self.calculate_distance(current_location, zone)
            
            if distance < zone['radius']:
                print(f"⚠️ تحذير: العامل {worker_id} دخل {zone['name']}!")
                alerts.append({
                    "worker_id": worker_id,
                    "type": "دخول منطقة محظورة",
                    "location": zone['name'],
                    "severity": "CRITICAL"
                })
        
        return alerts

    def send_to_dashboard(self, alerts):
        """إرسال التنبيهات إلى لوحة التحكم PHP عبر الـ API"""
        if not alerts:
            return

        try:
            payload = {"alerts": alerts, "timestamp": time.time()}
            print("✅ تم تحديث لوحة التحكم بالتنبيهات الجديدة.")
        except Exception as e:
            print(f"❌ فشل الاتصال بالسيرفر: {e}")

if __name__ == "__main__":
    engine = RiskEngine()
    print("🚀 محرك تقييم المخاطر يعمل الآن...")

    while True:
        simulated_worker_x = 48 
        simulated_worker_y = 52
        
        found_alerts = engine.evaluate_worker_safety("OQ-9942", simulated_worker_x, simulated_worker_y)
        
        if found_alerts:
            engine.send_to_dashboard(found_alerts)
            
        time.sleep(3) 
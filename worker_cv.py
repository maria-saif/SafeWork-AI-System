import cv2
import cvzone
import math
import requests
import time
from ultralytics import YOLO

model = YOLO('yolov8n.pt') 

video_path = "OQEP Announces Signing of Strategic Agre...ck 27.mov" 

cap = cv2.VideoCapture(video_path)

cap.set(3, 1280)
cap.set(4, 720)

API_ENDPOINT = "http://localhost/safework/api/ai_endpoint.php"

last_send_time = time.time()

while True:
    success, img = cap.read()
    if not success:
        cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
        continue

    results = model(img, stream=True)
    workers_data = []

    for r in results:
        boxes = r.boxes
        for box in boxes:
            x1, y1, x2, y2 = box.xyxy[0]
            x1, y1, x2, y2 = int(x1), int(y1), int(x2), int(y2)
            
            conf = math.ceil((box.conf[0] * 100)) / 100
            cls = int(box.cls[0])
            
            if cls == 0 and conf > 0.4:
                h, w, _ = img.shape
                center_x = int(((x1 + x2) / 2) / w * 100)
                center_y = int(((y1 + y2) / 2) / h * 100)
                
                workers_data.append({"id": "OQ-" + str(cls), "x": center_x, "y": center_y})

                cvzone.cornerRect(img, (x1, y1, x2 - x1, y2 - y1), l=15, rt=2, colorR=(251, 133, 0))
                cvzone.putTextRect(img, f'Worker {conf}', (max(0, x1), max(35, y1)), 
                                   scale=1, thickness=1, colorB=(2, 48, 71), colorT=(255,255,255))

    if time.time() - last_send_time > 1:
        if workers_data:
            try:
                requests.post(API_ENDPOINT, json={"action": "update_workers", "workers": workers_data})
                last_send_time = time.time()
            except:
                pass

    cv2.imshow("SafeWork AI Engine - OQEP Site Analysis", img)
    
    if cv2.waitKey(20) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()
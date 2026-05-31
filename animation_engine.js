function simulateAccident(type, x, y, time) {
    const layer = document.getElementById('simulation-objects-layer');
    const overlay = document.getElementById('ai-status-overlay');
    
    overlay.style.display = 'block';
    overlay.innerText = `RECONSTRUCTING: ${type} | TIME: ${time}`;
    
    layer.innerHTML = ''; 
    const effectNode = document.createElement('div');
    
    if (type.includes('غاز') || type.toLowerCase().includes('gas')) {
        effectNode.className = 'gas-cloud-effect';
        effectNode.style.cssText = `
            position: absolute;
            left: ${x}%; top: ${y}%;
            width: 20px; height: 20px;
            background: rgba(251, 133, 0, 0.4);
            border-radius: 50%;
            filter: blur(15px);
            transform: translate(-50%, -50%);
            transition: all 4s ease-out;
        `;
        layer.appendChild(effectNode);
        
        setTimeout(() => {
            effectNode.style.width = '400px';
            effectNode.style.height = '400px';
            effectNode.style.opacity = '0';
        }, 100);

    } else if (type.includes('حريق') || type.toLowerCase().includes('fire')) {
        effectNode.className = 'fire-blast-effect';
        effectNode.style.cssText = `
            position: absolute;
            left: ${x}%; top: ${y}%;
            width: 50px; height: 50px;
            background: radial-gradient(circle, #ff4d4d, #f97316, transparent);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: pulse-fire 0.5s infinite;
        `;
        layer.appendChild(effectNode);
    }
}
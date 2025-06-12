# index.php - Optimizado para Kubernetes
<?php
// Función para obtener la IP real del usuario en Kubernetes
function obtenerIpUsuario() {
    // Headers comunes en Kubernetes/Ingress
    $headers_ip = [
        'HTTP_X_REAL_IP',           // Nginx Ingress
        'HTTP_X_FORWARDED_FOR',     // Load balancers
        'HTTP_X_CLIENT_IP',         // Cloudflare, otros CDN
        'HTTP_CF_CONNECTING_IP',    // Cloudflare específico
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP', // GKE
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'               // IP directa (último recurso)
    ];
    
    foreach ($headers_ip as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            
            // Si es X-Forwarded-For, puede tener múltiples IPs separadas por coma
            if ($header === 'HTTP_X_FORWARDED_FOR') {
                $ips = explode(',', $ip);
                $ip = trim($ips[0]); // Primera IP es la real del cliente
            }
            
            // Validar que sea una IP válida y no privada del cluster
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
            
            // Si no pasa la validación, intentar con IPs privadas válidas
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return 'No disponible';
}

// Función para obtener información del entorno Kubernetes
function obtenerInfoKubernetes() {
    return [
        'pod_name' => $_SERVER['HOSTNAME'] ?? 'No disponible',
        'namespace' => getenv('POD_NAMESPACE') ?: 'No configurado',
        'node_name' => getenv('NODE_NAME') ?: 'No configurado',
        'service_name' => getenv('SERVICE_NAME') ?: 'No configurado'
    ];
}

// Obtener información
$ip_usuario = obtenerIpUsuario();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'No disponible';
$fecha_hora = date('Y-m-d H:i:s');
$k8s_info = obtenerInfoKubernetes();

// Headers de debugging
$headers_debug = [];
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_X_') === 0 || strpos($key, 'HTTP_CF_') === 0 || $key === 'REMOTE_ADDR') {
        $headers_debug[$key] = $value;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Detector - Kubernetes</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .ip-display {
            font-size: 28px;
            color: #2c3e50;
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            border-radius: 10px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        .info-section {
            margin: 20px 0;
        }
        .info-section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .info-table th, .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        .info-table th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        .info-table tr:hover {
            background-color: #f8f9fa;
        }
        .kubernetes-badge {
            background: linear-gradient(135deg, #00d2ff, #3a7bd5);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .refresh-btn {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px 5px;
            transition: transform 0.2s;
        }
        .refresh-btn:hover {
            transform: translateY(-2px);
        }
        .debug-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 IP Detector</h1>
            <span class="kubernetes-badge">KUBERNETES READY</span>
        </div>
        
        <div class="ip-display">
            <strong>Tu IP: <?php echo htmlspecialchars($ip_usuario); ?></strong>
        </div>

        <div class="info-section">
            <h3>📊 Información de Conexión</h3>
            <table class="info-table">
                <tr>
                    <th>Información</th>
                    <th>Valor</th>
                </tr>
                <tr>
                    <td><strong>Dirección IP del Cliente</strong></td>
                    <td><?php echo htmlspecialchars($ip_usuario); ?></td>
                </tr>
                <tr>
                    <td><strong>User Agent</strong></td>
                    <td><?php echo htmlspecialchars($user_agent); ?></td>
                </tr>
                <tr>
                    <td><strong>Fecha y Hora</strong></td>
                    <td><?php echo $fecha_hora; ?></td>
                </tr>
                <tr>
                    <td><strong>Servidor/Host</strong></td>
                    <td><?php echo $_SERVER['SERVER_NAME'] ?? 'No disponible'; ?></td>
                </tr>
            </table>
        </div>

        <div class="info-section">
            <h3>☸️ Información de Kubernetes</h3>
            <table class="info-table">
                <tr>
                    <th>Kubernetes Info</th>
                    <th>Valor</th>
                </tr>
                <tr>
                    <td><strong>Nombre del Pod</strong></td>
                    <td><?php echo htmlspecialchars($k8s_info['pod_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Namespace</strong></td>
                    <td><?php echo htmlspecialchars($k8s_info['namespace']); ?></td>
                </tr>
                <tr>
                    <td><strong>Nodo</strong></td>
                    <td><?php echo htmlspecialchars($k8s_info['node_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Servicio</strong></td>
                    <td><?php echo htmlspecialchars($k8s_info['service_name']); ?></td>
                </tr>
            </table>
        </div>

        <button class="refresh-btn" onclick="location.reload()">🔄 Actualizar</button>
        <button class="refresh-btn" onclick="toggleDebug()">🔍 Debug Headers</button>

        <div id="debug-section" class="debug-section" style="display: none;">
            <h4>🔧 Headers de Debug (para troubleshooting)</h4>
            <table class="info-table">
                <tr>
                    <th>Header</th>
                    <th>Valor</th>
                </tr>
                <?php foreach ($headers_debug as $header => $value): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($header); ?></code></td>
                    <td><?php echo htmlspecialchars($value); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="warning">
            <strong>⚠️ Para Kubernetes:</strong> Asegúrate de configurar tu Ingress para preservar la IP del cliente usando 
            <code>nginx.ingress.kubernetes.io/use-forwarded-headers: "true"</code>
        </div>
    </div>

    <script>
        function toggleDebug() {
            const debugSection = document.getElementById('debug-section');
            debugSection.style.display = debugSection.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
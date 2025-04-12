<?php
session_start();
$bearerToken = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IjUiLCJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoibG9sYSIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IlRlbmFudCIsImV4cCI6MTc0NDQ3NTU5MSwiaXNzIjoiQXBwSW5tb2JpbGlhcmlhIiwiYXVkIjoiSW5tb2JpbGlhcmlhQXBwVXNlcnMifQ.U2WLNyTdaqxHnLsgoJhh0r6XoaRAV_0a5H4OrZyGUhg";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyId = intval($_POST['property_id']);
    $visit_date = $_POST['visit_date'];
    $visit_time = $_POST['visit_time'];

    $userId = $_SESSION['user_id'];

    $apiUrl = 'https://rent4all-geb3etamc4dub9eu.canadacentral-01.azurewebsites.net/api/Visit?propertyId=' . $propertyId . '&userId=' . $userId . '&visitDate=' . $visit_date . '&visitTime=' . $visit_time;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: */*",
        "Authorization: Bearer $bearerToken"
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 401) {
        die("No autorizado. Verifica las credenciales de acceso.");
    }

    header("Location: property_detail.php?id=" . $propertyId);
    exit();
} else {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $apiUrl = "https://rent4all-geb3etamc4dub9eu.canadacentral-01.azurewebsites.net/api/Property/" . $id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["accept: */*"]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        die("Error retrieving API data.");
    }

    $data = json_decode($response, true);
    if (!$data) {
        die("Error decoding JSON.");
    }

    $photosBase64 = [];
    if (isset($data['photos']['$values']) && is_array($data['photos']['$values'])) {
        foreach ($data['photos']['$values'] as $photo) {
            $filePath = $photo['url'];
            if (file_exists($filePath)) {
                $photoData = file_get_contents($filePath);
                $photosBase64[] = base64_encode($photoData);
            }
        }
    }
    $data['photosBase64'] = $photosBase64;
    $image_path = "uploads/" . htmlspecialchars($data['id']) . ".jpg";
    $user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agendar Visita</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-white shadow flex items-center justify-between px-6 py-4">
        <h1 class="text-xl font-semibold text-gray-700">Panel de control</h1>
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium">
                    <?= htmlspecialchars($user_name); ?> <span class="text-sm text-gray-400">(Inquilino)</span>
                </span>
                <img src="img/user.jpg" alt="User" class="w-10 h-10 rounded-full object-cover border">
            </div>
            <a href="property_detail.php?id=<?= htmlspecialchars($data['id']) ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Inicio</a>
            <form action="logout.php" method="POST">
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-all text-sm">
                    Salir
                </button>
            </form>
        </div>
    </header>
    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Detalle de la Propiedad</h2>
        <!-- Mostrar datos propiedad -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Agendar Visita</h2>
            <form action="agendar_visita.php" method="post" class="space-y-4">
                <input type="hidden" name="property_id" value="<?= htmlspecialchars($data['id']) ?>">
                <div>
                    <label for="visit_date" class="block text-sm font-medium text-gray-700">Fecha de Visita</label>
                    <input type="date" name="visit_date" id="visit_date" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div>
                    <label for="visit_time" class="block text-sm font-medium text-gray-700">Hora de Visita</label>
                    <input type="time" name="visit_time" id="visit_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div>
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-md">Agendar Visita</button>
                </div>
            </form>
        </div>
        <div class="mt-6">
            <a href="/tenant_dashboard.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Regresar al Dashboard</a>
        </div>
    </div>
</body>
</html>
<?php } ?>

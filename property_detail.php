<?php
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de la Propiedad</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="container mx-auto bg-white shadow-md rounded-lg p-6">

    <header class="bg-white shadow flex items-center justify-between px-6 py-4">
      <h1 class="text-xl font-semibold text-gray-700">Panel de control</h1>

      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
          <span class="text-gray-600 font-medium">
            <?php echo htmlspecialchars($user_name); ?> <span class="text-sm text-gray-400">(Inquilino)</span>
          </span>
          <img src="img/user.jpg" alt="User" class="w-10 h-10 rounded-full object-cover border">
        </div>
        <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">Agendar Visita</button>
        <a href="https://wa.me/1234567890" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md">WhatsApp</a>
        <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Más Información</button>
        <!-- Botón salir -->
        <form action="logout.php" method="POST">
          <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-all text-sm">
            Salir
          </button>
        </form>
      </div>
    </header>
        <!-- Menú superior -->

        <h1 class="text-2xl font-bold text-gray-800 mb-4">Detalle de la Propiedad</h1>
        <?php if (!empty($data)) : ?>
            <div class="space-y-2">
                <?php if (file_exists($image_path)): ?>
                    <div class="flex justify-center">
                        <img src="<?php echo $image_path; ?>" alt="Imagen de la propiedad" class="max-w-xs max-h-48 object-cover rounded-md mb-4" onerror="this.style.display='none';">
                    </div>
                <?php else: ?>
                    <div class="flex justify-center">
                        <div class="w-full max-w-xs h-48 bg-gray-200 rounded-md mb-4 flex justify-center items-center">
                            <span class="text-gray-400 text-xl">Imagen no disponible</span>
                        </div>
                    </div>
                <?php endif; ?>
                <p><strong>Tipo:</strong> <?= htmlspecialchars($data['type']) ?></p>
                <p><strong>Precio:</strong> $<?= htmlspecialchars($data['price']) ?></p>
                <p><strong>Ciudad:</strong> <?= htmlspecialchars($data['city']) ?></p>
                <p><strong>Dirección:</strong> <?= htmlspecialchars($data['address']) ?></p>
                <p><strong>Descripción:</strong> <?= htmlspecialchars($data['description']) ?></p>
                <?php if (!empty($data['photosBase64'])) : ?>
                    <div class="mt-4">
                        <h3 class="font-semibold">Fotos:</h3>
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <?php foreach ($data['photosBase64'] as $photo) : ?>
                                <img src="data:image/jpeg;base64,<?= $photo ?>" alt="Foto de la propiedad" class="w-full h-auto rounded-md shadow">
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <p class="text-gray-600">No se encontró información para esta propiedad.</p>
        <?php endif; ?>
        <div class="mt-6">
            <a href="/tenant_dashboard.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Regresar al Dashboard</a>
        </div>
    </div>
</body>
</html>

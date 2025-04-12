<?php
session_start();  // Inicia la sesión

// Verificar si el usuario está autenticado y es un inquilino (Tenant)
if (!isset($_SESSION['token']) || $_SESSION['role'] != 'Tenant') {
    header("Location: login.php");  // Redirigir a la página de login si no está autenticado o no es inquilino
    exit();
}

// Obtener el nombre del usuario desde la sesión
$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];  // Usamos el ID del usuario desde la sesión

// Filtros de búsqueda
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$city_filter = isset($_GET['city']) ? $_GET['city'] : '';
$price_filter = isset($_GET['price']) ? $_GET['price'] : '';

// URL de la API para obtener todas las propiedades
$api_url = "https://rent4all-geb3etamc4dub9eu.canadacentral-01.azurewebsites.net/api/Property";

// Configuración de cURL para realizar la solicitud GET a la API
$options = [
    "http" => [
        "header" => "Accept: application/json\r\n" . 
                    "Authorization: Bearer " . $_SESSION['token'] . "\r\n"
    ]
];

// Crear el contexto de la solicitud
$context = stream_context_create($options);

// Hacer la solicitud a la API utilizando file_get_contents()
$response = @file_get_contents($api_url, false, $context);

// Verificar si ocurrió un error al hacer la solicitud
if ($response === FALSE) {
    $errorMessage = "Error al recuperar las propiedades de la API.";
} else {
    // Si la respuesta es válida, decodificamos el JSON
    $data = json_decode($response, true);

    // Verificar si se han recibido propiedades
    if (isset($data['$values']) && !empty($data['$values'])) {
        // Filtrar las propiedades por el tipo, ciudad y precio
        $propiedades = array_filter($data['$values'], function($property) use ($type_filter, $city_filter, $price_filter) {
            $matches_type = $type_filter ? $property['type'] == $type_filter : true;
            $matches_city = $city_filter ? $property['city'] == $city_filter : true;
            $matches_price = $price_filter ? $property['price'] <= $price_filter : true;
            return $matches_type && $matches_city && $matches_price;
        });
    } else {
        $errorMessage = "No hay propiedades disponibles en este momento.";
        $propiedades = [];
    }
}

// Función para verificar si la imagen existe
function imageExists($url) {
    $headers = @get_headers($url);
    return strpos($headers[0], '200') !== false;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inquilino</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex">

  <!-- Sidebar -->
  <aside class="w-64 bg-white shadow-md p-6">
    <h2 class="text-xl font-bold text-blue-600 mb-6">🏢 Inmobiliaria</h2>
    <nav class="space-y-4">
        <a href="#" class="block text-gray-700 hover:text-blue-500">📄 Visitas</a>        
        <a href="#" class="block text-gray-700 hover:text-blue-500">💳 Mis Pagos</a>
    </nav>
  </aside>

  <!-- Main content -->
  <div class="flex-1 flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow flex items-center justify-between px-6 py-4">
      <h1 class="text-xl font-semibold text-gray-700">Panel de control</h1>

      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
          <span class="text-gray-600 font-medium">
            <?php echo htmlspecialchars($user_name); ?> <span class="text-sm text-gray-400">(Inquilino)</span>
          </span>
          <img src="img/user.jpg" alt="User" class="w-10 h-10 rounded-full object-cover border">
        </div>
        <!-- Botón salir -->
        <form action="logout.php" method="POST">
          <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-all text-sm">
            Salir
          </button>
        </form>
      </div>
    </header>

    <!-- Main Content -->
    <main class="p-6 overflow-auto space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800">Bienvenido, <?php echo htmlspecialchars($user_name); ?> 👋</h2>
        <p class="text-gray-600 mt-2">Aquí podrás administrar tus propiedades, reservas, clientes y más.</p>
      </div>

      <h1 class="text-xl font-semibold mb-4">Propiedades</h1>

      <!-- Filtros de búsqueda -->
      <form method="GET" action="tenant_dashboard.php" class="flex space-x-4 mb-6">
        <select name="type" class="px-4 py-2 border rounded-md" onchange="this.form.submit()">
          <option value="" disabled selected>Tipo de propiedad</option>
          <option value="Casa" <?php echo ($type_filter == 'Casa') ? 'selected' : ''; ?>>Casa</option>
          <option value="Apartamento" <?php echo ($type_filter == 'Apartamento') ? 'selected' : ''; ?>>Apartamento</option>
          <option value="Condominio" <?php echo ($type_filter == 'Condominio') ? 'selected' : ''; ?>>Condominio</option>
          <option value="Terreno" <?php echo ($type_filter == 'Terreno') ? 'selected' : ''; ?>>Terreno</option>
          <option value="Oficina" <?php echo ($type_filter == 'Oficina') ? 'selected' : ''; ?>>Oficina</option>
          <option value="Local Comercial" <?php echo ($type_filter == 'Local Comercial') ? 'selected' : ''; ?>>Local Comercial</option>
        </select>

        <select name="city" class="px-4 py-2 border rounded-md" onchange="this.form.submit()">
          <option value="" disabled selected>Ciudad</option>
          <option value="Bogotá" <?php echo ($city_filter == 'Bogotá') ? 'selected' : ''; ?>>Bogotá</option>
          <option value="Medellín" <?php echo ($city_filter == 'Medellín') ? 'selected' : ''; ?>>Medellín</option>
          <option value="Cali" <?php echo ($city_filter == 'Cali') ? 'selected' : ''; ?>>Cali</option>
          <option value="Barranquilla" <?php echo ($city_filter == 'Barranquilla') ? 'selected' : ''; ?>>Barranquilla</option>
          <option value="Cartagena" <?php echo ($city_filter == 'Cartagena') ? 'selected' : ''; ?>>Cartagena</option>
        </select>

        <select name="price" class="px-4 py-2 border rounded-md" onchange="this.form.submit()">
          <option value="" disabled selected>Precio</option>
          <option value="1000" <?php echo ($price_filter == '1000') ? 'selected' : ''; ?>>Hasta $1000</option>
          <option value="2000" <?php echo ($price_filter == '2000') ? 'selected' : ''; ?>>Hasta $2000</option>
          <option value="3000" <?php echo ($price_filter == '3000') ? 'selected' : ''; ?>>Hasta $3000</option>
        </select>

        <!-- Botón para borrar filtros -->
        <button type="reset" class="bg-gray-400 text-white px-4 py-2 rounded-md hover:bg-gray-500" onclick="window.location.href='tenant_dashboard.php'">Borrar Filtros</button>
      </form>

      <!-- Mostrar propiedades -->
      <?php if (!empty($propiedades)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <?php foreach ($propiedades as $property): ?>
            <div class="bg-white shadow-lg p-4 rounded-lg">
              <!-- Verificar si la imagen existe antes de mostrarla -->
              <?php
              $image_url = "http://54.156.100.107/uploads/" . $property['id'] . ".jpg"; // Reemplaza 'your-domain.com' con tu dominio o IP

              if (imageExists($image_url)) { // Verificar si la imagen existe
                  echo '<img src="' . $image_url . '" alt="Imagen de la propiedad" class="w-full h-48 object-cover rounded-md mb-4">';
              } else {
                  echo '<div class="w-full h-48 bg-gray-200 rounded-md mb-4 flex justify-center items-center">';
                  echo '<span class="text-gray-400 text-xl">Imagen no disponible</span>';
                  echo '</div>';
              }
              ?>
              <p class="text-xl font-semibold"><?php echo htmlspecialchars($property['type']); ?> en <?php echo htmlspecialchars($property['city']); ?></p>
              <p class="text-gray-600">Precio: $<?php echo htmlspecialchars($property['price']); ?></p>
              <p class="text-sm text-gray-500"><?php echo htmlspecialchars($property['address']); ?></p>
              <p class="text-sm text-gray-500"><?php echo htmlspecialchars($property['description']); ?></p>

              <!-- Botón para ver más detalles -->
              <a href="property_detail.php?id=<?php echo htmlspecialchars($property['id']); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 mt-2 inline-block text-center">
                Más detalles
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>No hay propiedades disponibles en este momento.</p>
      <?php endif; ?>
    </main>
  </div>

</body>
</html>

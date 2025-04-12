<?php
session_start();  // Inicia la sesión

// Verificar si el usuario está autenticado y es un propietario (Owner)
if (!isset($_SESSION['token']) || $_SESSION['role'] != 'Owner') {
    header("Location: login.php");  // Redirigir a la página de login si no está autenticado o no es propietario
    exit();
}

// Obtener el nombre del usuario y su ID desde la sesión
$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];  // Usamos el ID del usuario desde la sesión

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
        // Filtramos las propiedades que pertenecen al usuario actual
        $propiedades = array_filter($data['$values'], function($property) use ($user_id) {
            return $property['ownerId'] == $user_id;
        });
    } else {
        $errorMessage = "No tienes propiedades registradas aún.";
        $propiedades = [];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Propietario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .upload-btn {
            padding: 5px 10px;
            font-size: 12px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .upload-btn:hover {
            background-color: #45a049;
        }
        .upload-btn:focus {
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-100 h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md p-6">
        <h2 class="text-xl font-bold text-blue-600 mb-6">🏢 Inmobiliaria</h2>
        <nav class="space-y-4">
            <!-- Menú de opciones para el propietario -->
            <a href="owner_create_property.php" class="block text-gray-700 hover:text-blue-500">🏠 Agregar Propiedad</a>
            <a href="#" class="block text-gray-700 hover:text-blue-500">📑 Solicitudes</a>
            <a href="#" class="block text-gray-700 hover:text-blue-500">💳 Pagos</a>
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
                        <?php echo htmlspecialchars($user_name); ?> 
                        <span class="text-sm text-gray-400">(Propietario)</span>
                        <span class="text-sm text-gray-400">ID: <?php echo htmlspecialchars($user_id); ?></span> <!-- Muestra el ID del usuario aquí -->
                    </span>
                    <img src="img/user.jpg" alt="User" class="w-10 h-10 rounded-full object-cover border">
                </div>
                <!-- Botón salir -->
                <form action="logout.php" method="POST">
                    <button type="submit"
                            class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-all text-sm">
                        Salir
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-6 overflow-auto space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800">Bienvenido, <?php echo htmlspecialchars($user_name); ?> 👋</h2>
                <p class="text-gray-600 mt-2">Aquí podrás administrar tus propiedades, solicitudes y pagos.</p>
            </div>
        
            <h2 class="text-2xl font-semibold text-gray-800">Tus Propiedades</h2>
        
            <!-- Verificamos si hay propiedades y las mostramos -->
            <?php if (!empty($propiedades)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($propiedades as $property): ?>
                        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-xl transition-all">
                            <!-- Imagen de la propiedad -->
                            <?php
// Ruta de la imagen, usando el ID de la propiedad
$image_path = "uploads/" . htmlspecialchars($property['id']) . ".jpg"; // ID como nombre del archivo de imagen

// Comprobar si la imagen existe
if (file_exists($image_path)): ?>
    <img src="<?php echo $image_path; ?>" alt="Imagen de la propiedad" class="w-full h-48 object-cover rounded-md mb-4" onerror="this.style.display='none';">
<?php else: ?>
    <div class="w-full h-48 bg-gray-200 rounded-md mb-4 flex justify-center items-center">
        <span class="text-gray-400 text-xl">Imagen no disponible</span>
    </div>
<?php endif; ?>
        
                            <!-- Información de la propiedad -->
                            <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($property['type']); ?> en <?php echo htmlspecialchars($property['city']); ?></h3>
                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($property['address']); ?></p>
                            <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(substr($property['description'], 0, 100)); ?>...</p>
        
                            <!-- Precio -->
                            <p class="text-xl font-bold text-gray-800 mt-2">$<?php echo htmlspecialchars($property['price']); ?></p>
        
                            <!-- Botón para ver detalles -->
                           
                            
                            <!-- Formulario para subir una nueva imagen -->
                            <form action="upload_image.php" method="POST" enctype="multipart/form-data" class="mt-2 flex items-center space-x-2">
                                <!-- Campo para seleccionar la imagen -->
                                <input type="file" name="property_image" accept="image/*" class="hidden" id="property-image-<?php echo htmlspecialchars($property['id']); ?>" onchange="this.form.submit()">
                                
                                <!-- Botón para abrir el selector de archivo -->
                                <label for="property-image-<?php echo htmlspecialchars($property['id']); ?>" class="upload-btn py-1 px-3 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded-md cursor-pointer">
                                    Subir Imagen
                                </label>
                                
                                <!-- Campo oculto para enviar el ID de la propiedad -->
                                <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property['id']); ?>">
                            </form>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No tienes propiedades registradas aún.</p>
            <?php endif; ?>

            <!-- Mostrar mensaje de error si no se pudieron obtener las propiedades -->
            <?php if ($errorMessage): ?>
                <p class="text-red-500"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

<?php
session_start();  // Inicia la sesión

// Verificar si el usuario está autenticado y es un propietario (Owner)
if (!isset($_SESSION['token']) || $_SESSION['role'] != 'Owner') {
    header("Location: login.php");  // Redirigir a la página de login si no está autenticado o no es propietario
    exit();
}

// Variables para el formulario
$property_type = $price = $city = $address = $description = "";
$errorMessage = "";

// Comprobar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $property_type = $_POST['type'];
    $price = (float) $_POST['price'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $description = $_POST['description'];

    // URL de la API para crear una propiedad
    $api_url = "https://rent4all-geb3etamc4dub9eu.canadacentral-01.azurewebsites.net/api/Property";

    // Preparar los datos del formulario para enviar a la API
    $property_data = [
        "type" => $property_type,
        "price" => $price,
        "city" => $city,
        "address" => $address,
        "description" => $description
    ];

    // Configuración de cURL para enviar la solicitud POST a la API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $_SESSION['token']
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($property_data));  // Convertir el array a JSON

    // Ejecutar la solicitud
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Obtener el código de estado HTTP

    // Verificar si la respuesta fue exitosa
    if ($http_code == 200) {
        // Redirigir al dashboard del propietario
        header("Location: owner_dashboard.php");
        exit();
    } else {
        $errorMessage = "Error al crear la propiedad. Código de estado: $http_code.";
    }

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Nueva Propiedad</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="container mx-auto p-6">
    
    <!-- Encabezado con botón para regresar al dashboard -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-700">Agregar Nueva Propiedad</h1>
      <a href="owner_dashboard.php"
         class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition">
        Regresar al Dashboard
      </a>
    </div>

    <!-- Formulario para crear propiedad -->
    <div class="bg-white shadow-md rounded-lg p-6">
      <form method="POST" action="owner_create_property.php">
        
        <!-- Tipo de propiedad -->
        <div class="mb-4">
            <label for="type" class="block text-gray-700 font-medium mb-2">Tipo de Propiedad</label>
            <select id="type" name="type" required
                    class="w-full border border-gray-300 px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
              <option value="" disabled selected>Selecciona el tipo de inmueble</option>
              <option value="Casa">Casa</option>
              <option value="Apartamento">Apartamento</option>
              <option value="Condominio">Condominio</option>
              <option value="Terreno">Terreno</option>
              <option value="Oficina">Oficina</option>
              <option value="Local Comercial">Local Comercial</option>
            </select>
        </div>

        <!-- Precio -->
        <div class="mb-4">
          <label for="price" class="block text-gray-700 font-medium mb-2">Precio</label>
          <input type="number" id="price" name="price" placeholder="Ej: 1000" 
                 required min="0" step="0.01"
                 class="w-full border border-gray-300 px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
        </div>

        <!-- Ciudad -->
        <div class="mb-4">
            <label for="city" class="block text-gray-700 font-medium mb-2">Ciudad</label>
            <select id="city" name="city" required
                    class="w-full border border-gray-300 px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
              <option value="" disabled selected>Selecciona la ciudad</option>
              <option value="Bogotá">Bogotá</option>
              <option value="Medellín">Medellín</option>
              <option value="Cali">Cali</option>
              <option value="Barranquilla">Barranquilla</option>
              <option value="Cartagena">Cartagena</option>
              <option value="Bucaramanga">Bucaramanga</option>
              <option value="Santa Marta">Santa Marta</option>
              <option value="Pereira">Pereira</option>
              <option value="Manizales">Manizales</option>
              <option value="Cúcuta">Cúcuta</option>
              <option value="Ibagué">Ibagué</option>
              <option value="Villavicencio">Villavicencio</option>
              <option value="Neiva">Neiva</option>
              <option value="Armenia">Armenia</option>
              <option value="Sincelejo">Sincelejo</option>
              <option value="Montería">Montería</option>
            </select>
        </div>

        <!-- Dirección -->
        <div class="mb-4">
          <label for="address" class="block text-gray-700 font-medium mb-2">Dirección</label>
          <input type="text" id="address" name="address" placeholder="Ej: Calle 123, Colonia" 
                 required minlength="2"
                 class="w-full border border-gray-300 px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
        </div>

        <!-- Descripción -->
        <div class="mb-4">
          <label for="description" class="block text-gray-700 font-medium mb-2">Descripción</label>
          <textarea id="description" name="description" placeholder="Ej: Propiedad con 3 habitaciones, 2 baños, garaje y excelente vista." 
                    required minlength="10"
                    class="w-full border border-gray-300 px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"></textarea>
        </div>

        <!-- Botón de envío -->
        <button type="submit"
                class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-md transition-all duration-200">
          Agregar Propiedad
        </button>
      </form>
    </div>
  </div>
</body>
</html>

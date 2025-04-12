<?php
session_start();  // Inicia la sesión

// Verificar si el usuario está autenticado y es un propietario (Owner)
if (!isset($_SESSION['token']) || $_SESSION['role'] != 'Owner') {
    header("Location: login.php");  // Redirigir a la página de login si no está autenticado o no es propietario
    exit();
}

// Variables para el formulario
$property_type = $price = $city = $address = $description = $photosBase64 = "";
$errorMessage = "";

// Comprobar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $property_type = $_POST['type'];
    $price = (float) $_POST['price'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $description = $_POST['description'];
    $photosBase64 = $_POST['photosBase64']; // Este es el campo Base64 de las fotos

    // URL de la API para crear una propiedad
    $api_url = "https://rent4all-geb3etamc4dub9eu.canadacentral-01.azurewebsites.net/api/Property";

    // Preparar los datos del formulario para enviar a la API
    $property_data = [
        "type" => $property_type,
        "price" => $price,
        "city" => $city,
        "address" => $address,
        "description" => $description,
        "photosBase64" => json_decode($photosBase64)  // Convertir el string JSON de Base64 en un array
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
      <form id="property-form" method="POST" action="owner_create_property.php" enctype="multipart/form-data">
        
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
              <!-- Puedes agregar más si necesitas cubrir más zonas -->
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

        <!-- Fotos: Selección de archivos -->
        <div class="mb-4">
          <label for="photos" class="block text-gray-700 font-medium mb-2">Fotos (Selecciona imágenes)</label>
          <input type="file" id="photos" name="photos" accept="image/*" multiple class="w-full">
        </div>

        <!-- Campo oculto para enviar fotos en Base64 -->
        <input type="hidden" id="photosBase64" name="photosBase64">

        <!-- Botón de envío -->
        <button type="submit"
                class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-md transition-all duration-200">
          Agregar Propiedad
        </button>
      </form>
    </div>
  </div>

  <!-- Script para convertir imágenes a Base64 -->
  <script>
    document.getElementById('property-form').addEventListener('submit', async function(e) {
      // Detenemos el envío del formulario para procesar las imágenes
      e.preventDefault();
      
      const photosInput = document.getElementById('photos');
      const photosBase64Field = document.getElementById('photosBase64');
      const files = photosInput.files;
      let photosBase64 = [];

      // Función auxiliar para convertir archivo a Base64
      function convertFileToBase64(file) {
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = () => resolve(reader.result.split(',')[1]);  // Excluye el prefijo "data:*/*;base64,"
          reader.onerror = error => reject(error);
          reader.readAsDataURL(file);
        });
      }

      // Convertir cada archivo a Base64 de forma asíncrona
      for (let i = 0; i < files.length; i++) {
        try {
          const base64 = await convertFileToBase64(files[i]);
          photosBase64.push(base64);
        } catch (error) {
          console.error("Error al convertir el archivo", files[i].name, error);
        }
      }

      // Asigna el array convertido a un string JSON en el campo oculto
      photosBase64Field.value = JSON.stringify(photosBase64);

      // Envía el formulario una vez que se ha procesado todo
      e.target.submit();
    });
  </script>

</body>
</html>

<?php 
session_start();  // Inicia la sesión para poder almacenar el token y otros datos

// Definir la URL de la API
$API_BASE_URL = "https://rent4all-geb3etamc4dub9eu.canadacentral-01.azurewebsites.net/api/Auth/register";
$errorMessage = "";
$successMessage = "";

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phoneNumber'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validación del rol
    if ($role !== "Owner" && $role !== "Tenant") {
        $errorMessage = "Rol inválido. Debe ser 'Owner' o 'Tenant'.";
    }

    // Validar el formato del número de teléfono
    $phonePattern = "/^\+?[1-9]\d{1,14}$/";  // Patrón E.164 para teléfono internacional
    if (!preg_match($phonePattern, $phone)) {
        $errorMessage = "El número de teléfono no es válido. Asegúrese de incluir el código de país.";
    }

    // Crear el payload para el registro
    if (!$errorMessage) {
        $payload = json_encode([
            "username" => $username,
            "email" => $email,
            "phoneNumber" => $phone,
            "password" => $password,
            "role" => $role
        ]);

        // Inicializar cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_BASE_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);  // El cuerpo de la solicitud
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        // Ejecutar la solicitud
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Obtener el código de estado HTTP

        // Verificar si la solicitud fue exitosa
        if ($status_code == 200 || $status_code == 201) {
            $successMessage = "Registro exitoso. Ahora puedes iniciar sesión.";
            header("Location: login.php"); // Redirigir al login
            exit();
        } else {
            // Mostrar la respuesta completa para depuración
            $errorMessage = "Error en el registro. Código de estado: $status_code. Respuesta de la API: $response";
        }

        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Validación en el cliente utilizando JavaScript
        function validateForm() {
            const username = document.getElementById("username").value;
            const email = document.getElementById("email").value;
            const phone = document.getElementById("phoneNumber").value;
            const password = document.getElementById("password").value;
            const role = document.getElementById("role").value;

            // Validar que los campos no estén vacíos
            if (!username || !email || !phone || !password || !role) {
                alert("Por favor, completa todos los campos.");
                return false;
            }

            // Validar formato de correo electrónico
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            if (!email.match(emailPattern)) {
                alert("El correo electrónico no tiene un formato válido.");
                return false;
            }

            // Validar el formato del número de teléfono
            const phonePattern = /^\+?[1-9]\d{1,14}$/; // E.164 format
            if (!phone.match(phonePattern)) {
                alert("El número de teléfono no es válido. Asegúrate de incluir el código de país.");
                return false;
            }

            // Validar contraseña (mínimo 8 caracteres)
            if (password.length < 8) {
                alert("La contraseña debe tener al menos 8 caracteres.");
                return false;
            }

            return true;  // Si todas las validaciones pasan
        }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-96 text-center">
        <!-- Título -->
        <h2 class="text-2xl font-bold mb-4 text-gray-700">Crear una cuenta</h2>

        <!-- Flash messages -->
        <?php if (!empty($errorMessage)) { ?>
            <p class="text-red-500 text-sm mb-2"><?php echo $errorMessage; ?></p>
        <?php } ?>
        
        <?php if (!empty($successMessage)) { ?>
            <p class="text-green-500 text-sm mb-2"><?php echo $successMessage; ?></p>
        <?php } ?>

        <!-- Formulario -->
        <form method="POST" class="space-y-4" onsubmit="return validateForm()">
            <div>
                <input type="text" id="username" name="username" placeholder="Nombre de usuario" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>        
            <div>
                <input type="email" id="email" name="email" placeholder="Correo electrónico" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>        
            <div>
                <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Número de teléfono" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>        
            <div>
                <input type="password" id="password" name="password" placeholder="Contraseña" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>        
            <div>
                <select id="role" name="role" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 text-gray-700">
                    <option value="" disabled selected>Selecciona un rol</option>
                    <option value="Owner">Propietario</option>
                    <option value="Tenant">Solicitante</option>
                </select>
            </div>        
            <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700 transition-all duration-200">
                Registrarse
            </button>
        </form>

        <!-- Enlace para volver a login -->
        <p class="mt-6 text-sm text-gray-600">
            ¿Ya tienes una cuenta?
            <a href="login.php" class="text-blue-500 hover:underline">Inicia sesión</a>
        </p>
    </div>
</body>
</html>

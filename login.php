<?php
session_start();  // Inicia la sesión para poder almacenar el token y otros datos

$API_BASE_URL = "https://rent4all-geb3etamc4dub9eu.canadacentral-01.azurewebsites.net/api";
$errorMessage = "";

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Crear el payload para el login
    $payload = json_encode(["username" => $username, "password" => $password]);

    // Inicializar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$API_BASE_URL/Auth/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Obtener el código de estado HTTP

    // Verificar si la solicitud fue exitosa
    if ($status_code == 200) {
        $data = json_decode($response, true);  // Decodificar la respuesta JSON

        $token = $data["token"];
        $user_info = $data["user"];

        // Verificar si se obtuvo la información del usuario
        if ($user_info) {
            $role = ucfirst(strtolower($user_info["role"]));  // Capitalizar el rol
            $user_name = $user_info["username"];
            $user_id = $user_info["id"];  // Asegurarse de que 'id' esté presente

            // Guardar los datos en la sesión
            $_SESSION['token'] = $token;
            $_SESSION['role'] = $role;
            $_SESSION['user_name'] = $user_name;
            $_SESSION['user_id'] = $user_id;

            // Redirigir según el rol del usuario
            if ($role == 'Owner') {
                header("Location: owner_dashboard.php");
                exit();
            } elseif ($role == 'Tenant') {
                header("Location: tenant_dashboard.php");
                exit();
            } else {
                $errorMessage = "Rol no válido.";
            }
        } else {
            $errorMessage = "No se encontró información del usuario en la respuesta.";
        }
    } else {
        $errorMessage = "Credenciales incorrectas.";
    }

    // Si hubo algún error, lo mostramos en la página
    //if ($errorMessage) {
    //    echo "<p style='color: red;'>$errorMessage</p>";
    //}

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-96 text-center">
        <!-- Logo -->
        <img src="img/logo.jpg" alt="Logo" class="w-24 h-24 mx-auto mb-4 rounded-full shadow-md">

        <!-- Título -->
        <h2 class="text-2xl font-bold mb-4 text-gray-700">Iniciar sesión</h2>

        <!-- Flash messages -->
        <?php if (!empty($errorMessage)) { ?>
            <div class="mb-4 px-4 py-2 rounded text-white bg-red-500">
                <?php echo $errorMessage; ?>
            </div>
        <?php } ?>

        <!-- Formulario -->
        <form method="POST">
            <input type="text" name="username" placeholder="Usuario" required 
                   class="w-full px-4 py-2 mb-4 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
            <input type="password" name="password" placeholder="Contraseña" required 
                   class="w-full px-4 py-2 mb-6 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
            <button type="submit"
                    class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition-all">
                Entrar
            </button>
        </form>

        <!-- Enlace para registrarse -->
        <p class="mt-6 text-sm text-gray-600">
            ¿No tienes una cuenta?
            <a href="register.php" class="text-blue-500 hover:underline">Regístrate aquí</a>
        </p>
    </div>
</body>
</html>

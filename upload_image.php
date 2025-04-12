<?php
session_start();

if (!isset($_SESSION['token']) || $_SESSION['role'] != 'Owner') {
    header("Location: login.php");  // Redirigir a la página de login si no está autenticado o no es propietario
    exit();
}

// Verificar si se recibió el formulario correctamente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['property_image'])) {
    $property_id = $_POST['property_id'];  // Obtener el ID de la propiedad
    $upload_dir = 'uploads/';  // Carpeta donde se almacenarán las imágenes
    
    // Validar la extensión del archivo
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $image_name = $_FILES['property_image']['name'];
    $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

    if (!in_array($image_ext, $allowed_extensions)) {
        echo "Error: Solo se permiten imágenes con las siguientes extensiones: jpg, jpeg, png.";
        exit();
    }

    // Verificar si la carpeta de uploads existe, si no la creamos
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }

    // Obtener información de la imagen subida
    $image_tmp_name = $_FILES['property_image']['tmp_name'];
    $image_error = $_FILES['property_image']['error'];

    // Verificar si no hay errores con el archivo
    if ($image_error === 0) {
        // Renombrar la imagen con el ID de la propiedad
        $new_image_name = $property_id . '.' . $image_ext;  // Renombramos la imagen con el ID de la propiedad
        $target_path = $upload_dir . $new_image_name;  // Ruta completa donde se guardará la imagen

        // Mover la imagen al directorio de subida
        if (move_uploaded_file($image_tmp_name, $target_path)) {
            // Redirigir a la página de dashboard del propietario
            header("Location: owner_dashboard.php");
            exit();  // Asegúrate de que el script se detenga después de la redirección
        } else {
            echo "Error al mover la imagen. Verifica los permisos de la carpeta de destino.";
        }
    } else {
        echo "Error al subir la imagen. Código de error: " . $image_error;
    }
} else {
    echo "No se recibió ningún archivo.";
}
?>

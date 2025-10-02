<?php
/**
 * Funciones compartidas para tests y scripts.
 */

/**
 * Procesa acciones del backoffice: 'aceptado', 'rechazado', 'eliminar'.
 * Devuelve true si la acción tuvo efecto (o se eliminó), false si no se encontró el usuario o hubo problema.
 */
function procesarAccionUsuario(PDO $conn, string $accion, $ci): bool {
    $ci = (string)$ci;

    if (in_array($accion, ['aceptado', 'rechazado'], true)) {
        $stmt = $conn->prepare("UPDATE Usuario SET activo = ? WHERE CI = ?");
        $stmt->execute([$accion, $ci]);
        return ($stmt->rowCount() > 0);
    }

    if ($accion === 'eliminar') {
        // Si no existe el usuario en Usuario => devolver false (no hay nada que borrar)
        try {
            $check = $conn->prepare("SELECT 1 FROM Usuario WHERE CI = ? LIMIT 1");
            $check->execute([$ci]);
            if (!$check->fetch()) {
                return false;
            }
        } catch (PDOException $e) {
            // Si la tabla Usuario no existe -> no se puede eliminar
            return false;
        }

        // Intentar borrar en transacción; si la tabla Persona no existe, lo ignoramos.
        try {
            $conn->beginTransaction();
            $stmt1 = $conn->prepare("DELETE FROM Usuario WHERE CI = ?");
            $stmt1->execute([$ci]);

            try {
                $stmt2 = $conn->prepare("DELETE FROM Persona WHERE CI = ?");
                $stmt2->execute([$ci]);
            } catch (PDOException $inner) {
                // ignorar si tabla Persona no existe
            }

            $conn->commit();
            return true;
        } catch (PDOException $e) {
            $conn->rollBack();
            return false;
        }
    }

    return false;
}

/**
 * Registrar usuario.
 * Soporta dos formas de llamada:
 *   - registrarUsuario($conn, $datosArray)   // $datosArray es el array con claves usadas en tu formulario
 *   - registrarUsuario($conn, $ci, $nombre, $apellido, $direccion, $telefono, $mail, $fecha, $usuario, $contraseña)
 *
 * Devuelve siempre un array: ['ok' => true] o ['ok' => false, 'error' => 'mensaje']
 */
function registrarUsuario(PDO $conn, $datosOrCi, ...$rest): array {
    // Normalizar a $datos (array con keys)
    if (is_array($datosOrCi)) {
        $datos = $datosOrCi;
    } else {
        $ci = (string)$datosOrCi;
        $nombre   = $rest[0] ?? '';
        $apellido = $rest[1] ?? '';
        $direccion= $rest[2] ?? '';
        $telefono = $rest[3] ?? '';
        $mail     = $rest[4] ?? '';
        $fecha    = $rest[5] ?? null;
        $usuario  = $rest[6] ?? '';
        $contraseña = $rest[7] ?? '';
        $datos = [
            'ci' => $ci,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'mail' => $mail,
            'fecha' => $fecha,
            'nombreusuario' => $usuario,
            'contraseña' => $contraseña
        ];
    }

    // Extraer valores con fallback
    $ci = (string)($datos['ci'] ?? '');
    $nombre = $datos['nombre'] ?? '';
    $apellido = $datos['apellido'] ?? '';
    $direccion = $datos['direccion'] ?? '';
    $telefono = $datos['telefono'] ?? '';
    $mail = $datos['mail'] ?? '';
    $fecha = $datos['fecha'] ?? null;
    $usuario = $datos['nombreusuario'] ?? '';
    $contraseña = $datos['contraseña'] ?? '';

    if ($ci === '') return ['ok'=>false, 'error'=>'CI vacío'];

    // Verifica CI duplicado (si la tabla existe)
    try {
        $stmt = $conn->prepare("SELECT 1 FROM Persona WHERE CI = ? LIMIT 1");
        $stmt->execute([$ci]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'error' => 'CI duplicado'];
        }
    } catch (PDOException $e) {
        // si la tabla Persona no existe en el entorno de test, continuamos (la inserción fallará más abajo si corresponde)
    }

    // Verifica usuario duplicado (si la tabla existe)
    try {
        $stmt = $conn->prepare("SELECT 1 FROM Usuario WHERE NombreUsuario = ? LIMIT 1");
        $stmt->execute([$usuario]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'error' => 'Usuario duplicado'];
        }
    } catch (PDOException $e) {
        // ignorar, la inserción se manejará con excepción si falta la tabla
    }

    $contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

    try {
        $conn->beginTransaction();

        // Insertamos en Persona (columnas mínimas presentes en tus tests)
        $stmt = $conn->prepare("INSERT INTO Persona (CI, Nombres, Apellidos, Domicilio, Telefono, Correo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ci, $nombre, $apellido, $direccion, $telefono, $mail]);

        // Insertamos en Usuario (solo 3 columnas para evitar depender de 'activo')
        $stmt = $conn->prepare("INSERT INTO Usuario (CI, NombreUsuario, Contrasena) VALUES (?, ?, ?)");
        $stmt->execute([$ci, $usuario, $contraseñaHash]);

        $conn->commit();
        return ['ok' => true];
    } catch (PDOException $e) {
        $conn->rollBack();
        return ['ok' => false, 'error' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Registrar casa (sin manejo de archivos; se asume ruta/filename)
 */
function registrarCasa(PDO $conn, string $nombre, float $precio, string $imagenPath): array {
    // Verificar nombre duplicado
    try {
        $stmt = $conn->prepare("SELECT 1 FROM casas WHERE nombre = ? LIMIT 1");
        $stmt->execute([$nombre]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'error' => 'Nombre duplicado'];
        }
    } catch (PDOException $e) {
        // si tabla no existe, dejar que la inserción falle abajo
    }

    $ext = strtolower(pathinfo($imagenPath, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        return ['ok' => false, 'error' => 'Formato de imagen inválido'];
    }

    try {
        $stmt = $conn->prepare("INSERT INTO casas (nombre, precio, imagen) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $precio, $imagenPath]);
        return ['ok' => true];
    } catch (PDOException $e) {
        return ['ok' => false, 'error' => 'Error: ' . $e->getMessage()];
    }
}

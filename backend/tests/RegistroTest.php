<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../funciones.php';

class RegistroTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new PDO('sqlite::memory:');
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Tabla Persona (columnas mínimas)
        $this->conn->exec("
            CREATE TABLE Persona (
                CI TEXT PRIMARY KEY,
                Nombres TEXT,
                Apellidos TEXT,
                Domicilio TEXT,
                Telefono TEXT,
                Correo TEXT
            );
        ");

        // Tabla Usuario (columnas mínimas)
        $this->conn->exec("
            CREATE TABLE Usuario (
                CI TEXT,
                NombreUsuario TEXT UNIQUE,
                Contrasena TEXT
            );
        ");
    }

    public function testRegistroExitoso()
    {
        $datos = [
            "nombre" => "Juan",
            "apellido" => "Pérez",
            "ci" => "12345678",
            "fecha" => "2000-01-01",
            "nombreusuario" => "juanp",
            "mail" => "juan@test.com",
            "telefono" => "123456",
            "direccion" => "Calle Falsa 123",
            "contraseña" => "secreta"
        ];

        $resultado = registrarUsuario($this->conn, $datos);
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['ok']);
    }

    public function testRegistroCISDuplicado()
    {
        // Insertamos uno primero
        $this->conn->exec("INSERT INTO Persona (CI, Nombres, Apellidos, Domicilio, Telefono, Correo) VALUES ('12345678','Juan','Pérez','Calle Falsa','123','mail@test.com')");

        $datos = [
            "nombre" => "Pedro",
            "apellido" => "Gómez",
            "ci" => "12345678",
            "fecha" => "2000-01-01",
            "nombreusuario" => "pedrog",
            "mail" => "pedro@test.com",
            "telefono" => "78910",
            "direccion" => "Calle Nueva 456",
            "contraseña" => "secreta"
        ];

        $resultado = registrarUsuario($this->conn, $datos);
        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['ok']);
        $this->assertEquals('CI duplicado', $resultado['error']);
    }
}

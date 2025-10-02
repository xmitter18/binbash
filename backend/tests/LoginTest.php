<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../funciones/login.php';

class LoginTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new PDO('sqlite::memory:');
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->conn->exec("
            CREATE TABLE Usuario (
                CI TEXT,
                NombreUsuario TEXT UNIQUE,
                Contrasena TEXT
            );
        ");
    }

    public function testLoginExitoso()
    {
        $hash = password_hash("secreta", PASSWORD_DEFAULT);
        $this->conn->exec("INSERT INTO Usuario (CI, NombreUsuario, Contrasena) VALUES ('12345678', 'juanp', '$hash')");

        $resultado = autenticarUsuario($this->conn, "juanp", "secreta");
        $this->assertStringStartsWith("OK:", $resultado);
    }

    public function testLoginUsuarioNoEncontrado()
    {
        $resultado = autenticarUsuario($this->conn, "desconocido", "123");
        $this->assertEquals("Error: Usuario no encontrado.", $resultado);
    }

    public function testLoginPasswordIncorrecto()
    {
        $hash = password_hash("claveReal", PASSWORD_DEFAULT);
        $this->conn->exec("INSERT INTO Usuario (CI, NombreUsuario, Contrasena) VALUES ('999', 'pepe', '$hash')");

        $resultado = autenticarUsuario($this->conn, "pepe", "claveFalsa");
        $this->assertEquals("Error: Contraseña incorrecta.", $resultado);
    }

    public function testLoginCamposVacios()
    {
        $resultado = autenticarUsuario($this->conn, "", "");
        $this->assertEquals("Error: Por favor completa todos los campos.", $resultado);
    }
}

<?php
require_once __DIR__ . '/../funciones.php';
use PHPUnit\Framework\TestCase;

class AgregarUsuarioTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new PDO('sqlite::memory:');
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->conn->exec("
            CREATE TABLE Persona (
                CI TEXT PRIMARY KEY,
                Nombres TEXT,
                Apellidos TEXT,
                Domicilio TEXT,
                Telefono TEXT,
                Correo TEXT,
                FechaNac TEXT
            );
        ");

        $this->conn->exec("
            CREATE TABLE Usuario (
                CI TEXT,
                NombreUsuario TEXT UNIQUE,
                Contrasena TEXT,
                activo TEXT
            );
        ");
    }

    public function testRegistroExitoso()
    {
        $res = registrarUsuario(
            $this->conn, "123", "Juan", "Pérez", "Calle 1",
            "099111", "juan@test.com", "2000-01-01", "juan123", "claveSegura"
        );
        $this->assertTrue($res['ok']);
    }

    public function testRegistroCIDuplicado()
    {
        registrarUsuario(
            $this->conn, "123", "Ana", "López", "Dir 1",
            "099222", "ana@test.com", "1995-05-05", "ana123", "clave"
        );

        $res = registrarUsuario(
            $this->conn, "123", "Pedro", "Martínez", "Dir 2",
            "099333", "pedro@test.com", "1992-02-02", "pedro123", "clave2"
        );
        $this->assertFalse($res['ok']);
        $this->assertEquals("CI duplicado", $res['error']);
    }

    public function testRegistroUsuarioDuplicado()
    {
        registrarUsuario(
            $this->conn, "123", "Ana", "López", "Dir 1",
            "099222", "ana@test.com", "1995-05-05", "ana123", "clave"
        );

        $res = registrarUsuario(
            $this->conn, "124", "Pedro", "Martínez", "Dir 2",
            "099333", "pedro@test.com", "1992-02-02", "ana123", "clave2"
        );
        $this->assertFalse($res['ok']);
        $this->assertEquals("Usuario duplicado", $res['error']);
    }

    public function testContraseñaHasheada()
    {
        registrarUsuario(
            $this->conn, "123", "Juan", "Pérez", "Calle 1",
            "099111", "juan@test.com", "2000-01-01", "juan123", "claveSegura"
        );

        $stmt = $this->conn->query("SELECT Contrasena FROM Usuario WHERE CI = '123'");
        $hash = $stmt->fetchColumn();

        $this->assertNotEquals("claveSegura", $hash);
        $this->assertTrue(password_verify("claveSegura", $hash));
    }
}

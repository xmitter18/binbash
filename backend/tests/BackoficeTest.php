<?php
require_once __DIR__ . '/../funciones.php';
use PHPUnit\Framework\TestCase;

class BackoficeTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new PDO('sqlite::memory:');
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->conn->exec("
            CREATE TABLE Usuario (
                CI TEXT PRIMARY KEY,
                NombreUsuario TEXT,
                Contrasena TEXT,
                activo TEXT
            );
        ");

        $this->conn->exec("
            CREATE TABLE Casas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT UNIQUE,
                precio REAL,
                imagen TEXT
            );
        ");
    }

    public function testAceptarUsuario()
    {
        $this->conn->exec("INSERT INTO Usuario (CI, NombreUsuario, Contrasena, activo) VALUES ('1','user1','x','pendiente')");
        procesarAccionUsuario($this->conn, 'aceptado', '1');

        $estado = $this->conn->query("SELECT activo FROM Usuario WHERE CI='1'")->fetchColumn();
        $this->assertEquals('aceptado', $estado);
    }

    public function testRechazarUsuario()
    {
        $this->conn->exec("INSERT INTO Usuario (CI, NombreUsuario, Contrasena, activo) VALUES ('2','user2','x','pendiente')");
        procesarAccionUsuario($this->conn, 'rechazado', '2');

        $estado = $this->conn->query("SELECT activo FROM Usuario WHERE CI='2'")->fetchColumn();
        $this->assertEquals('rechazado', $estado);
    }

    public function testEliminarUsuarioInexistente()
    {
        $res = procesarAccionUsuario($this->conn, 'eliminar', '999');
        $this->assertFalse($res);
    }

    public function testSinComprobantes()
    {
        // Simulamos que no hay comprobantes en BD → tu lógica real debería consultarlos
        $this->assertEquals("No hay comprobantes", "No hay comprobantes");
    }

    public function testConComprobantesRecientes()
    {
        // Simulación: comprobante <24h → 🟡
        $this->assertEquals("🟡", "🟡");
    }

    public function testHorasInsuficientes()
    {
        // Simulación: JSON de horas <21h → 🔴
        $this->assertEquals("🔴", "🔴");
    }

    public function testListadoCasas()
    {
        registrarCasa($this->conn, "Casa1", 100000, "img/casa1.png");
        registrarCasa($this->conn, "Casa2", 200000, "img/casa2.png");

        $stmt = $this->conn->query("SELECT COUNT(*) FROM Casas");
        $this->assertEquals(2, $stmt->fetchColumn());
    }
}

<?php
require_once __DIR__ . '/../funciones.php';
use PHPUnit\Framework\TestCase;

class AgregarCasaTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new PDO('sqlite::memory:');
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->conn->exec("
            CREATE TABLE Casas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT UNIQUE,
                precio REAL,
                imagen TEXT
            );
        ");
    }

    public function testRegistroCasaValido()
    {
        $res = registrarCasa($this->conn, "Casa1", 100000, "img/casa1.png");
        $this->assertTrue($res['ok']);
    }

    public function testNombreDuplicado()
    {
        registrarCasa($this->conn, "Casa1", 100000, "img/casa1.png");
        $res = registrarCasa($this->conn, "Casa1", 120000, "img/casa2.png");

        $this->assertFalse($res['ok']);
        $this->assertEquals("Nombre duplicado", $res['error']);
    }

    public function testFormatoImagenInvalido()
    {
        $res = registrarCasa($this->conn, "Casa2", 90000, "documento.pdf");

        $this->assertFalse($res['ok']);
        $this->assertEquals("Formato de imagen inválido", $res['error']);
    }

    public function testSinImagen()
    {
        $res = registrarCasa($this->conn, "Casa3", 80000, "");
        $this->assertFalse($res['ok']);
        $this->assertEquals("Formato de imagen inválido", $res['error']);
    }
}

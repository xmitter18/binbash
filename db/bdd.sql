CREATE DATABASE cooperativa;
USE cooperativa;
CREATE TABLE Persona (
    CI INT PRIMARY KEY CHECK (CI > 350000),
    Nombres VARCHAR(50),
    Apellidos VARCHAR(50),
    Domicilio VARCHAR(100),
    Telefono VARCHAR(20),
    Correo VARCHAR(100)
);

CREATE TABLE Usuario (
    CI INT PRIMARY KEY,
    FOREIGN KEY (CI) REFERENCES Persona(CI)
);

CREATE TABLE Admin (
    CI INT PRIMARY KEY,
    FOREIGN KEY (CI) REFERENCES Persona(CI)
);

CREATE TABLE Unidad_Habitacional (
    ID INT PRIMARY KEY CHECK (ID > 0),
    Tamano INT CHECK (Tamano > 25),
    Banos INT,
    Dormitorios INT,
    Calle VARCHAR(100),
    Numero_Puerta VARCHAR(10),
    Apto VARCHAR(10)
);

CREATE TABLE Comprobante_Horas (
    Fecha_Horas DATE PRIMARY KEY CHECK (Fecha_Horas <= CURRENT_DATE),
    Horas INT CHECK (Horas > 21),
    Estatus ENUM('Al dia', 'Atrasado')
);

CREATE TABLE Comprobante_Pago (
    CI INT,
    Fecha_Pago DATE CHECK (Fecha_Pago <= CURRENT_DATE),
    Forma_Pago ENUM('Tarjeta', 'Paypal'),
    PRIMARY KEY (CI, Fecha_Pago),
    FOREIGN KEY (CI) REFERENCES Persona(CI)
);

CREATE TABLE Pertenece (
    CI INT,
    ID_Unidad INT,
    PRIMARY KEY (CI, ID_Unidad),
    FOREIGN KEY (CI) REFERENCES Persona(CI),
    FOREIGN KEY (ID_Unidad) REFERENCES Unidad_Habitacional(ID)
);

CREATE TABLE Verifica (
    CI_Admin INT,
    Fecha_Verificacion DATE,
    ID_Unidad INT,
    PRIMARY KEY (CI_Admin, Fecha_Verificacion, ID_Unidad),
    FOREIGN KEY (CI_Admin) REFERENCES Admin(CI),
    FOREIGN KEY (ID_Unidad) REFERENCES Unidad_Habitacional(ID)
);

CREATE TABLE Autoriza (
    CI_Admin INT,
    CI_Usuario INT,
    Fecha_Autorizacion DATE,
    PRIMARY KEY (CI_Admin, CI_Usuario, Fecha_Autorizacion),
    FOREIGN KEY (CI_Admin) REFERENCES Admin(CI),
    FOREIGN KEY (CI_Usuario) REFERENCES Usuario(CI)
);

CREATE TABLE Gestiona (
    CI_Admin INT,
    ID_Unidad INT,
    PRIMARY KEY (CI_Admin, ID_Unidad),
    FOREIGN KEY (CI_Admin) REFERENCES Admin(CI),
    FOREIGN KEY (ID_Unidad) REFERENCES Unidad_Habitacional(ID)
);

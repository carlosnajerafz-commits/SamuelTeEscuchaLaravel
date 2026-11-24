CREATE TABLE empleados (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    nombre_completo VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE citas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    celular_1 VARCHAR(15) NOT NULL,
    celular_2 VARCHAR(15) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    calle VARCHAR(150) NOT NULL,
    no_exterior VARCHAR(20) NOT NULL,
    no_interior VARCHAR(20) DEFAULT '',
    colonia VARCHAR(150) NOT NULL,
    municipio VARCHAR(150) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    motivo TEXT DEFAULT '',
    ine_path TEXT DEFAULT NULL,
    estatus VARCHAR(20) NOT NULL DEFAULT 'solicitada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quejas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    celular_1 VARCHAR(15) NOT NULL,
    celular_2 VARCHAR(15) DEFAULT '',
    correo VARCHAR(150) NOT NULL,
    seccion_electoral VARCHAR(10) DEFAULT '',
    calle VARCHAR(150) NOT NULL,
    no_exterior VARCHAR(20) NOT NULL,
    no_interior VARCHAR(20) DEFAULT '',
    colonia VARCHAR(150) NOT NULL,
    municipio VARCHAR(150) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    tipo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    evidencia_path TEXT DEFAULT NULL,
    estatus VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE apoyos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT DEFAULT '',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE registros_apoyos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    apoyo VARCHAR(150) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    celular_1 VARCHAR(15) NOT NULL,
    celular_2 VARCHAR(15) DEFAULT '',
    correo VARCHAR(150) NOT NULL,
    seccion_electoral VARCHAR(10) DEFAULT '',
    calle VARCHAR(150) NOT NULL,
    no_exterior VARCHAR(20) NOT NULL,
    no_interior VARCHAR(20) DEFAULT '',
    colonia VARCHAR(150) NOT NULL,
    municipio VARCHAR(150) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    observaciones TEXT DEFAULT '',
    ine_path TEXT DEFAULT NULL,
    estatus VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE codigos_postales (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo_postal VARCHAR(10) NOT NULL,
    colonia VARCHAR(150) NOT NULL,
    municipio VARCHAR(150) DEFAULT ''
);

CREATE INDEX idx_codigos_postales_cp ON codigos_postales(codigo_postal);

CREATE TABLE bloqueos_cita (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha DATE NOT NULL,
    hora TIME DEFAULT NULL,
    dia_completo BOOLEAN DEFAULT FALSE,
    motivo VARCHAR(255) DEFAULT '',
    empleado_id INTEGER REFERENCES empleados(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_bloqueos_fecha ON bloqueos_cita(fecha);
CREATE INDEX idx_bloqueos_fecha_hora ON bloqueos_cita(fecha, hora);

CREATE TABLE eventos_comedor (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    lugar VARCHAR(255) DEFAULT '',
    descripcion TEXT DEFAULT '',
    cupo_maximo INT DEFAULT 0,
    tipo_comedor VARCHAR(100) DEFAULT '',
    grupo_dirigido VARCHAR(150) DEFAULT '',
    menu TEXT DEFAULT '',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_eventos_comedor_fecha ON eventos_comedor(fecha);

CREATE TABLE registros_comedor (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evento_id INTEGER REFERENCES eventos_comedor(id) ON DELETE SET NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    celular_1 VARCHAR(15) NOT NULL,
    celular_2 VARCHAR(15) DEFAULT '',
    correo VARCHAR(150) DEFAULT '',
    seccion_electoral VARCHAR(10) DEFAULT '',
    calle VARCHAR(150) DEFAULT '',
    no_exterior VARCHAR(20) DEFAULT '',
    no_interior VARCHAR(20) DEFAULT '',
    colonia VARCHAR(150) DEFAULT '',
    municipio VARCHAR(150) DEFAULT '',
    codigo_postal VARCHAR(10) DEFAULT '',
    numero_personas INT DEFAULT 1,
    categoria_vulnerable VARCHAR(150) DEFAULT '',
    observaciones TEXT DEFAULT '',
    estatus VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE personal_access_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT DEFAULT NULL,
    last_used_at TIMESTAMP DEFAULT NULL,
    expires_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT NULL
);

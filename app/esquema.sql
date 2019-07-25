CREATE TABLE IF NOT EXISTS sesiones(
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    datos TEXT NOT NULL,
    ultimo_acceso BIGINT UNSIGNED NOT NULL
);


CREATE TABLE IF NOT EXISTS sesiones_usuarios(
    id_sesion VARCHAR(255) NOT NULL UNIQUE,
    id_usuario BIGINT UNSIGNED NOT NULL
);

CREATE TABLE usuarios(
    id BIGINT UNSIGNED NOT NULL auto_increment,
    administrador BOOLEAN NOT NULL DEFAULT FALSE,
    correo VARCHAR(255) NOT NULL UNIQUE,
    palabra_secreta VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
);


CREATE TABLE usuarios_no_verificados(
    id BIGINT UNSIGNED NOT NULL auto_increment,
    correo VARCHAR(255) NOT NULL UNIQUE,
    palabra_secreta VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
);


CREATE TABLE verificaciones_pendientes_usuarios(
    id BIGINT UNSIGNED NOT NULL auto_increment,
    token VARCHAR(20) NOT NULL UNIQUE,
    id_usuario_no_verificado BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(id_usuario_no_verificado) REFERENCES usuarios_no_verificados(id) ON DELETE CASCADE
);

CREATE TABLE restablecimientos_passwords_usuarios(
    token VARCHAR(20) NOT NULL UNIQUE,
    id_usuario BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY(id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);


CREATE TABLE notas(
    id BIGINT UNSIGNED NOT NULL auto_increment,
    fecha_hora DATETIME NOT NULL,
    id_usuario BIGINT UNSIGNED NOT NULL,
    contenido TEXT NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);
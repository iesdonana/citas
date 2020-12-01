DROP TABLE IF EXISTS usuarios CASCADE;

CREATE TABLE usuarios
(
    id       bigserial    PRIMARY KEY
  , login    varchar(255) NOT NULL UNIQUE
  , password varchar(255) NOT NULL
);

DROP TABLE IF EXISTS citas CASCADE;

CREATE TABLE citas
(
    id         bigserial    PRIMARY KEY
  , fecha_hora timestamp(0) NOT NULL UNIQUE
  , usuario_id bigint       NOT NULL REFERENCES usuarios (id)
);

CREATE INDEX idx_citas_usuario_id ON citas (usuario_id);

INSERT INTO usuarios (login, password)
VALUES ('usuario', crypt('usuario', gen_salt('bf', 10)))
     , ('admin', crypt('admin', gen_salt('bf', 10)));
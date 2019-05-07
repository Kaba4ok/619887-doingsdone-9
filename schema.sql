CREATE DATABASE dvp
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE dvp;

CREATE TABLE projects(
  id_project INT AUTO_INCREMENT PRIMARY KEY,
  project CHAR(64) NOT NULL UNIQUE,
  id_user INT
);

CREATE TABLE tasks(
  id_task INT AUTO_INCREMENT PRIMARY KEY,
  dt_create TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status INT(64) DEFAULT 0,
  task CHAR(128) NOT NULL,
  file CHAR(128) DEFAULT NULL,
  deadline DATE,
  id_user INT,
  id_project INT
);

CREATE TABLE users(
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  dt_reg TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email CHAR(128) NOT NULL UNIQUE,
  name CHAR(128) NOT NULL UNIQUE,
  password CHAR(64) NOT NULL
);

CREATE INDEX dt_create ON tasks(dt_create);
CREATE INDEX status ON tasks(status);
CREATE INDEX deadline ON tasks(deadline);
CREATE INDEX dt_reg ON users(dt_reg);
CREATE INDEX id_user ON projects(id_user);
CREATE INDEX id_user ON tasks(id_user);
CREATE INDEX id_project ON tasks(id_project);

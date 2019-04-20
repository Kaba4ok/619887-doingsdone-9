CREATE DATABASE dvp
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE dvp;

CREATE TABLE projects(
  id_project INT AUTO_INCREMENT PRIMARY KEY,
  project CHAR(64) NOT NULL UNIQUE
);

CREATE TABLE tasks(
  id_task INT AUTO_INCREMENT PRIMARY KEY,
  dt_create TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status INT(64) DEFAULT 0,
  task CHAR(128) NOT NULL UNIQUE,
  file CHAR(128) UNIQUE,
  deadline TIMESTAMP
);

CREATE TABLE users(
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  dt_reg TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email CHAR(128) NOT NULL UNIQUE,
  name CHAR(128) NOT NULL UNIQUE,
  password CHAR(64) NOT NULL
);

CREATE UNIQUE INDEX t_task ON tasks(task);
CREATE UNIQUE INDEX t_file ON tasks(file);
CREATE UNIQUE INDEX u_email ON users(email);
CREATE UNIQUE INDEX u_name ON users(name);
CREATE INDEX t_dt_create ON tasks(dt_create);
CREATE INDEX t_status ON tasks(status);
CREATE INDEX t_deadline ON tasks(deadline);
CREATE INDEX u_dt_reg ON users(dt_reg);

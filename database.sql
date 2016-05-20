-- CREATE DATABASE lbag;

CREATE TABLE users (
  user_id         SERIAL PRIMARY KEY,
  username        VARCHAR NOT NULL UNIQUE,
  email           VARCHAR NOT NULL UNIQUE,
  password        VARCHAR,
  date_registered TIMESTAMP NOT NULL default now(),
  origin          VARCHAR NOT NULL
);

CREATE INDEX "users_origin" ON "users" ("origin");

CREATE TABLE projects (
  project_id  SERIAL PRIMARY KEY,
  name        VARCHAR NOT NULL UNIQUE
);

CREATE TABLE user_projects (
  project_id    INTEGER NOT NULL REFERENCES projects,
  user_id       INTEGER NOT NULL REFERENCES users,
  is_subscribed BOOLEAN NOT NULL,
  PRIMARY KEY (project_id, user_id)
);

CREATE TABLE notifications (
  notification_id     SERIAL PRIMARY KEY,
  notification_desc   VARCHAR(50) NOT NULL
);

CREATE TABLE user_notifications (
  user_id             INTEGER NOT NULL REFERENCES users,
  notification_id     INTEGER NOT NULL REFERENCES notifications,
  PRIMARY KEY (user_id, notification_id)
);

CREATE TABLE newsletter (
  user_id   INTEGER REFERENCES users (user_id),
  frequency INTEGER NOT NULL
);

CREATE TABLE email_verification (
  user_id         SERIAL PRIMARY KEY,
  email           VARCHAR NOT NULL UNIQUE,
  code            VARCHAR NOT NULL UNIQUE
);

CREATE TABLE settings (
  user_id                 INTEGER PRIMARY KEY REFERENCES users (user_id),
  notifications_frequency VARCHAR
);
CREATE TABLE "jobs" (
  "job_id"  INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  "hint"  TEXT NOT NULL UNIQUE,
  "code"  TEXT NOT NULL,
  "testbench_id"  INTEGER NOT NULL,
  "status"  INTEGER NOT NULL,
  "ip"  TEXT NOT NULL,
  "vcd"  INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE "reports" (
  "report_id"  INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  "meta"  TEXT NOT NULL,
  "analysis"  TEXT NOT NULL,
  "compilation"  TEXT NOT NULL,
  "execution"  TEXT NOT NULL,
  "time"  TEXT NOT NULL,
  "code"  INTEGER,
  "job_id"  INTEGER NOT NULL UNIQUE
);

CREATE TABLE "testbenches" (
  "testbench_id"  INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  "name"  TEXT NOT NULL UNIQUE,
  "description"  TEXT NOT NULL,
  "contents"  TEXT NOT NULL UNIQUE
);

CREATE TABLE "admins" (
  "admin_id"  INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  "name"  TEXT NOT NULL UNIQUE,
  "salt"  TEXT NOT NULL,
  "opaque"  TEXT NOT NULL
);

CREATE TABLE "tokens" (
  "token_id"  INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  "code"  TEXT NOT NULL UNIQUE,
  "username"  TEXT NOT NULL,
  "expires"  INTEGER NOT NULL,
  "revoked"  INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE "config" (
  "key"  TEXT NOT NULL UNIQUE,
  "value"  TEXT
);

INSERT INTO admins (name, salt, opaque)
  VALUES ("borges", "LE_SALT", "afe4aa22ddbb35ffd2d21dd830f1682b9b1cc3732abe62bb00404129d297b93c7a099ff8ccecb55c26abcbce3cf6eec91b3dc4f3b8b4c964262a515c02cf7127");
INSERT INTO admins (name, salt, opaque)
  VALUES ("gjvnq", "SAL_DO_GABRIEL", "f34e3cbf10965c3fcc49b665affe6b7db3cb55711d8b636d1a86d321041a768752373eaed20f919feefdb7191e8be2f6c06bc5ed3e4c879dd40e8d962fb43b42");

INSERT INTO config (key, value) VALUES ("grecaptcha_sitekey", NULL);
INSERT INTO config (key, value) VALUES ("grecaptcha_secretkey", NULL);

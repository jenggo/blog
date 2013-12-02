-- Adminer 3.7.1 SQLite 3 dump

CREATE TABLE "artikel" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "id_kategori" integer NOT NULL,
  "id_user" integer NULL,
  "judul" text NOT NULL,
  "slug" text NOT NULL,
  "tanggal" text NOT NULL,
  "isi" text NOT NULL,
  "aktif" numeric NULL,
  "komentar" numeric NULL,
  "pages" numeric NULL
);


CREATE TABLE "kategori" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "nama" text NOT NULL,
  "slug" text NOT NULL,
  "aktif" numeric
);


CREATE TABLE "komentar" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "id_artikel" integer NOT NULL,
  "id_user" integer NULL,
  "tanggal" text NOT NULL,
  "nama" text NULL,
  "email" text NULL,
  "situs" text NULL,
  "isi" text NOT NULL,
  "aktif" numeric NULL,
  "kontak" numeric NULL
);


CREATE TABLE "umum" (
  "id" integer NOT NULL DEFAULT '1',
  "title" text NULL,
  "slogan" text NULL,
  "meta_description" text NULL,
  "meta_key" text NULL,
  "wp_key" numeric NULL,
  "about" text NULL,
  "featured" integer NULL
);


CREATE TABLE "user" (
	"id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
	"nama" text NOT NULL,
	"email" text NOT NULL,
	"situs" text,
	"password" text NOT NULL,
	"aktif" numeric NOT NULL
, "akses" numeric NOT NULL DEFAULT '0');


-- 

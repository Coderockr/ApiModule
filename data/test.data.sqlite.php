<?php

return array(
    'cliente' => array(
        'create' => 'create table if not exists "cliente" (
                      "id" INTEGER PRIMARY KEY NOT NULL,
                      "nome" varchar(100) NOT NULL,
                      "login" varchar(45) NOT NULL,
                      "senha" varchar(255) NOT NULL,
                      "status" int NOT NULL,
                      "criado" timestamp NOT NULL
                    )',
        'drop' => "delete from cliente"
    ),
    'permissao' => array(
        'create' => 'create table if not exists "permissao" (
                      "id" integer primary key NOT NULL,
                      "cliente_id" smallint(6) NOT NULL,
                      "recurso" varchar(100) NOT NULL,
                      "criado" timestamp NOT NULL,
                      CONSTRAINT "fk_permissao_cliente1" FOREIGN KEY ("cliente_id") REFERENCES "cliente" ("id")
                    )',
        'drop' =>'delete from permissao'
    ),
    'token' => array(
        'create' => 'create table if not exists "token" (
                      "id" integer primary key NOT NULL,
                      "cliente_id" smallint(6) NOT NULL,
                      "token" varchar(255) NOT NULL,
                      "ip" varchar(20) DEFAULT NULL,
                      "status" int,
                      "criado" timestamp NOT NULL,
                      CONSTRAINT "fk_token_cliente" FOREIGN KEY ("cliente_id") REFERENCES "cliente" ("id")
                    );',
        'drop' =>'delete from token'
    ),
    'log' => array(
        'create' => 'create table if not exists "log" (
                      "id" integer primary key NOT NULL,
                      "token_id" int(11) NOT NULL,
                      "recurso" varchar(100) NOT NULL,
                      "criado" timestamp NULL DEFAULT NULL
                    );',
        'drop' =>'delete from log'
    ),

);
<?php

return array(
    'cliente_seq' => array(
        'create' => 'CREATE SEQUENCE ACADTEST.cliente_seq MINVALUE 1 INCREMENT BY 1 START WITH 1 CACHE 20 NOORDER NOCYCLE',
        'drop' => 'drop sequence acadtest.cliente_seq'
    ),
    'permissao_seq' => array(
        'create' => 'CREATE SEQUENCE ACADTEST.permissao_seq MINVALUE 1 INCREMENT BY 1 START WITH 1 CACHE 20 NOORDER NOCYCLE',
        'drop' => 'drop sequence acadtest.permissao_seq'
    ),
    'token_seq' => array(
        'create' => 'CREATE SEQUENCE ACADTEST.token_seq MINVALUE 1 INCREMENT BY 1 START WITH 1 CACHE 20 NOORDER NOCYCLE',
        'drop' => 'drop sequence acadtest.token_seq'
    ),
    'log_seq' => array(
        'create' => 'CREATE SEQUENCE ACADTEST.log_seq MINVALUE 1 INCREMENT BY 1 START WITH 1 NOORDER NOCYCLE',
        'drop' => 'drop sequence acadtest.log_seq'
    ),
    'cliente' => array(
        'create' => 'create table acadtest.cliente (
                      id number(10) NULL,
                      nome varchar(100) NOT NULL,
                      login varchar(45) NOT NULL,
                      senha varchar(255) NOT NULL,
                      status varchar(10) NOT NULL,
                      criado date NOT NULL
                    )',
        'drop' => "drop table acadtest.cliente"
    ),
    'permissao' => array(
        'create' => 'create table acadtest.permissao (
                      "ID" number(10) NOT NULL,
                      "CLIENTE_ID" number(10) NOT NULL,
                      "RECURSO" varchar(100) NOT NULL,
                      "CRIADO" date NOT NULL
                    )',
        'drop' => 'drop table acadtest.permissao'
    ),
    'token' => array(
        'create' => 'create table acadtest.token (
                      "ID" number(10) NOT NULL,
                      "CLIENTE_ID" number(10) NOT NULL,
                      "TOKEN" varchar(255) NOT NULL,
                      "IP" varchar(20) DEFAULT NULL,
                      "STATUS" varchar(10),
                      "CRIADO" date NOT NULL
                    )',
        'drop' => 'drop table acadtest.token'
    ),
    'log' => array(
        'create' => 'create table acadtest.log (
                      "ID" number(10) NOT NULL,
                      "TOKEN_ID" number(11) NOT NULL,
                      "RECURSO" varchar(100) NOT NULL,
                      "CRIADO" date NULL 
                    )',
        'drop' => 'drop table acadtest.log'
    ),
);
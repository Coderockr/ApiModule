CREATE SEQUENCE cliente_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE cliente_id_seq
  OWNER TO postgres;

CREATE SEQUENCE token_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE token_id_seq
  OWNER TO postgres;

CREATE SEQUENCE permissao_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE permissao_id_seq
  OWNER TO postgres;


CREATE TYPE cliente_status AS ENUM ('ATIVO', 'INATIVO');
CREATE TYPE token_status AS ENUM ('VALIDO', 'INVALIDO');

CREATE TABLE cliente
(
  id integer NOT NULL DEFAULT nextval('cliente_id_seq'::regclass),
  nome character varying(100) NOT NULL,
  login character varying(45) NOT NULL,
  senha character varying(255) NOT NULL,
  status cliente_status NOT NULL DEFAULT 'ATIVO',
  criado timestamp without time zone,
  CONSTRAINT cliente_pkey PRIMARY KEY (id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE cliente
  OWNER TO postgres;

CREATE TABLE token
(
  id integer NOT NULL DEFAULT nextval('token_id_seq'::regclass),
  cliente_id integer NOT NULL,
  token character varying(255) NOT NULL,  
  ip character varying(20),  
  status token_status NOT NULL DEFAULT 'VALIDO',
  criado timestamp without time zone,
  CONSTRAINT token_pkey PRIMARY KEY (id ),
  CONSTRAINT fk_token_cliente FOREIGN KEY (cliente_id)
      REFERENCES cliente (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE token
  OWNER TO postgres;    

CREATE TABLE permissao
(
  id integer NOT NULL DEFAULT nextval('permissao_id_seq'::regclass),
  cliente_id integer NOT NULL,
  recurso character varying(100) NOT NULL,  
  criado timestamp without time zone,
  CONSTRAINT permissao_pkey PRIMARY KEY (id ),
  CONSTRAINT fk_permissao_cliente FOREIGN KEY (cliente_id)
      REFERENCES cliente (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE permissao
  OWNER TO postgres;    

CREATE TABLE log
(
  token_id integer NOT NULL,
  recurso character varying(100) NOT NULL,
  criado timestamp without time zone
)
WITH (
  OIDS=FALSE
);
ALTER TABLE log
  OWNER TO postgres;

begin;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "cliente" (
  "id" INTEGER PRIMARY KEY NOT NULL,
  "nome" varchar(100) NOT NULL,
  "login" varchar(45) NOT NULL,
  "senha" varchar(255) NOT NULL,
  "status" int NOT NULL,
  "criado" timestamp NOT NULL
);
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "log" (
  "token_id" int(11) NOT NULL,
  "recurso" varchar(100) NOT NULL,
  "criado" timestamp NULL DEFAULT NULL,
  CONSTRAINT "fk_log_token1" FOREIGN KEY ("token_id") REFERENCES "token" ("id")
);
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "permissao" (
  "id" integer primary key NOT NULL,
  "cliente_id" smallint(6) NOT NULL,
  "recurso" varchar(100) NOT NULL DEFAULT '*',
  "criado" timestamp NOT NULL,
  CONSTRAINT "fk_permissao_cliente1" FOREIGN KEY ("cliente_id") REFERENCES "cliente" ("id")
);
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE "token" (
  "id" integer primary key NOT NULL,
  "cliente_id" smallint(6) NOT NULL,
  "token" varchar(255) NOT NULL,
  "ip" varchar(20) DEFAULT NULL,
  "status" int,
  "criado" timestamp NOT NULL,
  CONSTRAINT "fk_token_cliente" FOREIGN KEY ("cliente_id") REFERENCES "cliente" ("id")
);
/*!40101 SET character_set_client = @saved_cs_client */;
commit;

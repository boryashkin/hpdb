CREATE TABLE "website" (
	"id" serial NOT NULL,
	"base_url" VARCHAR(255) NOT NULL UNIQUE,
	"created_at" DATETIME NOT NULL,
	CONSTRAINT website_pk PRIMARY KEY ("id")
) WITH (
  OIDS=FALSE
);



CREATE TABLE "website_page" (
	"id" serial NOT NULL,
	"website_id" integer NOT NULL,
	"url" VARCHAR(1000) NOT NULL,
	"type_id" integer NOT NULL,
	"created_at" DATETIME NOT NULL
) WITH (
  OIDS=FALSE
);



CREATE TABLE "type" (
	"id" serial NOT NULL,
	"name" serial(255) NOT NULL,
	"url" VARCHAR(255) NOT NULL UNIQUE,
	"created_at" DATETIME NOT NULL,
	CONSTRAINT type_pk PRIMARY KEY ("id")
) WITH (
  OIDS=FALSE
);



CREATE TABLE "website_to_index" (
	"id" serial NOT NULL,
	"url" VARCHAR(255) NOT NULL UNIQUE,
	"created_at" DATETIME NOT NULL,
	"website_id" integer NOT NULL
) WITH (
  OIDS=FALSE
);



CREATE TABLE "tag_for_page" (
	"id" serial NOT NULL,
	"tag" VARCHAR(255) NOT NULL UNIQUE,
	"url" VARCHAR(255) NOT NULL UNIQUE,
	"created_at" DATETIME NOT NULL
) WITH (
  OIDS=FALSE
);



CREATE TABLE "user" (
	"id" serial NOT NULL,
	"username" VARCHAR(255) NOT NULL UNIQUE,
	"created_at" DATETIME NOT NULL,
	"password_hash" VARCHAR(255) NOT NULL
) WITH (
  OIDS=FALSE
);



CREATE TABLE "tagged_page" (
	"tag_id" integer NOT NULL,
	"page_id" integer NOT NULL,
	"user_id" integer NOT NULL
) WITH (
  OIDS=FALSE
);



CREATE TABLE "tag_for_website" (
	"id" serial NOT NULL,
	"tag" VARCHAR(255) NOT NULL UNIQUE,
	"url" VARCHAR(255) NOT NULL UNIQUE,
	"created_at" DATETIME NOT NULL
) WITH (
  OIDS=FALSE
);



CREATE TABLE "tagged_website" (
	"tag_id" integer NOT NULL,
	"website_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"created_at" DATETIME NOT NULL
) WITH (
  OIDS=FALSE
);




ALTER TABLE "website_page" ADD CONSTRAINT "website_page_fk0" FOREIGN KEY ("website_id") REFERENCES "website"("id");
ALTER TABLE "website_page" ADD CONSTRAINT "website_page_fk1" FOREIGN KEY ("type_id") REFERENCES "type"("id");


ALTER TABLE "website_to_index" ADD CONSTRAINT "website_to_index_fk0" FOREIGN KEY ("website_id") REFERENCES "website"("id");



ALTER TABLE "tagged_page" ADD CONSTRAINT "tagged_page_fk0" FOREIGN KEY ("tag_id") REFERENCES "tag_for_page"("id");
ALTER TABLE "tagged_page" ADD CONSTRAINT "tagged_page_fk1" FOREIGN KEY ("page_id") REFERENCES "website_page"("id");
ALTER TABLE "tagged_page" ADD CONSTRAINT "tagged_page_fk2" FOREIGN KEY ("user_id") REFERENCES "user"("id");


ALTER TABLE "tagged_website" ADD CONSTRAINT "tagged_website_fk0" FOREIGN KEY ("tag_id") REFERENCES "tag_for_website"("id");
ALTER TABLE "tagged_website" ADD CONSTRAINT "tagged_website_fk1" FOREIGN KEY ("website_id") REFERENCES "website"("id");
ALTER TABLE "tagged_website" ADD CONSTRAINT "tagged_website_fk2" FOREIGN KEY ("user_id") REFERENCES "tag_for_website"("id");


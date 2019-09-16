CREATE DATABASE IF NOT EXIST blog;
USE blog;

CREATE TABLE users(
id       int(255) auto_increment not null,
role     varchar(20),
name     varchar(255),
surname  varchar(255),
email    varchar(255),
password varchar(255),
image    varchar(255),
CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDB;

CREATE TABLE categories(
id           int(255) auto_increment not null,
name         varchar(255),
description  text,
CONSTRAINT pk_categories PRIMARY KEY(id)
)ENGINE=InnoDB;

CREATE TABLE entries(
id           int(255) auto_increment not null,
user_id      int(255) not null,
category_id  int(255) not nullm
title        varchar(255),
content      text,
status       varchar(255),
image        varchar(255),
CONSTRAINT pk_entries PRIMARY KEY(id),
CONSTRAINT fk_entries_users foreign key(user_id) references users(id),
CONSTRAINT fk_entries_categories foreign key(category_id) references categories(id),
)ENGINE=InnoDB;

CREATE TABLE tags(
id           int(255) auto_increment not null,
name         varchar(255),
description  text,
CONSTRAINT pk_tags PRIMARY KEY(id)
)ENGINE=InnoDB;

CREATE TABLE entry_tags(
id           int(255) auto_increment not null,
entry_id     int(255) not null,
tag_id      int(255) not null,
CONSTRAINT pk_entry_tag PRIMARY KEY(id),
CONSTRAINT fk_entry_tag_entries foreign key(entry_id) references entries(id),
CONSTRAINT fk_entry_tag_tags foreign key(tag_id) references tags(id)
)ENGINE=InnoDB;

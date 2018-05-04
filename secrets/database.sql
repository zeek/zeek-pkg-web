CREATE DATABASE IF NOT EXISTS bro DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON bro.* to brouser@'%'         IDENTIFIED BY 'BRO_USER_PASSWORD';
GRANT ALL PRIVILEGES on bro.* to brouser@'localhost' IDENTIFIED BY 'BRO_USER_PASSWORD';

use bro;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS sessions, users, packages,metadatas, tags, metadatas_tags, updater;
SET FOREIGN_KEY_CHECKS=1;

source /var/www/bropkg/config/schema/sessions.sql; 

CREATE TABLE packages (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    short_name VARCHAR(255) NOT NULL,
    url VARCHAR(255),
    readme MEDIUMTEXT,
    readme_name VARCHAR(255),
    subscribers_count INT DEFAULT 0 NOT NULL,
    stargazers_count INT DEFAULT 0 NOT NULL,
    open_issues_count INT DEFAULT 0 NOT NULL,
    forks_count INT DEFAULT 0 NOT NULL,
    pushed_at DATETIME,
    created DATETIME,
    modified DATETIME
);

CREATE TABLE metadatas (
    id CHAR(36) PRIMARY KEY,
    package_id CHAR(36) NOT NULL,
    version VARCHAR(255),
    description TEXT,
    script_dir TEXT,
    plugin_dir TEXT,
    build_command TEXT,
    user_vars TEXT,
    test_command TEXT,
    config_files TEXT,
    depends TEXT,
    external_depends TEXT,
    suggests TEXT,
    created DATETIME,
    modified DATETIME,
    FOREIGN KEY package_key (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

CREATE TABLE tags (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created DATETIME,
    modified DATETIME
);

create TABLE metadatas_tags (
    metadata_id CHAR(36) NOT NULL,
    tag_id CHAR(36) NOT NULL,
    PRIMARY KEY (metadata_id, tag_id),
    FOREIGN KEY metadata_key (metadata_id) REFERENCES metadatas(id) ON DELETE CASCADE,
    FOREIGN KEY tag_key (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

create TABLE updater (
    id INT PRIMARY KEY,
    status CHAR(16),
    package VARCHAR(255),
    started DATETIME,
    ended DATETIME
);

-- Schema used to build the `test` database connection for the PHPUnit suite.
--
-- It mirrors the production schema in `secrets/database.sql`, minus the
-- database creation, grants and session table (which are environment specific).
-- Foreign key constraints are intentionally omitted so fixtures can be inserted
-- and torn down in any order; referential integrity is exercised through the
-- ORM's application rules instead (see MetadatasTagsTable::buildRules()).

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
    package_ci TEXT,
    created DATETIME,
    modified DATETIME
);

CREATE TABLE tags (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created DATETIME,
    modified DATETIME
);

CREATE TABLE metadatas_tags (
    metadata_id CHAR(36) NOT NULL,
    tag_id CHAR(36) NOT NULL,
    PRIMARY KEY (metadata_id, tag_id)
);

CREATE TABLE updater (
    id INT PRIMARY KEY,
    status CHAR(16),
    package VARCHAR(255),
    started DATETIME,
    ended DATETIME
);

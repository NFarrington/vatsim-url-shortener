<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB as DB;

class DoctrineSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::connection()->getName() == 'mysql') {
            DB::statement('ALTER TABLE news CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
            DB::statement('ALTER TABLE url_analytics DROP FOREIGN KEY url_analytics_url_id_foreign');
            DB::statement('ALTER TABLE url_analytics DROP FOREIGN KEY url_analytics_user_id_foreign');
            DB::statement('ALTER TABLE url_analytics CHANGE get_data get_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE custom_headers custom_headers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
            DB::statement('DROP INDEX url_analytics_user_id_foreign ON url_analytics');
            DB::statement('CREATE INDEX IDX_7EC4BD62A76ED395 ON url_analytics (user_id)');
            DB::statement('DROP INDEX url_analytics_url_id_foreign ON url_analytics');
            DB::statement('CREATE INDEX IDX_7EC4BD6281CFDAE7 ON url_analytics (url_id)');
            DB::statement('ALTER TABLE url_analytics ADD CONSTRAINT url_analytics_url_id_foreign FOREIGN KEY (url_id) REFERENCES urls (id)');
            DB::statement('ALTER TABLE url_analytics ADD CONSTRAINT url_analytics_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('DROP INDEX revisions_model_type_model_id_index ON revisions');
            DB::statement('ALTER TABLE revisions DROP FOREIGN KEY revisions_user_id_foreign');
            DB::statement('ALTER TABLE revisions CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
            DB::statement('DROP INDEX revisions_user_id_foreign ON revisions');
            DB::statement('CREATE INDEX IDX_89B12285A76ED395 ON revisions (user_id)');
            DB::statement('ALTER TABLE revisions ADD CONSTRAINT revisions_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE email_verifications DROP FOREIGN KEY email_verifications_user_id_foreign');
            DB::statement('ALTER TABLE email_verifications CHANGE created_at created_at DATETIME NOT NULL');
            DB::statement('DROP INDEX email_verifications_user_id_foreign ON email_verifications');
            DB::statement('CREATE INDEX IDX_7EB1F4EEA76ED395 ON email_verifications (user_id)');
            DB::statement('ALTER TABLE email_verifications ADD CONSTRAINT email_verifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE domain_organization CHANGE domain_id domain_id INT UNSIGNED NOT NULL, CHANGE organization_id organization_id INT UNSIGNED NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
            DB::statement('ALTER TABLE domain_organization ADD CONSTRAINT FK_58E115F1115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id)');
            DB::statement('ALTER TABLE domain_organization ADD CONSTRAINT FK_58E115F132C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id)');
            DB::statement('CREATE INDEX IDX_58E115F1115F0EE5 ON domain_organization (domain_id)');
            DB::statement('CREATE INDEX IDX_58E115F132C8A3DE ON domain_organization (organization_id)');
            DB::statement('DROP INDEX users_email_unique ON users');
            DB::statement('ALTER TABLE users ADD admin TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE vatsim_sso_data vatsim_sso_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE vatsim_status_data vatsim_status_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
            DB::statement('ALTER TABLE organization_prefix_applications DROP FOREIGN KEY organization_prefix_applications_organization_id_foreign');
            DB::statement('ALTER TABLE organization_prefix_applications DROP FOREIGN KEY organization_prefix_applications_user_id_foreign');
            DB::statement('ALTER TABLE organization_prefix_applications CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
            DB::statement('DROP INDEX organization_prefix_applications_organization_id_foreign ON organization_prefix_applications');
            DB::statement('CREATE INDEX IDX_54CE7FE632C8A3DE ON organization_prefix_applications (organization_id)');
            DB::statement('DROP INDEX organization_prefix_applications_user_id_foreign ON organization_prefix_applications');
            DB::statement('CREATE INDEX IDX_54CE7FE6A76ED395 ON organization_prefix_applications (user_id)');
            DB::statement('ALTER TABLE organization_prefix_applications ADD CONSTRAINT organization_prefix_applications_organization_id_foreign FOREIGN KEY (organization_id) REFERENCES organizations (id)');
            DB::statement('ALTER TABLE organization_prefix_applications ADD CONSTRAINT organization_prefix_applications_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE organization_user DROP FOREIGN KEY organization_user_organization_id_foreign');
            DB::statement('ALTER TABLE organization_user DROP FOREIGN KEY organization_user_user_id_foreign');
            DB::statement('ALTER TABLE organization_user CHANGE role_id role_id SMALLINT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
            DB::statement('DROP INDEX organization_user_organization_id_foreign ON organization_user');
            DB::statement('CREATE INDEX IDX_B49AE8D432C8A3DE ON organization_user (organization_id)');
            DB::statement('DROP INDEX organization_user_user_id_foreign ON organization_user');
            DB::statement('CREATE INDEX IDX_B49AE8D4A76ED395 ON organization_user (user_id)');
            DB::statement('ALTER TABLE organization_user ADD CONSTRAINT organization_user_organization_id_foreign FOREIGN KEY (organization_id) REFERENCES organizations (id)');
            DB::statement('ALTER TABLE organization_user ADD CONSTRAINT organization_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('DROP INDEX urls_url_index ON urls');
            DB::statement('ALTER TABLE urls DROP FOREIGN KEY urls_domain_id_foreign');
            DB::statement('ALTER TABLE urls DROP FOREIGN KEY urls_organization_id_foreign');
            DB::statement('ALTER TABLE urls DROP FOREIGN KEY urls_user_id_foreign');
            DB::statement('ALTER TABLE urls CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
            DB::statement('DROP INDEX urls_organization_id_foreign ON urls');
            DB::statement('CREATE INDEX IDX_2A9437A132C8A3DE ON urls (organization_id)');
            DB::statement('DROP INDEX urls_user_id_foreign ON urls');
            DB::statement('CREATE INDEX IDX_2A9437A1A76ED395 ON urls (user_id)');
            DB::statement('DROP INDEX urls_domain_id_foreign ON urls');
            DB::statement('CREATE INDEX IDX_2A9437A1115F0EE5 ON urls (domain_id)');
            DB::statement('ALTER TABLE urls ADD CONSTRAINT urls_domain_id_foreign FOREIGN KEY (domain_id) REFERENCES domains (id)');
            DB::statement('ALTER TABLE urls ADD CONSTRAINT urls_organization_id_foreign FOREIGN KEY (organization_id) REFERENCES organizations (id)');
            DB::statement('ALTER TABLE urls ADD CONSTRAINT urls_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE email_events CHANGE data data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', CHANGE triggered_at triggered_at DATETIME NOT NULL');
            DB::statement('ALTER TABLE organizations CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        } elseif (DB::connection()->getName() == 'sqlite') {
            DB::statement('CREATE TEMPORARY TABLE __temp__news AS SELECT id, title, content, published, created_at, updated_at FROM news');
            DB::statement('DROP TABLE news');
            DB::statement('CREATE TABLE news (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL COLLATE BINARY, published BOOLEAN DEFAULT \'0\' NOT NULL, title VARCHAR(250) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
            DB::statement('INSERT INTO news (id, title, content, published, created_at, updated_at) SELECT id, title, content, published, created_at, updated_at FROM __temp__news');
            DB::statement('DROP TABLE __temp__news');
            DB::statement('CREATE TEMPORARY TABLE __temp__url_analytics AS SELECT id, user_id, url_id, request_time, remote_addr, request_uri, get_data, custom_headers, response_code, created_at, updated_at, http_host, http_referer, http_user_agent FROM url_analytics');
            DB::statement('DROP TABLE url_analytics');
            DB::statement('CREATE TABLE url_analytics (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER UNSIGNED DEFAULT NULL, url_id INTEGER UNSIGNED DEFAULT NULL, request_time VARCHAR(255) DEFAULT NULL COLLATE BINARY, remote_addr VARCHAR(255) DEFAULT NULL COLLATE BINARY, request_uri VARCHAR(255) DEFAULT NULL COLLATE BINARY, response_code INTEGER DEFAULT NULL, http_host CLOB DEFAULT NULL COLLATE BINARY, http_referer CLOB DEFAULT NULL COLLATE BINARY, http_user_agent CLOB DEFAULT NULL COLLATE BINARY, get_data CLOB DEFAULT NULL --(DC2Type:json)
        , custom_headers CLOB DEFAULT NULL --(DC2Type:json)
        , created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_7EC4BD62A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7EC4BD6281CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            DB::statement('INSERT INTO url_analytics (id, user_id, url_id, request_time, remote_addr, request_uri, get_data, custom_headers, response_code, created_at, updated_at, http_host, http_referer, http_user_agent) SELECT id, user_id, url_id, request_time, remote_addr, request_uri, get_data, custom_headers, response_code, created_at, updated_at, http_host, http_referer, http_user_agent FROM __temp__url_analytics');
            DB::statement('DROP TABLE __temp__url_analytics');
            DB::statement('CREATE INDEX IDX_7EC4BD62A76ED395 ON url_analytics (user_id)');
            DB::statement('CREATE INDEX IDX_7EC4BD6281CFDAE7 ON url_analytics (url_id)');
            DB::statement('DROP INDEX revisions_model_type_model_id_index');
            DB::statement('CREATE TEMPORARY TABLE __temp__revisions AS SELECT id, model_type, model_id, user_id, old_value, new_value, created_at, updated_at, property_name FROM revisions');
            DB::statement('DROP TABLE revisions');
            DB::statement('CREATE TABLE revisions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER UNSIGNED DEFAULT NULL, model_type VARCHAR(255) NOT NULL COLLATE BINARY, old_value CLOB DEFAULT NULL COLLATE BINARY, new_value CLOB DEFAULT NULL COLLATE BINARY, property_name VARCHAR(255) NOT NULL COLLATE BINARY, model_id BIGINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_89B12285A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            DB::statement('INSERT INTO revisions (id, model_type, model_id, user_id, old_value, new_value, created_at, updated_at, property_name) SELECT id, model_type, model_id, user_id, old_value, new_value, created_at, updated_at, property_name FROM __temp__revisions');
            DB::statement('DROP TABLE __temp__revisions');
            DB::statement('CREATE INDEX IDX_89B12285A76ED395 ON revisions (user_id)');
            DB::statement('CREATE TEMPORARY TABLE __temp__email_verifications AS SELECT id, user_id, token, created_at FROM email_verifications');
            DB::statement('DROP TABLE email_verifications');
            DB::statement('CREATE TABLE email_verifications (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER UNSIGNED NOT NULL, token VARCHAR(255) NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL, CONSTRAINT FK_7EB1F4EEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            DB::statement('INSERT INTO email_verifications (id, user_id, token, created_at) SELECT id, user_id, token, created_at FROM __temp__email_verifications');
            DB::statement('DROP TABLE __temp__email_verifications');
            DB::statement('CREATE INDEX IDX_7EB1F4EEA76ED395 ON email_verifications (user_id)');
            DB::statement('CREATE TEMPORARY TABLE __temp__domain_organization AS SELECT id, domain_id, organization_id, created_at, updated_at FROM domain_organization');
            DB::statement('DROP TABLE domain_organization');
            DB::statement('CREATE TABLE domain_organization (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, domain_id INTEGER UNSIGNED NOT NULL, organization_id INTEGER UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_58E115F1115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_58E115F132C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            DB::statement('INSERT INTO domain_organization (id, domain_id, organization_id, created_at, updated_at) SELECT id, domain_id, organization_id, created_at, updated_at FROM __temp__domain_organization');
            DB::statement('DROP TABLE __temp__domain_organization');
            DB::statement('CREATE INDEX IDX_58E115F1115F0EE5 ON domain_organization (domain_id)');
            DB::statement('CREATE INDEX IDX_58E115F132C8A3DE ON domain_organization (organization_id)');
            DB::statement('DROP INDEX users_email_unique');
            DB::statement('CREATE TEMPORARY TABLE __temp__users AS SELECT id, first_name, last_name, email, email_verified, totp_secret, remember_token, vatsim_sso_data, vatsim_status_data, created_at, updated_at FROM users');
            DB::statement('DROP TABLE users');
            DB::statement('CREATE TABLE users (id INTEGER UNSIGNED NOT NULL, first_name VARCHAR(255) NOT NULL COLLATE BINARY, last_name VARCHAR(255) NOT NULL COLLATE BINARY, email VARCHAR(255) DEFAULT NULL COLLATE BINARY, email_verified BOOLEAN DEFAULT \'0\' NOT NULL, totp_secret VARCHAR(255) DEFAULT NULL COLLATE BINARY, remember_token VARCHAR(100) DEFAULT NULL, vatsim_sso_data CLOB DEFAULT NULL --(DC2Type:json)
        , vatsim_status_data CLOB DEFAULT NULL --(DC2Type:json)
        , created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, admin BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
            DB::statement('INSERT INTO users (id, first_name, last_name, email, email_verified, totp_secret, remember_token, vatsim_sso_data, vatsim_status_data, created_at, updated_at) SELECT id, first_name, last_name, email, email_verified, totp_secret, remember_token, vatsim_sso_data, vatsim_status_data, created_at, updated_at FROM __temp__users');
            DB::statement('DROP TABLE __temp__users');
            DB::statement('CREATE TEMPORARY TABLE __temp__organization_prefix_applications AS SELECT id, organization_id, user_id, identity_url, prefix, created_at, updated_at, deleted_at FROM organization_prefix_applications');
            DB::statement('DROP TABLE organization_prefix_applications');
            DB::statement('CREATE TABLE organization_prefix_applications (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, organization_id INTEGER UNSIGNED NOT NULL, user_id INTEGER UNSIGNED NOT NULL, prefix VARCHAR(255) NOT NULL COLLATE BINARY, deleted_at DATETIME DEFAULT NULL, identity_url VARCHAR(1000) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_54CE7FE632C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_54CE7FE6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            DB::statement('INSERT INTO organization_prefix_applications (id, organization_id, user_id, identity_url, prefix, created_at, updated_at, deleted_at) SELECT id, organization_id, user_id, identity_url, prefix, created_at, updated_at, deleted_at FROM __temp__organization_prefix_applications');
            DB::statement('DROP TABLE __temp__organization_prefix_applications');
            DB::statement('CREATE INDEX IDX_54CE7FE632C8A3DE ON organization_prefix_applications (organization_id)');
            DB::statement('CREATE INDEX IDX_54CE7FE6A76ED395 ON organization_prefix_applications (user_id)');
            DB::statement('CREATE TEMPORARY TABLE __temp__organization_user AS SELECT id, organization_id, user_id, role_id, created_at, updated_at, deleted_at FROM organization_user');
            DB::statement('DROP TABLE organization_user');
            DB::statement('CREATE TABLE organization_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, organization_id INTEGER UNSIGNED NOT NULL, user_id INTEGER UNSIGNED NOT NULL, deleted_at DATETIME DEFAULT NULL, role_id SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_B49AE8D432C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B49AE8D4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            DB::statement('INSERT INTO organization_user (id, organization_id, user_id, role_id, created_at, updated_at, deleted_at) SELECT id, organization_id, user_id, role_id, created_at, updated_at, deleted_at FROM __temp__organization_user');
            DB::statement('DROP TABLE __temp__organization_user');
            DB::statement('CREATE INDEX IDX_B49AE8D432C8A3DE ON organization_user (organization_id)');
            DB::statement('CREATE INDEX IDX_B49AE8D4A76ED395 ON organization_user (user_id)');
            DB::statement('DROP INDEX urls_url_index');
            DB::statement('CREATE TEMPORARY TABLE __temp__urls AS SELECT id, user_id, domain_id, url, redirect_url, created_at, updated_at, deleted_at, organization_id, prefix, analytics_disabled FROM urls');
            DB::statement('DROP TABLE urls');
            DB::statement('CREATE TABLE urls (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER UNSIGNED DEFAULT NULL, domain_id INTEGER UNSIGNED NOT NULL, organization_id INTEGER UNSIGNED DEFAULT NULL, url VARCHAR(255) NOT NULL COLLATE BINARY, deleted_at DATETIME DEFAULT NULL, prefix BOOLEAN DEFAULT \'0\' NOT NULL, analytics_disabled BOOLEAN DEFAULT \'0\' NOT NULL, redirect_url VARCHAR(1000) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_2A9437A132C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2A9437A1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2A9437A1115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            DB::statement('INSERT INTO urls (id, user_id, domain_id, url, redirect_url, created_at, updated_at, deleted_at, organization_id, prefix, analytics_disabled) SELECT id, user_id, domain_id, url, redirect_url, created_at, updated_at, deleted_at, organization_id, prefix, analytics_disabled FROM __temp__urls');
            DB::statement('DROP TABLE __temp__urls');
            DB::statement('CREATE INDEX IDX_2A9437A132C8A3DE ON urls (organization_id)');
            DB::statement('CREATE INDEX IDX_2A9437A1A76ED395 ON urls (user_id)');
            DB::statement('CREATE INDEX IDX_2A9437A1115F0EE5 ON urls (domain_id)');
            DB::statement('CREATE TEMPORARY TABLE __temp__email_events AS SELECT id, broker, message_id, name, recipient, data, triggered_at FROM email_events');
            DB::statement('DROP TABLE email_events');
            DB::statement('CREATE TABLE email_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, broker VARCHAR(255) NOT NULL COLLATE BINARY, message_id VARCHAR(255) DEFAULT NULL COLLATE BINARY, name VARCHAR(255) NOT NULL COLLATE BINARY, recipient VARCHAR(255) NOT NULL COLLATE BINARY, triggered_at DATETIME NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        )');
            DB::statement('INSERT INTO email_events (id, broker, message_id, name, recipient, data, triggered_at) SELECT id, broker, message_id, name, recipient, data, triggered_at FROM __temp__email_events');
            DB::statement('DROP TABLE __temp__email_events');
            DB::statement('CREATE TEMPORARY TABLE __temp__organizations AS SELECT id, name, created_at, updated_at, deleted_at, prefix FROM organizations');
            DB::statement('DROP TABLE organizations');
            DB::statement('CREATE TABLE organizations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, deleted_at DATETIME DEFAULT NULL, prefix VARCHAR(255) DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
            DB::statement('INSERT INTO organizations (id, name, created_at, updated_at, deleted_at, prefix) SELECT id, name, created_at, updated_at, deleted_at, prefix FROM __temp__organizations');
            DB::statement('DROP TABLE __temp__organizations');
        } else {
            throw new \Exception("Migration can only be executed safely on mysql or sqlite, current connection is {DB::connection()->getName()}.");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::connection()->getName() == 'mysql') {
            DB::statement('ALTER TABLE domain_organization DROP FOREIGN KEY FK_58E115F1115F0EE5');
            DB::statement('ALTER TABLE domain_organization DROP FOREIGN KEY FK_58E115F132C8A3DE');
            DB::statement('DROP INDEX IDX_58E115F1115F0EE5 ON domain_organization');
            DB::statement('DROP INDEX IDX_58E115F132C8A3DE ON domain_organization');
            DB::statement('ALTER TABLE domain_organization CHANGE domain_id domain_id BIGINT UNSIGNED NOT NULL, CHANGE organization_id organization_id BIGINT UNSIGNED NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('ALTER TABLE email_events CHANGE data data TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE triggered_at triggered_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
            DB::statement('ALTER TABLE email_verifications DROP FOREIGN KEY FK_7EB1F4EEA76ED395');
            DB::statement('ALTER TABLE email_verifications CHANGE created_at created_at DATETIME DEFAULT NULL');
            DB::statement('DROP INDEX idx_7eb1f4eea76ed395 ON email_verifications');
            DB::statement('CREATE INDEX email_verifications_user_id_foreign ON email_verifications (user_id)');
            DB::statement('ALTER TABLE email_verifications ADD CONSTRAINT FK_7EB1F4EEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE news CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('ALTER TABLE organization_prefix_applications DROP FOREIGN KEY FK_54CE7FE632C8A3DE');
            DB::statement('ALTER TABLE organization_prefix_applications DROP FOREIGN KEY FK_54CE7FE6A76ED395');
            DB::statement('ALTER TABLE organization_prefix_applications CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('DROP INDEX idx_54ce7fe6a76ed395 ON organization_prefix_applications');
            DB::statement('CREATE INDEX organization_prefix_applications_user_id_foreign ON organization_prefix_applications (user_id)');
            DB::statement('DROP INDEX idx_54ce7fe632c8a3de ON organization_prefix_applications');
            DB::statement('CREATE INDEX organization_prefix_applications_organization_id_foreign ON organization_prefix_applications (organization_id)');
            DB::statement('ALTER TABLE organization_prefix_applications ADD CONSTRAINT FK_54CE7FE632C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id)');
            DB::statement('ALTER TABLE organization_prefix_applications ADD CONSTRAINT FK_54CE7FE6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE organization_user DROP FOREIGN KEY FK_B49AE8D432C8A3DE');
            DB::statement('ALTER TABLE organization_user DROP FOREIGN KEY FK_B49AE8D4A76ED395');
            DB::statement('ALTER TABLE organization_user CHANGE role_id role_id TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('DROP INDEX idx_b49ae8d4a76ed395 ON organization_user');
            DB::statement('CREATE INDEX organization_user_user_id_foreign ON organization_user (user_id)');
            DB::statement('DROP INDEX idx_b49ae8d432c8a3de ON organization_user');
            DB::statement('CREATE INDEX organization_user_organization_id_foreign ON organization_user (organization_id)');
            DB::statement('ALTER TABLE organization_user ADD CONSTRAINT FK_B49AE8D432C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id)');
            DB::statement('ALTER TABLE organization_user ADD CONSTRAINT FK_B49AE8D4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE organizations CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('ALTER TABLE revisions DROP FOREIGN KEY FK_89B12285A76ED395');
            DB::statement('ALTER TABLE revisions CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('CREATE INDEX revisions_model_type_model_id_index ON revisions (model_type, model_id)');
            DB::statement('DROP INDEX idx_89b12285a76ed395 ON revisions');
            DB::statement('CREATE INDEX revisions_user_id_foreign ON revisions (user_id)');
            DB::statement('ALTER TABLE revisions ADD CONSTRAINT FK_89B12285A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE url_analytics DROP FOREIGN KEY FK_7EC4BD62A76ED395');
            DB::statement('ALTER TABLE url_analytics DROP FOREIGN KEY FK_7EC4BD6281CFDAE7');
            DB::statement('ALTER TABLE url_analytics CHANGE get_data get_data TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE custom_headers custom_headers TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('DROP INDEX idx_7ec4bd6281cfdae7 ON url_analytics');
            DB::statement('CREATE INDEX url_analytics_url_id_foreign ON url_analytics (url_id)');
            DB::statement('DROP INDEX idx_7ec4bd62a76ed395 ON url_analytics');
            DB::statement('CREATE INDEX url_analytics_user_id_foreign ON url_analytics (user_id)');
            DB::statement('ALTER TABLE url_analytics ADD CONSTRAINT FK_7EC4BD62A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE url_analytics ADD CONSTRAINT FK_7EC4BD6281CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id)');
            DB::statement('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A132C8A3DE');
            DB::statement('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A1A76ED395');
            DB::statement('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A1115F0EE5');
            DB::statement('ALTER TABLE urls CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('CREATE INDEX urls_url_index ON urls (url)');
            DB::statement('DROP INDEX idx_2a9437a1115f0ee5 ON urls');
            DB::statement('CREATE INDEX urls_domain_id_foreign ON urls (domain_id)');
            DB::statement('DROP INDEX idx_2a9437a1a76ed395 ON urls');
            DB::statement('CREATE INDEX urls_user_id_foreign ON urls (user_id)');
            DB::statement('DROP INDEX idx_2a9437a132c8a3de ON urls');
            DB::statement('CREATE INDEX urls_organization_id_foreign ON urls (organization_id)');
            DB::statement('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A132C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id)');
            DB::statement('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
            DB::statement('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A1115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id)');
            DB::statement('ALTER TABLE users DROP admin, CHANGE vatsim_sso_data vatsim_sso_data LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE vatsim_status_data vatsim_status_data LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
            DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        } elseif (DB::connection()->getName() == 'sqlite') {
            DB::statement('DROP INDEX IDX_58E115F1115F0EE5');
            DB::statement('DROP INDEX IDX_58E115F132C8A3DE');
            DB::statement('CREATE TEMPORARY TABLE __temp__domain_organization AS SELECT id, domain_id, organization_id, created_at, updated_at FROM domain_organization');
            DB::statement('DROP TABLE domain_organization');
            DB::statement('CREATE TABLE domain_organization (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, domain_id INTEGER NOT NULL, organization_id INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO domain_organization (id, domain_id, organization_id, created_at, updated_at) SELECT id, domain_id, organization_id, created_at, updated_at FROM __temp__domain_organization');
            DB::statement('DROP TABLE __temp__domain_organization');
            DB::statement('CREATE TEMPORARY TABLE __temp__email_events AS SELECT id, broker, message_id, name, recipient, data, triggered_at FROM email_events');
            DB::statement('DROP TABLE email_events');
            DB::statement('CREATE TABLE email_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, broker VARCHAR(255) NOT NULL, message_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, recipient VARCHAR(255) NOT NULL, triggered_at DATETIME NOT NULL, data CLOB NOT NULL COLLATE BINARY)');
            DB::statement('INSERT INTO email_events (id, broker, message_id, name, recipient, data, triggered_at) SELECT id, broker, message_id, name, recipient, data, triggered_at FROM __temp__email_events');
            DB::statement('DROP TABLE __temp__email_events');
            DB::statement('DROP INDEX IDX_7EB1F4EEA76ED395');
            DB::statement('CREATE TEMPORARY TABLE __temp__email_verifications AS SELECT id, user_id, token, created_at FROM email_verifications');
            DB::statement('DROP TABLE email_verifications');
            DB::statement('CREATE TABLE email_verifications (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, token VARCHAR(255) NOT NULL, user_id INTEGER NOT NULL, created_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO email_verifications (id, user_id, token, created_at) SELECT id, user_id, token, created_at FROM __temp__email_verifications');
            DB::statement('DROP TABLE __temp__email_verifications');
            DB::statement('CREATE TEMPORARY TABLE __temp__news AS SELECT id, title, content, published, created_at, updated_at FROM news');
            DB::statement('DROP TABLE news');
            DB::statement('CREATE TABLE news (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, published BOOLEAN DEFAULT \'0\' NOT NULL, title VARCHAR(255) NOT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO news (id, title, content, published, created_at, updated_at) SELECT id, title, content, published, created_at, updated_at FROM __temp__news');
            DB::statement('DROP TABLE __temp__news');
            DB::statement('DROP INDEX IDX_54CE7FE632C8A3DE');
            DB::statement('DROP INDEX IDX_54CE7FE6A76ED395');
            DB::statement('CREATE TEMPORARY TABLE __temp__organization_prefix_applications AS SELECT id, organization_id, user_id, identity_url, prefix, created_at, updated_at, deleted_at FROM organization_prefix_applications');
            DB::statement('DROP TABLE organization_prefix_applications');
            DB::statement('CREATE TABLE organization_prefix_applications (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, prefix VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, organization_id INTEGER NOT NULL, user_id INTEGER NOT NULL, identity_url VARCHAR(255) NOT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO organization_prefix_applications (id, organization_id, user_id, identity_url, prefix, created_at, updated_at, deleted_at) SELECT id, organization_id, user_id, identity_url, prefix, created_at, updated_at, deleted_at FROM __temp__organization_prefix_applications');
            DB::statement('DROP TABLE __temp__organization_prefix_applications');
            DB::statement('DROP INDEX IDX_B49AE8D432C8A3DE');
            DB::statement('DROP INDEX IDX_B49AE8D4A76ED395');
            DB::statement('CREATE TEMPORARY TABLE __temp__organization_user AS SELECT id, organization_id, user_id, role_id, created_at, updated_at, deleted_at FROM organization_user');
            DB::statement('DROP TABLE organization_user');
            DB::statement('CREATE TABLE organization_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, deleted_at DATETIME DEFAULT NULL, organization_id INTEGER NOT NULL, user_id INTEGER NOT NULL, role_id INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO organization_user (id, organization_id, user_id, role_id, created_at, updated_at, deleted_at) SELECT id, organization_id, user_id, role_id, created_at, updated_at, deleted_at FROM __temp__organization_user');
            DB::statement('DROP TABLE __temp__organization_user');
            DB::statement('CREATE TEMPORARY TABLE __temp__organizations AS SELECT id, name, prefix, created_at, updated_at, deleted_at FROM organizations');
            DB::statement('DROP TABLE organizations');
            DB::statement('CREATE TABLE organizations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, prefix VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO organizations (id, name, prefix, created_at, updated_at, deleted_at) SELECT id, name, prefix, created_at, updated_at, deleted_at FROM __temp__organizations');
            DB::statement('DROP TABLE __temp__organizations');
            DB::statement('DROP INDEX IDX_89B12285A76ED395');
            DB::statement('CREATE TEMPORARY TABLE __temp__revisions AS SELECT id, user_id, model_type, model_id, property_name, old_value, new_value, created_at, updated_at FROM revisions');
            DB::statement('DROP TABLE revisions');
            DB::statement('CREATE TABLE revisions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, model_type VARCHAR(255) NOT NULL, property_name VARCHAR(255) NOT NULL, old_value CLOB DEFAULT NULL, new_value CLOB DEFAULT NULL, user_id INTEGER DEFAULT NULL, model_id INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO revisions (id, user_id, model_type, model_id, property_name, old_value, new_value, created_at, updated_at) SELECT id, user_id, model_type, model_id, property_name, old_value, new_value, created_at, updated_at FROM __temp__revisions');
            DB::statement('DROP TABLE __temp__revisions');
            DB::statement('CREATE INDEX revisions_model_type_model_id_index ON revisions (model_type, model_id)');
            DB::statement('DROP INDEX IDX_7EC4BD62A76ED395');
            DB::statement('DROP INDEX IDX_7EC4BD6281CFDAE7');
            DB::statement('CREATE TEMPORARY TABLE __temp__url_analytics AS SELECT id, user_id, url_id, request_time, http_host, http_referer, http_user_agent, remote_addr, request_uri, get_data, custom_headers, response_code, created_at, updated_at FROM url_analytics');
            DB::statement('DROP TABLE url_analytics');
            DB::statement('CREATE TABLE url_analytics (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, request_time VARCHAR(255) DEFAULT NULL, http_host CLOB DEFAULT NULL, http_referer CLOB DEFAULT NULL, http_user_agent CLOB DEFAULT NULL, remote_addr VARCHAR(255) DEFAULT NULL, request_uri VARCHAR(255) DEFAULT NULL, response_code INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, url_id INTEGER DEFAULT NULL, get_data CLOB DEFAULT NULL COLLATE BINARY, custom_headers CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO url_analytics (id, user_id, url_id, request_time, http_host, http_referer, http_user_agent, remote_addr, request_uri, get_data, custom_headers, response_code, created_at, updated_at) SELECT id, user_id, url_id, request_time, http_host, http_referer, http_user_agent, remote_addr, request_uri, get_data, custom_headers, response_code, created_at, updated_at FROM __temp__url_analytics');
            DB::statement('DROP TABLE __temp__url_analytics');
            DB::statement('DROP INDEX IDX_2A9437A132C8A3DE');
            DB::statement('DROP INDEX IDX_2A9437A1A76ED395');
            DB::statement('DROP INDEX IDX_2A9437A1115F0EE5');
            DB::statement('CREATE TEMPORARY TABLE __temp__urls AS SELECT id, organization_id, user_id, domain_id, prefix, url, redirect_url, analytics_disabled, created_at, updated_at, deleted_at FROM urls');
            DB::statement('DROP TABLE urls');
            DB::statement('CREATE TABLE urls (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, prefix BOOLEAN DEFAULT \'0\' NOT NULL, url VARCHAR(255) NOT NULL, analytics_disabled BOOLEAN DEFAULT \'0\' NOT NULL, deleted_at DATETIME DEFAULT NULL, organization_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, domain_id INTEGER NOT NULL, redirect_url VARCHAR(255) NOT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
            DB::statement('INSERT INTO urls (id, organization_id, user_id, domain_id, prefix, url, redirect_url, analytics_disabled, created_at, updated_at, deleted_at) SELECT id, organization_id, user_id, domain_id, prefix, url, redirect_url, analytics_disabled, created_at, updated_at, deleted_at FROM __temp__urls');
            DB::statement('DROP TABLE __temp__urls');
            DB::statement('CREATE INDEX urls_url_index ON urls (url)');
            DB::statement('CREATE TEMPORARY TABLE __temp__users AS SELECT id, first_name, last_name, email, email_verified, totp_secret, remember_token, vatsim_sso_data, vatsim_status_data, created_at, updated_at FROM users');
            DB::statement('DROP TABLE users');
            DB::statement('CREATE TABLE users (id INTEGER UNSIGNED NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, email_verified BOOLEAN DEFAULT \'0\' NOT NULL, totp_secret VARCHAR(255) DEFAULT NULL, remember_token VARCHAR(255) DEFAULT NULL COLLATE BINARY, vatsim_sso_data CLOB DEFAULT NULL COLLATE BINARY, vatsim_status_data CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
            DB::statement('INSERT INTO users (id, first_name, last_name, email, email_verified, totp_secret, remember_token, vatsim_sso_data, vatsim_status_data, created_at, updated_at) SELECT id, first_name, last_name, email, email_verified, totp_secret, remember_token, vatsim_sso_data, vatsim_status_data, created_at, updated_at FROM __temp__users');
            DB::statement('DROP TABLE __temp__users');
            DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        } else {
            throw new \Exception("Migration can only be executed safely on mysql or sqlite, current connection is {DB::connection()->getName()}.");
        }
    }
}

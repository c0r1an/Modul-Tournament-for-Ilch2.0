<?php

namespace Modules\Tournament\Config;

use Ilch\Config\Install;

class Config extends Install
{
    public $config = [
        'key' => 'tournament',
        'version' => '1.0.0',
        'icon_small' => 'fa-solid fa-trophy',
        'author' => 'c0rian',
        'link' => 'https://github.com/c0r1an',
        'languages' => [
            'de_DE' => [
                'name' => 'Tournament',
                'description' => 'Turnierverwaltung mit Single Elimination, Reporting und Disputes.',
            ],
            'en_EN' => [
                'name' => 'Tournament',
                'description' => 'Tournament management with single elimination, reporting and disputes.',
            ],
        ],
        'boxes' => [
            'nextmatches' => [
                'de_DE' => [
                    'name' => 'Nächste Matches'
                ],
                'en_EN' => [
                    'name' => 'Upcoming Matches'
                ]
            ],
            'runningtournaments' => [
                'de_DE' => [
                    'name' => 'Laufende Turniere'
                ],
                'en_EN' => [
                    'name' => 'Running Tournaments'
                ]
            ]
        ],
        'ilchCore' => '2.2.0',
        'phpVersion' => '7.3',
        'folderRights' => [
            'storage'
        ]
    ];

    public function install()
    {
        $this->db()->queryMulti($this->getInstallSql());
    }

    public function uninstall()
    {
        $this->db()->drop('tournament_audit_log', true);
        $this->db()->drop('tournament_match_disputes', true);
        $this->db()->drop('tournament_match_evidence', true);
        $this->db()->drop('tournament_match_reports', true);
        $this->db()->drop('tournament_matches', true);
        $this->db()->drop('tournament_tournament_teams', true);
        $this->db()->drop('tournament_member_profiles', true);
        $this->db()->drop('tournament_team_members', true);
        $this->db()->drop('tournament_teams', true);
        $this->db()->drop('tournament_tournaments', true);
    }

    public function getInstallSql(): string
    {
        return 'CREATE TABLE IF NOT EXISTS `[prefix]_tournament_tournaments` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NULL DEFAULT NULL,
            `banner` VARCHAR(255) NULL DEFAULT NULL,
            `game` VARCHAR(255) NOT NULL,
            `mode` VARCHAR(50) NOT NULL DEFAULT "single_elimination",
            `team_size` INT(11) NOT NULL,
            `max_teams` INT(11) NOT NULL,
            `start_at` DATETIME NOT NULL,
            `checkin_required` TINYINT(1) NOT NULL DEFAULT 0,
            `rules` MEDIUMTEXT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT "draft",
            `created_by` INT(11) NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_tournament_tournaments_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_teams` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `tag` VARCHAR(32) NULL DEFAULT NULL,
            `logo` VARCHAR(255) NULL DEFAULT NULL,
            `captain_user_id` INT(11) NOT NULL,
            `contact_discord` VARCHAR(255) NULL DEFAULT NULL,
            `contact_email` VARCHAR(255) NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_tournament_teams_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_team_members` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `team_id` INT(11) NOT NULL,
            `user_id` INT(11) NULL DEFAULT NULL,
            `nickname` VARCHAR(255) NULL DEFAULT NULL,
            `role` VARCHAR(20) NOT NULL DEFAULT "member",
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_tournament_team_members_team_id` (`team_id`),
            CONSTRAINT `fk_tournament_team_members_team` FOREIGN KEY (`team_id`) REFERENCES `[prefix]_tournament_teams` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_member_profiles` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `team_member_id` INT(11) NOT NULL,
            `full_name` VARCHAR(255) NULL DEFAULT NULL,
            `nickname` VARCHAR(255) NULL DEFAULT NULL,
            `age` INT(11) NULL DEFAULT NULL,
            `gender` VARCHAR(32) NULL DEFAULT NULL,
            `social_links` MEDIUMTEXT NULL,
            `bio` MEDIUMTEXT NULL,
            `games` MEDIUMTEXT NULL,
            `homepage` VARCHAR(500) NULL DEFAULT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_tournament_member_profiles_member` (`team_member_id`),
            CONSTRAINT `fk_tournament_member_profiles_member` FOREIGN KEY (`team_member_id`) REFERENCES `[prefix]_tournament_team_members` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_tournament_teams` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `tournament_id` INT(11) NOT NULL,
            `team_id` INT(11) NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT "accepted",
            `seed` INT(11) NULL DEFAULT NULL,
            `registered_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_tournament_team_registration` (`tournament_id`, `team_id`),
            INDEX `idx_tournament_tournament_teams_status` (`status`),
            CONSTRAINT `fk_tournament_tournament_teams_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `[prefix]_tournament_tournaments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
            CONSTRAINT `fk_tournament_tournament_teams_team` FOREIGN KEY (`team_id`) REFERENCES `[prefix]_tournament_teams` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_matches` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `tournament_id` INT(11) NOT NULL,
            `round` INT(11) NOT NULL,
            `match_no` INT(11) NOT NULL,
            `team1_id` INT(11) NULL DEFAULT NULL,
            `team2_id` INT(11) NULL DEFAULT NULL,
            `winner_team_id` INT(11) NULL DEFAULT NULL,
            `score1` INT(11) NULL DEFAULT NULL,
            `score2` INT(11) NULL DEFAULT NULL,
            `best_of` INT(11) NOT NULL DEFAULT 1,
            `map` VARCHAR(255) NULL DEFAULT NULL,
            `scheduled_at` DATETIME NULL DEFAULT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT "pending",
            `next_match_id` INT(11) NULL DEFAULT NULL,
            `next_match_slot` VARCHAR(10) NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_tournament_round_match` (`tournament_id`, `round`, `match_no`),
            INDEX `idx_tournament_matches_tournament` (`tournament_id`),
            CONSTRAINT `fk_tournament_matches_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `[prefix]_tournament_tournaments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
            CONSTRAINT `fk_tournament_matches_next` FOREIGN KEY (`next_match_id`) REFERENCES `[prefix]_tournament_matches` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_match_reports` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `match_id` INT(11) NOT NULL,
            `reported_by_team_id` INT(11) NOT NULL,
            `score1` INT(11) NOT NULL,
            `score2` INT(11) NOT NULL,
            `winner_team_id` INT(11) NOT NULL,
            `comment` MEDIUMTEXT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_tournament_match_reports_match` (`match_id`),
            CONSTRAINT `fk_tournament_match_reports_match` FOREIGN KEY (`match_id`) REFERENCES `[prefix]_tournament_matches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_match_evidence` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `match_report_id` INT(11) NOT NULL,
            `type` VARCHAR(20) NOT NULL,
            `path_or_url` VARCHAR(500) NOT NULL,
            `note` VARCHAR(255) NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_tournament_match_evidence_report` (`match_report_id`),
            CONSTRAINT `fk_tournament_match_evidence_report` FOREIGN KEY (`match_report_id`) REFERENCES `[prefix]_tournament_match_reports` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_match_disputes` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `match_id` INT(11) NOT NULL,
            `opened_by_team_id` INT(11) NOT NULL,
            `reason` MEDIUMTEXT NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT "open",
            `resolved_by_user_id` INT(11) NULL DEFAULT NULL,
            `resolution_note` MEDIUMTEXT NULL,
            `resolved_at` DATETIME NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_tournament_match_disputes_match` (`match_id`),
            INDEX `idx_tournament_match_disputes_status` (`status`),
            CONSTRAINT `fk_tournament_match_disputes_match` FOREIGN KEY (`match_id`) REFERENCES `[prefix]_tournament_matches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_tournament_audit_log` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `entity` VARCHAR(50) NOT NULL,
            `entity_id` INT(11) NOT NULL,
            `action` VARCHAR(100) NOT NULL,
            `data_json` MEDIUMTEXT NULL,
            `user_id` INT(11) NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_tournament_audit_log_entity` (`entity`, `entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
    }

    public function getUpdate(string $installedVersion): string
    {
        switch ($installedVersion) {
            case "1.0.0":
                $this->db()->query('ALTER TABLE `[prefix]_tournament_tournaments` ADD COLUMN `banner` VARCHAR(255) NULL DEFAULT NULL AFTER `slug`;');
                $this->db()->query('CREATE TABLE IF NOT EXISTS `[prefix]_tournament_member_profiles` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `team_member_id` INT(11) NOT NULL,
                    `full_name` VARCHAR(255) NULL DEFAULT NULL,
                    `nickname` VARCHAR(255) NULL DEFAULT NULL,
                    `age` INT(11) NULL DEFAULT NULL,
                    `gender` VARCHAR(32) NULL DEFAULT NULL,
                    `social_links` MEDIUMTEXT NULL,
                    `bio` MEDIUMTEXT NULL,
                    `games` MEDIUMTEXT NULL,
                    `homepage` VARCHAR(500) NULL DEFAULT NULL,
                    `updated_at` DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_tournament_member_profiles_member` (`team_member_id`),
                    CONSTRAINT `fk_tournament_member_profiles_member` FOREIGN KEY (`team_member_id`) REFERENCES `[prefix]_tournament_team_members` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
                // no break
        }

        return '"' . $this->config['key'] . '" Update-function executed.';
    }
}

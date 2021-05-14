<?php

require_once 'init.php';
if (in_array($user->data()->id, $master_account)) {
    $db = DB::getInstance();
    include 'plugin_info.php';

    $check = $db->query('SELECT * FROM us_plugins WHERE plugin = ?', [$plugin_name])->count();
    if ($check > 0) {
        err($plugin_name.' has already been installed!');
    } else {
        $fields = [
            'plugin' => $plugin_name,
            'status' => 'installed',
        ];
        $db->insert('us_plugins', $fields);
        if (!$db->error()) {
            err($plugin_name.' installed');
            logger($user->data()->id, 'USPlugins', $plugin_name.' installed');
        } else {
            err($plugin_name.' was not installed');
            logger($user->data()->id, 'USPlugins', 'Failed to to install plugin, Error: '.$db->errorString());
        }
    }

    $queries = [
      [
        'Description' => 'Create coupons table',
        'SQL' => 'CREATE TABLE coupons( kCouponID int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, Coupon varchar(255) NOT NULL, CouponType varchar(255) NULL, CouponGeneratedByUserId int(11) UNSIGNED NOT NULL, CouponGeneratedDate datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(), CouponUseLimit int(11) UNSIGNED NULL, CouponExpirationDate datetime NULL)',
      ],
      [
        'Description' => 'Create coupons_history table',
        'SQL' => 'CREATE TABLE coupons_history( kCouponHistoryID int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, fkCouponID int(11) UNSIGNED NOT NULL, fkUserID int(11) UNSIGNED NOT NULL, CouponHistoryDate datetime NOT NULL DEFAULT CURRENT_TIMESTAMP())',
      ],
      [
        'Description' => 'Create coupons_permissions table',
        'SQL' => 'CREATE TABLE coupons_permissions( kCouponPermissionID int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, fkCouponID int(11) UNSIGNED NOT NULL, fkPermissionID int(11) UNSIGNED NOT NULL)',
      ],
    ];

    foreach ($queries as $query) {
        $db->query($query['SQL']);
        if (!$db->error()) {
            logger($user->data()->id, 'USPlugins', "[Coupons] [SUCCESS] {$query['Description']}");
        } else {
            logger($user->data()->id, 'USPlugins', "[Coupons] [WARNING] [Database Error] {$query['Description']}", json_encode(['ERROR' => $db->errorString()]));
        }
    }

    $hooks = [];
    registerHooks($hooks, $plugin_name);
}

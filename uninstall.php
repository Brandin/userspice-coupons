<?php

require_once 'init.php';
if (in_array($user->data()->id, $master_account)) {
    $db = DB::getInstance();
    include 'plugin_info.php';

    $db->query('DELETE FROM us_plugins WHERE plugin = ?', [$plugin_name]);
    deRegisterHooks($plugin_name);
    if (!$db->error()) {
        err($plugin_name.' uninstalled');
        logger($user->data()->id, 'USPlugins', $plugin_name.' uninstalled');
    } else {
        err($plugin_name.' was not uninstalled');
        logger($user->data()->id, 'USPlugins', 'Failed to uninstall Plugin, Error: '.$db->errorString());
    }
}

$queries = [
  [
    'Description' => 'Delete coupons table',
    'SQL' => 'DROP TABLE coupons',
  ],
  [
    'Description' => 'Delete coupons_history table',
    'SQL' => 'DROP TABLE coupons_history',
  ],
  [
    'Description' => 'Delete coupons_permissions table',
    'SQL' => 'DROP TABLE coupons_permissions',
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

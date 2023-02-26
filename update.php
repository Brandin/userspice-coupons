<?php

require_once 'init.php';
if (in_array($user->data()->id, $master_account)) {
    $count = 0;
    $db = DB::getInstance();
    include 'plugin_info.php';
    pluginActive($plugin_name);

    $checkQ = $db->query('SELECT id,updates FROM us_plugins WHERE plugin = ?', [$plugin_name]);
    $checkC = $checkQ->count();
    if ($checkC < 1) {
        err($plugin_name.' is not installed');
        exit;
    }
    $check = $checkQ->first();
    if ($check->updates == '') {
        $existing = [];
    } else {
        $existing = json_decode($check->updates);
    }

    $update = '2023-02-25a';
    if (!in_array($update, $existing)) {
        $db->query('SELECT * FROM coupons_required_permissions');
        if ($db->error()) {
            $db->query('CREATE TABLE coupons_required_permissions( kCouponRequiredPermissionID int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, fkCouponID int(11) UNSIGNED NOT NULL, fkPermissionID int(11) UNSIGNED NOT NULL)');
            if (!$db->error()) {
                logger($user->data()->id, 'USPlugins', 'Applied Update', ['PLUGIN' => $plugin_name, 'UPDATE' => $update]);
                $existing[] = $update;
                ++$count;
            } else {
                logger($user->data()->id, 'USPlugins', 'Failed to apply update', ['PLUGIN' => $plugin_name, 'UPDATE' => $update, 'ERROR' => $db->errorString()]);
            }
        } else {
            logger($user->data()->id, 'USPlugins', 'Skipping Update', ['PLUGIN' => $plugin_name, 'UPDATE' => $update]);
            $existing[] = $update;
            ++$count;
        }
    }

    $new = json_encode($existing);
    $db->update('us_plugins', $check->id, ['updates' => $new]);
    if (!$db->error()) {
        if ($count == 1) {
        } else {
            err($count.' updates applied');
        }
    } else {
        err('Failed to save updates');
        logger($user->data()->id, 'USPlugins', 'Failed to save updates', ['ERROR' => $db->errorString()]);
    }
}

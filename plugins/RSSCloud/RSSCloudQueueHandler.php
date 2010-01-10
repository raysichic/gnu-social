#!/usr/bin/env php
<?php
/*
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2008, 2009, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('INSTALLDIR', realpath(dirname(__FILE__) . '/../..'));

$shortoptions = 'i::';
$longoptions = array('id::');

$helptext = <<<END_OF_ENJIT_HELP
Daemon script for pushing new notices to RSSCloud subscribers.

    -i --id           Identity (default none)

END_OF_ENJIT_HELP;

require_once INSTALLDIR . '/scripts/commandline.inc';
require_once INSTALLDIR . '/lib/queuehandler.php';
require_once INSTALLDIR . '/plugins/RSSCloud/RSSCloudNotifier.php';
require_once INSTALLDIR . '/plugins/RSSCloud/RSSCloudSubscription.php';

class RSSCloudQueueHandler extends QueueHandler
{
    var $notifier = null;

    function transport()
    {
        return 'rsscloud';
    }

    function start()
    {
        $this->log(LOG_INFO, "INITIALIZE");
        $this->notifier = new RSSCloudNotifier();
        return true;
    }

    function handle_notice($notice)
    {
        $profile = $notice->getProfile();
        return $this->notifier->notify($profile);
    }

    function finish()
    {
    }

}

if (have_option('i')) {
    $id = get_option_value('i');
} else if (have_option('--id')) {
    $id = get_option_value('--id');
} else if (count($args) > 0) {
    $id = $args[0];
} else {
    $id = null;
}

$handler = new RSSCloudQueueHandler($id);

$handler->runOnce();
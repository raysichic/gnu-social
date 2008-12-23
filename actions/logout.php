<?php
/*
 * Laconica - a distributed open-source microblogging tool
 * Copyright (C) 2008, Controlez-Vous, Inc.
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

if (!defined('LACONICA')) { exit(1); }

require_once(INSTALLDIR.'/lib/openid.php');

class LogoutAction extends Action
{
    
    function is_readonly()
    {
        return true;
    }
    
    function handle($args)
    {
        parent::handle($args);
        if (!common_logged_in()) {
            common_user_error(_('Not logged in.'));
        } else {
            common_set_user(null);
            common_real_login(false); # not logged in
            common_forgetme(); # don't log back in!
            common_redirect(common_local_url('public'));
        }
    }
}

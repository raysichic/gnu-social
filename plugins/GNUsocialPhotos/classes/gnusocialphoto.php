<?php
/**
 * GNU Social
 * Copyright (C) 2010, Free Software Foundation, Inc.
 *
 * PHP version 5
 *
 * LICENCE:
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
 *
 * @category  Widget
 * @package   GNU Social
 * @author    Ian Denhardt <ian@zenhack.net>
 * @copyright 2010 Free Software Foundation, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 */

if (!defined('STATUSNET')) {
    exit(1);
}

require_once INSTALLDIR . '/classes/Memcached_DataObject.php';

class GNUsocialPhoto extends Memcached_DataObject
{
    public $__table = 'GNUsocialPhoto';
    public $notice_id;  // int(11)
    public $album_id;   // int(11)
    public $uri;        // varchar(512)
    public $thumb_uri;  // varchar(512)
    

    /**
     *
     * k key
     * v value
     */
    function staticGet($k,$v=NULL)
    {
        return Memcached_DataObject::staticGet('GNUsocialPhoto',$k,$v);
    }

/*    function delete()
    {
        if(!unlink(INSTALLDIR . $this->thumb_uri)) {
            return false;
        }
        if(!unlink(INSTALLDIR . $this->path)) {
            return false;
        }
        return parent::delete();
    } */

    function table()
    {
        return array('notice_id' => DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
                     'album_id' => DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
                     'uri' => DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
                     'thumb_uri' => DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL);
    }
    
    function keys()
    {
        return array_keys($this->keyTypes());
    }

    function keyTypes()
    {
        return array('notice_id' => 'K');
    }

    function sequenceKey()
    {
        return array(false, false, false);
    }

    function saveNew($profile_id, $album_id, $thumb_uri, $uri, $source)
    {
        $photo = new GNUsocialPhoto();
        $photo->thumb_uri = $thumb_uri;
        $photo->uri = $uri;
		$photo->album_id = $album_id;

        $notice = Notice::saveNew($profile_id, $uri, $source);
        $photo->notice_id = $notice->id;
        $photo_id = $photo->insert();
        if (!$photo_id) {
            common_log_db_error($photo, 'INSERT', __FILE__);
            throw new ServerException(_m('Problem Saving Photo.'));
        }
    }


    /*
     * TODO: -Sanitize input
     * @param int page_id The desired page of the gallery to show.
     * @param int album_id The id of the album to get photos from.
     * @param int gallery_size The number of thumbnails to show per page in the gallery.
     * @return array Array of GNUsocialPhotos for this gallery page.
     */
    function getGalleryPage($page_id, $album_id, $gallery_size)
    {
		$page_offset = ($page_id-1) * $gallery_size; 
        $sql = 'SELECT * FROM GNUsocialPhoto order by notice_id limit ' . $page_offset . ',' . $gallery_size; 
        $this->query($sql);
        $thumbs = array();

        while ($this->fetch()) {
            $thumbs[] = clone($this);
        }

        return $thumbs;
    }

    /*
    function asActivityNoun($element)
    {
        $object = new ActivityObject();

        $object->type = ActivityObject::PHOTO;
        $object->title = "";
        $object->thumbnail = 'http://' . common_config('site', 'server') . $this->thumb_uri;
        $object->largerImage = 'http://' . common_config('site', 'server') . $this->path;
        return $object;
    } */
}
-- install.mysql.utf8.sql
-- version 2.2.0 01/08/2013
-- author	Bernard saulme
-- package	com_myjspace

CREATE TABLE IF NOT EXISTS #__myjspace (
	id int(10) unsigned NOT NULL AUTO_INCREMENT,
	title varchar(100) NOT NULL DEFAULT '',
	pagename VARCHAR(100) NOT NULL,
	userid int(11) NOT NULL DEFAULT 0,
	modified_by int(10) unsigned NOT NULL DEFAULT '0',
    access int(10) unsigned NOT NULL DEFAULT '0',
	content MEDIUMTEXT NULL,
	blockEdit TINYINT NOT NULL DEFAULT 0,
	blockView int(10) unsigned NOT NULL DEFAULT 1,
	create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	last_update_date TIMESTAMP,
	last_access_date TIMESTAMP,
	last_access_ip VARCHAR(8) NOT NULL DEFAULT '0',
	hits BIGINT NOT NULL DEFAULT 0,
    publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	metakey text NOT NULL DEFAULT '',
	template VARCHAR(50) NOT NULL DEFAULT '',
	catid int(11) NOT NULL DEFAULT '0',
	language char(7) NOT NULL DEFAULT '*',
	PRIMARY KEY (id),
	UNIQUE idx_pagename (pagename),
	KEY idx_userid (userid),
	KEY idx_access (access)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

<?php

/*
 * Copyright (C) 2015 Nathan Crause <nathan at crause.name>
 *
 * This file is part of QOTD
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Claromentis\Qotd;

use ClaCache;
use TemplaterComponentTmpl;

/**
 * Templater component for outputting a single quote of the day
 *
 * @author Nathan Crause <nathan at crause.name>
 */
class Component extends TemplaterComponentTmpl {
	
	const OPT_TEMPLATE = 'template';
	
	const URL = 'http://feeds.feedburner.com/brainyquote/QUOTEBR';
	
	public static $DEFAULTS = [
		self::OPT_TEMPLATE	=> 'qotd/default.html'
	];

	public function Show($attributes) {
		if (key_exists('PURGE', $_GET)) {
			ClaCache::Delete('qotd');
		}
		
		$options = array_merge(self::$DEFAULTS, $attributes);
		
		if (!($quote = ClaCache::Get('qotd'))) {
			$quote = $this->retrieve();
			
			ClaCache::Set('qotd', $quote, 3600);
		}
		
		$args = [
			'quote.body'	=> $quote->body,
			'source.body'	=> $quote->source,
			'citation.cite'	=> $quote->citation
		];
		
		return $this->CallTemplater($options[self::OPT_TEMPLATE], $args);
	}
	
	private function retrieve() {
		$xml = simplexml_load_string($this->http(self::URL));
		$item = $xml->channel->item[0];
		
		return (object) [
			'body'		=> trim((string) $item->description, '"'),
			'source'	=> (string) $item->title,
			'citation'	=> (string) $item->link
		];
	}
	
	private function http($url) {
		$handle = curl_init($url);
		 
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE); 
		
		$result = curl_exec($handle);
		
		curl_close($handle);
		
		return $result;
	}

}

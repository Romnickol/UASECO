<?php
/*
 * Class: Plugin
 * ~~~~~~~~~~~~~
 * » Structure for all plugins, extend this class to build your own one.
 * » Based upon plugin.class.php from ASECO/2.2.0c
 *
 * ----------------------------------------------------------------------------------
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ----------------------------------------------------------------------------------
 *
 * Dependencies:
 *  - includes/core/dependence.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

abstract class Plugin extends BaseClass {
	private $author		= 'Unknown';
	private $coauthors	= array();
	private $contributors	= array();
	private $copyright	= null;
	private $version	= null;
	private $build		= null;
	private $filename	= 'Unknown';
	private $description	= 'No description';
	private $events		= array();
	private $chat_commands	= array();
	private $dependencies	= array();

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2017-05-31');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription('Structure for all plugins, extend this class to build your own one.');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setAuthor ($author) {
		$this->author = $author;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getAuthor () {
		return $this->author;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setCoAuthors ($coauthor) {
		$this->coauthors = array_unique(array_merge($this->coauthors, func_get_args()), SORT_STRING);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCoAuthors () {
		return $this->coauthors;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setContributors ($contributor) {
		$this->contributors = array_unique(array_merge($this->contributors, func_get_args()), SORT_STRING);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getContributors () {
		return $this->contributors;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setVersion ($version) {
		$this->version = $version;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getVersion () {
		return $this->version;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setBuild ($build) {
		$this->build = $build;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getBuild () {
		return $this->build;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setCopyright ($copyright) {
		$this->copyright = $copyright;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCopyright () {
		return $this->copyright;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setFilename ($filename) {
		$this->filename = $filename;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getFilename () {
		return $this->filename;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setDescription ($description) {
		$this->description = $description;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getDescription () {
		return $this->description;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getClassname () {
		return get_class($this);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getDependencies () {
		return $this->dependencies;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addDependence ($plugin, $permissions = Dependence::REQUIRED, $min_version = null, $max_version = null) {
		$this->dependencies[] = new Dependence($plugin, $permissions, $min_version, $max_version);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function registerEvent ($event, $callback_function) {
		$this->events[$event] = array($this, $callback_function);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getEvents () {
		return $this->events;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function registerChatCommand ($chat_command, $callback_function, $help, $rights = Player::PLAYERS, $params = array()) {
		$this->chat_commands[$chat_command] = array(
			'callback'	=> array($this, $callback_function),
			'help'		=> $help,
			'rights'	=> $rights,
			'params'	=> $params,
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getChatCommands () {
		return $this->chat_commands;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function storePlayerData ($player, $key, $data) {
		if (isset($player) && is_object($player) && $player instanceof Player && !empty($key)) {
			$player->data[$this->getClassname()][$key] = $data;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerData ($player, $key) {
		if (isset($player) && is_object($player) && $player instanceof Player && !empty($key) && isset($player->data[$this->getClassname()][$key])) {
			return $player->data[$this->getClassname()][$key];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function removePlayerData ($player, $key) {
		if (isset($player) && is_object($player) && $player instanceof Player && !empty($key) && isset($player->data[$this->getClassname()][$key])) {
			unset($player->data[$this->getClassname()][$key]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function existsPlayerData ($player, $key) {
		if (!empty($key) && isset($player->data[$this->getClassname()][$key])) {
			return true;
		}
		return false;
	}
}

?>
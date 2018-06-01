<?php
/*
 * Plugin: Chatlog
 * ~~~~~~~~~~~~~~~
 * » Keeps log of player chat, and displays the chat log.
 * » Based upon plugin.chatlog.php from XAseco2/1.03 written by Xymph
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
 */

	// Start the plugin
	$_PLUGIN = new PluginChatlog();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatlog extends Plugin {
	public $chat_history_buffer	= array();
	public $chat_history_length	= 30;
	public $max_line_length		= 40;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription(new Message('plugin.chat_log', 'plugin_description'));

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginWelcomeCenter',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onPlayerChat',		'onPlayerChat');

		$this->registerChatCommand('chatlog',		'chat_chatlog', new Message('plugin.chat_log', 'plugin_description'), Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {
		if (isset($aseco->plugins['PluginWelcomeCenter'])) {
			$aseco->plugins['PluginWelcomeCenter']->addInfoMessage(new Message('plugin.chat_log', 'info_message'));
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerChat ($aseco, $chat) {

		// check for non-empty player chat line, not a chat command
		if ($chat[2] !== '' && $chat[2]{0} !== '/') {
			// drop oldest chat line if buffer full
			if (count($this->chat_history_buffer) >= $this->chat_history_length) {
				array_shift($this->chat_history_buffer);
			}

			// append timestamp, player nickname (but strip wide font) & chat line to history
			if ($player = $aseco->server->players->getPlayerByLogin($chat[1])) {
				$this->chat_history_buffer[] = array(date('H:i:s'), str_ireplace('$w', '', $player->nickname), $chat[2]);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_chatlog ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		if (!empty($this->chat_history_buffer)) {
			$head = 'Recent chat history:';
			$msg = array();
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(1.2), array('Icons64x64_1', 'Outbox'));
			foreach ($this->chat_history_buffer as $item) {
				// break up long lines into chunks with continuation strings
				$multi = explode(LF, wordwrap($aseco->stripStyles($item[2]), $this->max_line_length + 30, LF . '...'));
				foreach ($multi as $line) {
					$line = substr($line, 0, $this->max_line_length + 33);  // chop off excessively long words
					$msg[] = array(
						'$z'. ($aseco->settings['chatpmlog_times'] ? '<{#server}' . $item[0] . '$z> ' : '') .
						'[{#black}'. $item[1] .'$z] '. $line
					);
					if (++$lines > 14) {
						$player->msgs[] = $msg;
						$lines = 0;
						$msg = array();
					}
				}
			}

			// add if last batch exists
			if (!empty($msg)) {
				$player->msgs[] = $msg;
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else {
			(new Message('plugin.chat_log', 'message_no_history'))->sendChatMessage($login);
		}
	}
}

?>

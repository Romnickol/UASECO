<?php
/*
 * Plugin: Chat Stats
 * ~~~~~~~~~~~~~~~~~~
 * » Displays player statistics and personal settings.
 * » Based upon chat.stats.php from XAseco2/1.03 written by Xymph and others
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
	$_PLUGIN = new PluginChatStats();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatStats extends Plugin {


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('askuri');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription(new Message('chat.stats', 'plugin_description'));

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginPanels',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginWelcomeCenter',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');

		$this->registerChatCommand('stats',	'chat_stats',		new Message('chat.stats', 'plugin_description'),	Player::PLAYERS);
		$this->registerChatCommand('settings',	'chat_settings',	new Message('chat.stats', 'plugin_description'),		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {
		if (isset($aseco->plugins['PluginWelcomeCenter'])) {
			$aseco->plugins['PluginWelcomeCenter']->addInfoMessage(new Message('chat.stats', 'info_message'));
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_stats ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$target = $player;

		// check for optional player parameter
		if (!empty($chat_parameter)) {
			if (!$target = $aseco->server->players->getPlayerParam($player, $chat_parameter, true)) {
				return;
			}
		}

		// Setup current player info
		$rank = $target->ladder_rank;
		$score = $target->ladder_score;
		$lastm = $target->last_match_score;
		$wins = $target->nb_wins;
		$draws = $target->nb_draws;
		$losses = $target->nb_losses;

		// get zone info
		$inscr = $target->zone_inscription;
		$inscrdays = floor($inscr / 24);
		$inscrhours = $inscr - ($inscrdays * 24);

		// format numbers with narrow spaces between the thousands
		$frank = str_replace(' ', '$n $m', number_format($rank, 0, ' ', ' '));
		$fwins = str_replace(' ', '$n $m', number_format($wins, 0, ' ', ' '));
		$fdraws = str_replace(' ', '$n $m', number_format($draws, 0, ' ', ' '));
		$flosses = str_replace(' ', '$n $m', number_format($losses, 0, ' ', ' '));

		// obtain last online timestamp
		$query = "
		SELECT
			`LastVisit`
		FROM `%prefix%players`
		WHERE `Login` = ". $aseco->db->quote($target->login) .";
		";

		$result = $aseco->db->query($query);
		$laston = $result->fetch_row();
		$result->free_result();

		$records = 0;
		if ($list = $target->getRecords()) {
			// sort for best records
			asort($list);

			// count total ranked records
			foreach ($list as $name => $rec) {
				// stop upon unranked record
				if ($rec > $aseco->plugins['PluginLocalRecords']->records->getMaxRecords()) {
					break;
				}

				// count ranked record
				$records++;
			}
		}

		$header = 'Stats for: ' . $target->nickname . '$z / {#login}' . $target->login;
		$stats = array();
		$stats[] = array('Server Date', '{#black}' . date('M d, Y'));
		$stats[] = array('Server Time', '{#black}' . date('H:i:s T'));
		$value = '{#black}' . $aseco->formatTime($target->getTimePlayed() * 1000, false);
		// add clickable button
		if ($aseco->settings['clickable_lists']) {
			$value = array($value, 'PluginManialinks?Action=-5');  // action id
		}
		$stats[] = array('Time Played', $value);
		$stats[] = array('Last Online', '{#black}' . preg_replace('/:\d\d$/', '', $laston[0]));
		$value = '{#black}'. $target->getRankFormated();
		// add clickable button
		if ($aseco->settings['clickable_lists']) {
			$value = array($value, 'PluginManialinks?Action=-6');  // action id
		}
		$stats[] = array('Server Rank', $value);
		$value = '{#black}' . $records;
		// add clickable button
		if ($aseco->settings['clickable_lists']) {
			$value = array($value, 'PluginManialinks?Action=5');  // action id
		}
		$stats[] = array('Records', $value);
		$value = '{#black}' . ($target->getWins() > $target->wins ? $target->getWins() : $target->wins);
		// add clickable button
		if ($aseco->settings['clickable_lists']) {
			$value = array($value, 'PluginManialinks?Action=6');  // action id
		}
		$stats[] = array('Races Won', $value);
		$stats[] = array('Ladder Rank', '{#black}' . $frank);
		$stats[] = array('Ladder Score', '{#black}' . round($score, 1));
		$stats[] = array('Last Match', '{#black}' . round($lastm, 1));
		$stats[] = array('Wins', '{#black}' . $fwins);
		$stats[] = array('Draws', '{#black}' . $fdraws . ($losses !== 0 ? '   $gW/L: {#black}' . round($wins / $losses, 3) : ''));
		$stats[] = array('Losses', '{#black}' . $flosses);
		$stats[] = array('Zone', '{#black}' . implode(', ', $target->zone));
		$stats[] = array('Inscribed', '{#black}' . $inscrdays . ' day' . ($inscrdays === 1 ? ' ' : 's ') . $inscrhours . ' hours');
		$stats[] = array('Client', '{#black}' . $target->client);
		if ($aseco->allowAbility($player, 'chat_statsip')) {
			$stats[] = array('IP', '{#black}' . $target->ipport);
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons128x128_1', 'Statistics', 0.03), $stats, array(1.0, 0.3, 0.7), 'OK');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_settings ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$target = $player;

		// check for optional login parameter if any admin
		if ($chat_parameter !== '' && $aseco->allowAbility($player, 'chat_settings')) {
			if (!$target = $aseco->server->players->getPlayerParam($player, $chat_parameter, true)) {
				return;
			}
		}

		// get panel settings
		if ( isset($aseco->plugins['PluginPanels']) ) {
			$panels = $aseco->plugins['PluginPanels']->getPanels($target->login);
		}
		else {
			$panels = false;
		}

		// get panel background
		if ( isset($aseco->plugins['PluginPanels']) ) {
			$panelbg = $aseco->plugins['PluginPanels']->getPanelBG($target->login);
		}
		else {
			$panelbg = false;
		}

		$header = 'Settings for: ' . $target->nickname . '$z / {#login}' . $target->login;
		$settings = array();
		$settings[] = array('Panel Background', '{#black}' . $panelbg);

		if ($panels) {
			$settings[] = array();
			if ($aseco->isAnyAdmin($target)) {
				$settings[] = array('Admin Panel', '{#black}' . substr($panels['admin'], 5));
			}
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons128x128_1', 'Inputs', 0.03), $settings, array(1.0, 0.3, 0.7), 'OK');
	}
}

?>

<?php
/**
 * @package     Joomla\Module\TelegramBotMessage\Site\Helper\Telegram
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomla\Module\TelegramBotMessage\Site\Helper\Telegram;


// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;

// phpcs:enable PSR1.Files.SideEffects



trait TelegramTrait
{

	/**
	 * @param   string  $token
	 *
	 * @return \Joomla\Module\TelegramBotMessage\Site\Helper\Telegram\Telegram
	 *
	 * @since version
	 */
	final public static function telegram(string $token): Telegram
	{
		return new Telegram($token);
	}

}
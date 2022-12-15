<?php

/**
 * @package         Joomla.Site
 * @subpackage      mod_telegram_bot_message
 *
 * @author          M.Kulyk
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @since
 */

use Joomla\Module\TelegramBotMessage\Site\Helper\TelegramBotMessageHelper;

/**
 * @package
 *
 * @since
 * fix - because joomla 4.2.0 has a new module initialisation but com_ajax needs old helper
 */
class helper extends TelegramBotMessageHelper
{


}
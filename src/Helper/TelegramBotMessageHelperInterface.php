<?php
/**
 * @package     Joomla\Module\TelegramBotMessage\Site\Helper
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomla\Module\TelegramBotMessage\Site\Helper;

interface TelegramBotMessageHelperInterface
{

	/**
	 * @var string
	 * @since 4.2
	 */
	public const FIELD_NAME = 'name';


	/**
	 * @var string
	 * @since 4.2
	 */
	public const FIELD_PHONE = 'phone';


	/**
	 * @var string
	 * @since 4.2
	 */
	public const FIELD_ANNOTATION = 'annotation';


	/**
	 * @var string
	 * @since 4.2
	 */
	public const FIELD_ADDITIONAL = 'additional';


	/**
	 * @var string
	 * @since 4.2
	 */
	public const FIELD_MODULE_ID = 'module_id';

	/**
	 * @var string
	 * @since 4.2
	 */
	public const FIELD_CAPTCHA = 'captcha';


	/**
	 *
	 * @return array
	 *
	 * @since 4.2
	 */
	public function getForm(): array;


	/**
	 *
	 * @return array
	 *
	 * @since 4.2.0
	 */
	public function getAjax(): array;

}
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


namespace Joomla\Module\TelegramBotMessage\Site\Helper;


// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;

// phpcs:enable PSR1.Files.SideEffects


use stdClass;
use JModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Application\WebApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Module\TelegramBotMessage\Site\Helper\Telegram\TelegramTrait;


/**
 * @package     Joomla\Module\TelegramBotMessage\Site\Helper
 *
 * @since       version
 */
class TelegramBotMessageHelper implements TelegramBotMessageHelperInterface
{

	/**
	 * @var \Joomla\CMS\Form\Form|null
	 * @since 4.2.0
	 */
	public ?Form $form = null;


	/**
	 * @var WebApplication|null
	 * @since  4.2.0
	 */
	private static ?WebApplication $app = null;


	/**
	 * @var string
	 * @since  4.2.0
	 */
	private const AJAX_URL = 'index.php?option=com_ajax&module=telegram_bot_message&method=get&format=json';


	use TelegramTrait;

	/**
	 *
	 * @throws \Exception
	 * @since 4.2
	 */
	public function __construct()
	{
		if (self::$app === null)
		{
			self::$app = Factory::getApplication();
		}
		$this->bind([
			'bindForm', 'bindStyles', 'bindScripts'
		]);
	}


	/**
	 *
	 * @return array
	 *
	 * @since 4.2.0
	 */
	public function getAjax(): array
	{
		$moduleParams = null;
		$validCaptcha = false;
		$post         = self::$app->input->getArray();

		if (isset($post['module_id']))
		{
			$module       = $this->getModule($post[self::FIELD_MODULE_ID]);
			$moduleParams = new Registry($module->params);
		}

		$useCaptcha = $moduleParams->get('use_captcha');

		$result['sender']['ok'] = false;
		$result['post']         = $post;

		$selectCaptcha = self::$app->getConfig()->get('captcha', '');

		if ($useCaptcha)
		{
			if ($selectCaptcha !== "" && $selectCaptcha !== null && !($post[self::FIELD_CAPTCHA] === ""))
			{
				PluginHelper::importPlugin('captcha');
				$validation   = self::$app->triggerEvent('onCheckAnswer', [$post[self::FIELD_CAPTCHA]]);
				$validCaptcha = $validation[0] ?? false;
			}
		}
		else
		{
			$validCaptcha = true;
		}

		if ($validCaptcha && $moduleParams instanceof Registry && isset($post[self::FIELD_NAME], $post[self::FIELD_PHONE]))
		{
			$message          = implode('; ', array_filter([
				$post[self::FIELD_NAME], $post[self::FIELD_PHONE], $post[self::FIELD_ADDITIONAL], $post[self::FIELD_ANNOTATION]
			]));
			$sender           = $this->telegram($moduleParams->get('token', ''))
				->sendMessage([
						'chat_id' => $moduleParams->get('chat_id'),
						'text'    => $message
					]
				);
			$result['sender'] = $sender;
		}

		return $result;
	}


	/**
	 *
	 * @return array
	 *
	 * @since 4.2.0
	 */
	final public function getForm(): array
	{
		return [
			'form' => $this->form
		];
	}


	/**
	 * @param   array  $methods
	 *
	 *
	 * @since 4.2.0
	 */
	private function bind(array $methods = []): void
	{
		foreach ($methods as $method)
		{
			if (method_exists($this, $method))
			{
				$this->$method();
			}
		}
	}


	/**
	 * @param   string  $moduleId
	 *
	 * @return \stdClass|null
	 *
	 * @since 4.2.0
	 */
	private function getModule(string $moduleId): ?stdClass
	{
		return JModuleHelper::getModuleById((string) $moduleId);
	}

	/**
	 *
	 * @since 4.2.0
	 */
	private function bindForm(): void
	{
		$form = new Form("telegram", [
			'control' => false,
			'id'      => 'telegram'
		]);

		$form->loadFile(JPATH_ROOT . '/modules/mod_telegram_bot_message/forms/form.xml');
		$this->form = $form;
	}


	/**
	 *
	 * @since 4.2.0
	 */
	private function bindStyles(): void
	{
		/** @var  $document */
		$document = self::$app->getDocument();
		$document->getWebAssetManager()
			->getRegistry()
			->addRegistryFile('modules/mod_telegram_bot_message/joomla.asset.json');

		$document->getWebAssetManager()->useStyle('module.telegram-bot-message.style.css');
	}


	/**
	 *
	 * @since 4.2.0
	 */
	private function bindScripts(): void
	{
		/** @var  $document */
		$document = self::$app->getDocument();

		// Form
		$formName                = $this->form->getName();
		$formId                  = '#' . $formName . '-form';
		$formResponseResultClass = '.' . $formName . '-response-result';
		$ajaxUrl                 = \JUri::root() . self::AJAX_URL;
		$successMessage          = \JText::_('MOD_TELEGRAM_BOT_MESSAGE_SUCCESS');
		$warningMessage          = \JText::_('MOD_TELEGRAM_BOT_MESSAGE_WARNING');
		$errorMessage            = \JText::_('MOD_TELEGRAM_BOT_MESSAGE_ERROR');

		// Fields
		$name       = self::FIELD_NAME;
		$phone      = self::FIELD_PHONE;
		$annotation = self::FIELD_ANNOTATION;
		$additional = self::FIELD_ADDITIONAL;
		$moduleId   = self::FIELD_MODULE_ID;
		$captcha    = self::FIELD_CAPTCHA;


		$document->getWebAssetManager()->addInlineScript("
			jQuery(document).ready(function () {
				  jQuery('$formId').submit(function (event) {
				    let formData = {
				      $name: jQuery('#name').val(),
				      $phone: jQuery('#phone').val(),
				      $annotation: jQuery('#annotation').val(),
				      $additional: jQuery('#additional').val(),
				      $moduleId: jQuery('#module_id').val(),
				      $captcha: jQuery('#g-recaptcha-response').val(),
				    };
				    jQuery.ajax({
				      type: 'POST',
				      url: '$ajaxUrl',
				      data: formData,
				      dataType: 'json',
				      encode: false,
				      error: function() {
                        jQuery('$formResponseResultClass').addClass('error').html('$errorMessage');
                      },
                       success: function(response) {
                        if(response.data.sender.ok === true) {
                            jQuery('$formResponseResultClass').addClass('success').html('$successMessage');
                        } else {
                            jQuery('$formResponseResultClass').addClass('success').html('$warningMessage');
                        }
                      }
				    }).done(function (response) {
				    });
				    event.preventDefault();
				  });
			});
		");
	}


}
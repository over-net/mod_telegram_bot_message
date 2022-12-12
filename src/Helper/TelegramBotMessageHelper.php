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
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Application\WebApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Module\TelegramBotMessage\Site\Helper\Telegram\TelegramTrait;


/**
 * @package     Joomla\Module\TelegramBotMessage\Site\Helper
 *
 * @since       4.2.0
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
	 * @var \string[][]
	 * @since 4.2.0
	 */
	private const MSG = [
		'success'        => 'success',
		'warning'        => 'warning',
		'warningCaptcha' => 'warningCaptcha',
		'error'          => 'error',
	];

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

		$result['type'] = self::MSG['error'];

		if (isset($post['module_id']))
		{
			$module       = $this->getModule($post[self::FIELD_MODULE_ID]);
			$moduleParams = new Registry($module->params);
		}

		$useCaptcha = $moduleParams && $moduleParams->get('use_captcha');

		if ($useCaptcha)
		{
			if (isset($post[self::FIELD_CAPTCHA]) && $post[self::FIELD_CAPTCHA] !== "")
			{
				$config       = self::$app->getConfig()->get('captcha', '');
				$captcha      = \JCaptcha::getInstance($config);
				$validCaptcha = $captcha->CheckAnswer($post[self::FIELD_CAPTCHA]);
			}
			else
			{
				$result['type'] = self::MSG['warningCaptcha'];
			}
		}
		else
		{
			$validCaptcha = true;
		}

		if ($validCaptcha && $moduleParams instanceof Registry && isset($post[self::FIELD_NAME], $post[self::FIELD_PHONE]))
		{
			$message = implode('; ', array_filter([
				$post[self::FIELD_NAME], $post[self::FIELD_PHONE], $post[self::FIELD_ADDITIONAL], $post[self::FIELD_ANNOTATION]
			]));
			$sender  = $this->telegram($moduleParams->get('token', ''))
				->sendMessage([
						'chat_id' => $moduleParams->get('chat_id'),
						'text'    => $message
					]
				);
			if (isset($sender['ok']) && $sender['ok'] === true)
			{
				$result['type'] = self::MSG['success'];
			}
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
	 * @param   string  $moduleId
	 *
	 * @return \stdClass|null
	 *
	 * @since 4.2.0
	 */
	private function getModule(string $moduleId): ?stdClass
	{
		return JModuleHelper::getModuleById($moduleId);
	}


	/**
	 *
	 * @return array
	 * @since 4.2.0
	 */
	private static function messages(): array
	{
		return [
			self::MSG['success']        => Text::_('MOD_TELEGRAM_BOT_MESSAGE_SUCCESS'),
			self::MSG['warning']        => Text::_('MOD_TELEGRAM_BOT_MESSAGE_WARNING'),
			self::MSG['warningCaptcha'] => Text::_('MOD_TELEGRAM_BOT_CAPTCHA_MESSAGE_WARNING'),
			self::MSG['error']          => Text::_('MOD_TELEGRAM_BOT_MESSAGE_ERROR')
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
	 *
	 * @since 4.2.0
	 */
	private function bindForm(): void
	{
		$form = new Form("telegram", [
			'control' => false,
			'id'      => 'telegram',
			'class'   => 'form-validate'
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
	 * @throws \JsonException
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

		// Fields
		$name       = self::FIELD_NAME;
		$phone      = self::FIELD_PHONE;
		$annotation = self::FIELD_ANNOTATION;
		$additional = self::FIELD_ADDITIONAL;
		$moduleId   = self::FIELD_MODULE_ID;
		$captcha    = self::FIELD_CAPTCHA;

		// Add validation
		$document->getWebAssetManager()->useScript('form.validate');

		$messages = json_encode(self::messages(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

		// Add Ajax script
		$document->getWebAssetManager()->addInlineScript("
			const telegramMessages = $messages;
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
				    }).done(function (response) {
				        jQuery('$formResponseResultClass')
				            .removeAttr('data-type')
				            .attr('data-type', response.data.type)
				            .html(telegramMessages[response.data.type]);
				            if(response.data.type === 'success') {
						        jQuery('#captcha, #telegram-submit').hide('slow');
						        jQuery('#captcha').parent('.controls').parent('.control-group').hide('slow');
				            }
				    });
				    event.preventDefault();
				  });
			});
		");
	}


}
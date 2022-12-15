<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_telegram_bot_message
 *
 * @copyright   M.Kulyk
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\Module\TelegramBotMessage\Site\Helper\TelegramBotMessageHelper;
use Joomla\Registry\Registry;

/** @var TelegramBotMessageHelper $model */
/** @var \stdClass $module */
/** @var Registry $params */

?>


<form method="post" name="<?= $model->form->getName() ?>" class="form-validate" id="<?= $model->form->getName() ?>-form"
      enctype="multipart/form-data">

	<?php echo $model->form->renderField($model::FIELD_NAME); ?>

	<?php echo $model->form->renderField($model::FIELD_PHONE); ?>

	<?php echo $model->form->renderField($model::FIELD_ANNOTATION); ?>

	<?php echo $model->form->renderField($model::FIELD_ADDITIONAL); ?>

	<?php echo $model->form->renderField($model::FIELD_MODULE_ID, null, $module->id); ?>

	<?php if ($params->get('use_captcha')) : ?>
		<?php echo $model->form->renderField($model::FIELD_CAPTCHA); ?>
	<?php endif ?>

	<?php echo JHtml::_('form.token'); ?>

    <div class="<?= $model->form->getName() ?>-response-result"></div>
    <br>

    <button id="<?= $model->form->getName() ?>-submit" class="btn btn-lg btn-secondary" type="submit">
        <i class="fas fa-solid fa-paper-plane"></i> <?= JText::_('MOD_TELEGRAM_BOT_MESSAGE_SEND_LABEL') ?>
    </button>

</form>



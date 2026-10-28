<?php
/**
 *  @package   AkeebaSocialLogin
 *  @copyright Copyright (c)2016-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 *  @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Renders the social login buttons above the user registration form.
 *
 * Generic data
 *
 * @var   array  $displayData  The data in array format. DO NOT USE.
 *
 * Layout specific data
 *
 * @var   array  $buttons  Button definitions, in the format returned by the onUserLoginButtons event
 */

$buttons = $displayData['buttons'] ?? [];

// Start writing your template override code below this line
?>
<div class="akeeba-sociallogin-registration mb-4">
	<?php foreach ($buttons as $button): ?>
		<?php $dataAttributeKeys = array_filter(array_keys($button), fn($key) => str_starts_with($key, 'data-')); ?>
		<div class="control-group">
			<div class="controls">
				<button type="button"
						class="btn btn-secondary w-100 <?= htmlspecialchars($button['class'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
						<?php foreach ($dataAttributeKeys as $key): ?>
							<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>="<?= htmlspecialchars($button[$key], ENT_QUOTES, 'UTF-8') ?>"
						<?php endforeach; ?>
						title="<?= htmlspecialchars(strip_tags(Text::_($button['label'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
						id="<?= htmlspecialchars($button['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
				>
					<?php if (!empty($button['icon'])): ?>
						<span class="<?= htmlspecialchars($button['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></span>
					<?php elseif (!empty($button['image'])): ?>
						<?= HTMLHelper::_('image', $button['image'], '', ['class' => 'icon'], true) ?>
					<?php elseif (!empty($button['svg'])): ?>
						<?= $button['svg'] ?>
					<?php endif; ?>
					<?= Text::_($button['label'] ?? '') ?>
				</button>
			</div>
		</div>
	<?php endforeach; ?>
	<p class="akeeba-sociallogin-registration-divider text-center mt-3">
		<?= Text::_('PLG_SYSTEM_SOCIALLOGIN_LBL_REGISTRATION_DIVIDER') ?>
	</p>
</div>

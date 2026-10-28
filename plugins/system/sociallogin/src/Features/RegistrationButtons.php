<?php
/**
 *  @package   AkeebaSocialLogin
 *  @copyright Copyright (c)2016-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 *  @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Plugin\System\SocialLogin\Features;

// Prevent direct access
defined('_JEXEC') || die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\User\LoginButtonsEvent;
use Joomla\CMS\Log\Log;
use Joomla\Event\Event;

/**
 * Feature: social login buttons on the user registration page
 *
 * Joomla only asks plugins for login buttons on login forms (onUserLoginButtons). The registration page never does,
 * so this feature renders the same buttons above the registration form of com_users.
 *
 * @since   4.11.1
 */
trait RegistrationButtons
{
	/**
	 * Inserts the social login buttons above the user registration form.
	 *
	 * @param   Event  $event
	 *
	 * @return  void
	 * @since   4.11.1
	 *
	 * @noinspection PhpUnused
	 */
	public function onAfterDispatch(Event $event): void
	{
		if (!$this->enabled || !$this->addRegistrationButtons)
		{
			return;
		}

		$app = $this->getApplication();

		if (!$app->isClient('site'))
		{
			return;
		}

		$input = $app->getInput();

		if (
			$input->getCmd('option') !== 'com_users'
			|| $input->getCmd('view') !== 'registration'
			|| !in_array($input->getCmd('layout', 'default'), ['', 'default'], true)
		)
		{
			return;
		}

		$document = $app->getDocument();

		if (!$document instanceof HtmlDocument)
		{
			return;
		}

		// Reuse the login buttons feature, so the buttons are identical to the ones on the login page.
		$buttonsEvent = new LoginButtonsEvent('onUserLoginButtons', ['subject' => 'member-registration']);
		$this->onUserLoginButtons($buttonsEvent);

		$buttons = [];

		foreach ($buttonsEvent['result'] ?? [] as $result)
		{
			if (is_array($result))
			{
				$buttons = array_merge($buttons, $result);
			}
		}

		if (empty($buttons))
		{
			return;
		}

		// The language loaded in init() may predate the site language being set up
		$this->loadLanguage();

		$html = self::renderLayout('akeeba.sociallogin.registrationbuttons', ['buttons' => $buttons]);

		if (trim($html) === '')
		{
			return;
		}

		// Insert the buttons right before the registration form.
		$count  = 0;
		$buffer = preg_replace_callback(
			'/<form\b[^>]*\bid\s*=\s*["\']member-registration["\']/i',
			fn(array $m): string => $html . $m[0],
			(string) $document->getBuffer('component'),
			1,
			$count
		);

		if ($count === 0)
		{
			Log::add(
				'Registration form (id="member-registration") not found in the component output. Not adding social login buttons.',
				Log::DEBUG,
				'sociallogin.system'
			);

			return;
		}

		$document->setBuffer($buffer, ['type' => 'component']);
	}
}

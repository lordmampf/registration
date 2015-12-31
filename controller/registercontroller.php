<?php
/**
 * ownCloud - registration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * @author Julius Härtl <jus@bitgrid.net>
 * @copyright Pellaeon Lin 2014
 */

namespace OCA\Registration\Controller;

use OCA\Registration\Db\Registration;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\RedirectResponse;
use \OCP\AppFramework\Controller;
use OCP\IURLGenerator;
use \OCP\IL10N;

class RegisterController extends Controller {

	/** @var IL10N */
	private $l10n;
	/** @var IURLGenerator */
	private $urlgenerator;
	/** @var RegistrationService */
	private $registrationService;
	/** @var MailService */
	private $mailService;


	public function __construct(
		$appName,
		IRequest $request,
		IL10N $l10n,
		IURLGenerator $urlgenerator,
		RegistrationService $registrationService,
		MailService $mailService
	){
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->urlgenerator = $urlgenerator;
		$this->registrationService = $registrationService;
		$this->mailService = $mailService;
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @param $errormsg
	 * @param $entered
	 * @return TemplateResponse
	 */
	public function askEmail($errormsg, $entered) {
		$params = array(
			'errormsg' => $errormsg ? $errormsg : $this->request->getParam('errormsg'),
			'entered' => $entered ? $entered : $this->request->getParam('entered')
		);
		return new TemplateResponse('registration', 'form', $params, 'guest');
	}

	/**
	 * @PublicPage
	 *
	 * @return TemplateResponse
	 */
	public function validateEmail() {
		$email = $this->request->getParam('email');
<<<<<<< HEAD

		if (!$this->registrationService->checkAllowedDomains($email)) {
			return new TemplateResponse('registration', 'domains', [
				'domains' => $this->registrationService->getAllowedDomains()
			], 'guest');
=======
		$username = $this->request->getParam('username');
		$password = $this->request->getParam('password');
		$display_name = $this->request->getParam('display_name');
		if ( !$this->mailer->validateMailAddress($email) ) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('The email address you entered is not valid'),
					'hint' => ''
				))
			), 'error');
		}

		// find if there is an existing request
		if ( $this->pendingreg->find($email) ) {
			$this->pendingreg->delete($email);
			$token = $this->pendingreg->save($email);

			try {
				$this->sendValidationEmail($token, $email);
			} catch (\Exception $e) {
				return new TemplateResponse('', 'error', array(
					'errors' => array(array(
						'error' => $this->l10n->t('There is already a pending registration with this email, but while trying to send a new verification email, a problem occurred, please contact your administrator.'),
						'hint' => ''
					))
				), 'error');
			}
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('There is already a pending registration with this email, a new verification email has been sent to the address.'),
					'hint' => ''
				))
			), 'error');
>>>>>>> 8717ac4... Update# new sign up form
		}
		try {
			$this->registrationService->validateEmail($email);
			$registration = $this->registrationService->createRegistration($email);
			$this->mailService->sendTokenByMail($registration);
		} catch (RegistrationException $e) {
			return $this->renderError($e->getMessage(), $e->getHint());
		}


<<<<<<< HEAD
=======
		// allow only from specific email domain
		$allowed_domains = $this->config->getAppValue($this->appName, 'allowed_domains', '');
		if ( $allowed_domains !== '' ) {
			$allowed_domains = explode(';', $allowed_domains);
			$allowed = false;
			foreach ( $allowed_domains as $domain ) {
				$maildomain=explode("@",$email)[1];
				// valid domain, everythings fine
				if ($maildomain === $domain) {
					$allowed=true;
					break;
				}
			}
			if ( $allowed === false ) {
				return new TemplateResponse('registration', 'domains', ['domains' =>
					$allowed_domains
				], 'guest');
			}
		}

		// validate username as in UserManager::createUser()
		try {
			if (preg_match('/[^a-zA-Z0-9 _\.@\-]/', $username)) {
				throw new \Exception($this->l10n->t('Only the following characters are allowed in a username:'
					. ' "a-z", "A-Z", "0-9", and "_.@-"'));
			}
			// No empty username
			if (trim($username) == '') {
				throw new \Exception($this->l10n->t('A valid username must be provided'));
			}
			// No empty password
			if (trim($password) == '') {
				throw new \Exception($this->l10n->t('A valid password must be provided'));
			}
		} catch (\Exception $e) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $e->getMessage(),
					'hint' => ''
				))
			), 'error');
		}

		$token = $this->pendingreg->save($username, $display_name, $email, \OC::$server->getHasher()->hash($password));
		try {
			$this->sendValidationEmail($token, $email);
		} catch (\Exception $e) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('A problem occurred sending email, please contact your administrator.'),
					'hint' => ''
				))
			), 'error');
		}
>>>>>>> 8717ac4... Update# new sign up form
		return new TemplateResponse('registration', 'message', array('msg' =>
			$this->l10n->t('Verification email successfully sent.')
		), 'guest');
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @param $token
	 * @return TemplateResponse
	 */
	public function verifyToken($token) {
		try {
			/** @var Registration $registration */
			$registration = $this->registrationService->verifyToken($token);
			$this->registrationService->confirmEmail($registration);

			// create account without form if username/password are already stored
			if ($registration->getUsername() !== "" && $registration->getPassword() !== "") {
				$this->registrationService->createAccount($registration);
				return new TemplateResponse('registration', 'message',
					['msg' => $this->l10n->t('Your account has been successfully created, you can <a href="%s">log in now</a>.', [$this->urlgenerator->getAbsoluteURL('/')])],
					'guest'
				);
			}

			return new TemplateResponse('registration', 'form', ['email' => $registration->getEmail(), 'token' => $registration->getToken()], 'guest');
		} catch (RegistrationException $exception) {
			return $this->renderError($exception->getMessage(), $exception->getHint());
		}

	}

	/**
	 * @PublicPage
	 * @UseSession
	 *
	 * @param $token
	 * @return RedirectResponse|TemplateResponse
	 */
	public function createAccount($token) {
		$username = $this->request->getParam('username');
		$password = $this->request->getParam('password');
		$registration = $this->registrationService->getRegistrationForToken($token);

		try {
			$user = $this->registrationService->createAccount($registration, $username, $password);
		} catch (RegistrationException $exception) {
			return $this->renderError($exception->getMessage(), $exception->getHint());
		} catch (\InvalidArgumentException $exception) {
			// Render form with previously sent values
			return new TemplateResponse('registration', 'form',
				[
					'email' => $registration->getEmail(),
					'entered_data' => array('user' => $username),
					'errormsgs' => array($exception->getMessage()),
					'token' => $token
				], 'guest');
		}

		if ($user->isEnabled()) {
			// log the user
			return $this->registrationService->loginUser($user->getUID(), $username, $password, false);
		} else {
			// warn the user their account needs admin validation
			return new TemplateResponse(
				'registration',
				'message',
				array('msg' => $this->l10n->t("Your account has been successfully created, but it still needs approval from an administrator.")),
				'guest');
		}
	}

	private function renderError($error, $hint="") {
		return new TemplateResponse('', 'error', array(
			'errors' => array(array(
				'error' => $error,
				'hint' => $hint
			))
		), 'error');
	}

}

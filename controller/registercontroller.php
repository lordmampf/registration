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
use \OCP\IConfig;

class RegisterController extends Controller {

	/** @var IL10N */
	private $l10n;
	/** @var IURLGenerator */
	private $urlgenerator;
	/** @var RegistrationService */
	private $registrationService;
	/** @var MailService */
	private $mailService;
	/** @var IConfig */
	private $config;

	public function __construct(
		$appName,
		IRequest $request,
		IL10N $l10n,
		IURLGenerator $urlgenerator,
		RegistrationService $registrationService,
		MailService $mailService,
		IConfig $config
	){
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->urlgenerator = $urlgenerator;
		$this->registrationService = $registrationService;
		$this->mailService = $mailService;
		$this->config = $config;
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

		if (!$this->registrationService->checkAllowedDomains($email)) {
			return new TemplateResponse('registration', 'domains', [
				'domains' => $this->registrationService->getAllowedDomains()
			], 'guest');
		}
		
		try {
			$this->registrationService->validateEmail($email);
		} catch (RegistrationException $e) {
			return $this->renderError($e->getMessage(), $e->getHint());
		}		
		
		$username = $this->request->getParam('username');
		$password = $this->request->getParam('password');
				
		try {
			$this->registrationService->validateUsername($username);
		} catch (RegistrationException $e) {
			return $this->renderError($e->getMessage(), $e->getHint());
		}		
			
		try {
			$this->registrationService->validatePassword($password);
		} catch (RegistrationException $e) {
			return $this->renderError($e->getMessage(), $e->getHint());
		}

		try {
			$registration = $this->registrationService->createRegistration($email, $username, $password );			
			
			//lordmampf I don't understand why we need this, but it's used in API. This is form register so ClientSecret is null
			$this->registrationService->setClientSecret($registration, null);
						
			$this->mailService->sendTokenByMail($registration);
		} catch (RegistrationException $e) {
			return $this->renderError($e->getMessage(), $e->getHint());
		}

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
			$registration = $this->registrationService->verifyToken($token);
			$this->registrationService->confirmEmail($registration);
			
			// create account without form if username/password are already stored
			if ($registration->getUsername() !== "" && $registration->getPassword() !== "") {
			
				if($this->config->getAppValue($this->appName, 'admin_approval_required', 'no') == "yes") {
					//Admin will approve this account				
				
					return new TemplateResponse('registration', 'message',
						['msg' => $this->l10n->t('Your account has been successfully created. An Admin will now approve your account!')],
						'guest'
					);
				}
				
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
	/*	
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
		}*/
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

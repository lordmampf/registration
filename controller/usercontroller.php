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
use \OCP\AppFramework\Http\JSONResponse;
use OCP\IURLGenerator;
use \OCP\IL10N;
use \OCP\IConfig;

class UserController extends Controller {

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
		
	public function getRegistrations() {      
		//{'username': 'clubmate', 'email': 'clib@mate.me', 'displayname': 'John Doe', 'email_validated': false},
		
		$ret = array();
		
		$needApprove = $this->registrationService->findRegistrationsWhichNeedApprovement();
		
		foreach($needApprove as $reg) {
			$ret[] = array('username' => $reg->getUsername(), 'email' => $reg->getEmail(), 'email_validated' => $reg->getEmailConfirmed());
		}
		
        return new JSONResponse($ret);
    }
	
	public function approveRegistration($username) {
		try {
			$registration = $this->registrationService->getRegistrationForUsername($username);
			$user = $this->registrationService->createAccount($registration);
			return new JSONResponse("ok");
		} catch (Exception $exception) {
			error_log($exception);
			return new JSONResponse($exception->getMessage());
		}
    }

	public function deleteRegistration($username) {
		try {
			$reg = $this->registrationService->getRegistrationForUsername($username);
			$this->registrationService->deleteRegistration($reg);
			return new JSONResponse("ok");
		} catch (Exception $exception) {
			 return new JSONResponse($exception->getMessage());
		}	
    }
	
}

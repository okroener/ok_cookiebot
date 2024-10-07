<?php

namespace OliverKroener\OkCookiebotCookieConsent\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use OliverKroener\OkCookiebotCookieConsent\Service\DatabaseService;

class ConsentController extends ActionController
{

    /**
     * @var DatabaseService
    */
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Displays the form to edit the consent script
     */
    public function indexAction()
    {

        $scripts = $this->databaseService->getConsentScripts();

        if (!empty($scripts)) {
            // Assign data to the view
            $this->view->assign('tx_ok_cookiebot_banner_script', $scripts['tx_ok_cookiebot_banner_script']);
            $this->view->assign('tx_ok_cookiebot_declaration_script', $scripts['tx_ok_cookiebot_declaration_script']);
        } else {
            return $this->redirect('error');
        }
    }

    /**
     * Shows an error, when no site root exists
     */
    public function errorAction()
    {
    }

    /**
     * Saves the consent script
     */
    public function saveAction()
    {
        $bannerScript = $this->request->getArgument('tx_ok_cookiebot_banner_script') ?? '';
        $declarationScript = $this->request->getArgument('tx_ok_cookiebot_declaration_script') ?? '';

        $this->databaseService->saveConsentScript($bannerScript, $declarationScript);

        // Add a flash message
        $this->addFlashMessage(
            LocalizationUtility::translate('flash.message.success', 'ok_cookiebot'),
            '',
            AbstractMessage::OK
        );

        // Redirect back to index
        $this->redirect('index');
    }
}

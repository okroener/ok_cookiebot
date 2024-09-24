<?php

namespace OliverKroener\OkCookiebotCookieConsent\Controller;

use OliverKroener\Helpers\Service\SiteRootService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Backend\Template\ModuleTemplate;

class ConsentController extends ActionController
{
    /**
     * @var SiteRootService
     */
    private $siteRootService;

    /**
     * @var PageRenderer
     */
    private $pageRenderer;

    public function __construct(SiteRootService $siteRootService, PageRenderer $pageRenderer)
    {
        $this->siteRootService = $siteRootService;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * Displays the form to edit the consent script
     */
    public function indexAction()
    {

        $scripts = $this->getConsentScripts();

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

        $this->saveConsentScript($bannerScript, $declarationScript);

        // Add a flash message
        $this->addFlashMessage(
            'Script saved successfully.',
            '',
            AbstractMessage::OK
        );

        // Redirect back to index
        $this->redirect('index');
    }

    /**
     * Retrieves the consent script from the first sys_template record
     *
     * @return ?array
     */
    protected function getConsentScripts(): ?array
    {
        /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_template');

        $currentPageId = (int)GeneralUtility::_GP('id');
        $siteRootPid = $this->siteRootService->findNextSiteRoot($currentPageId);

        // return null if no site root is found
        if (!$siteRootPid) return null;

        $scripts = $queryBuilder
            ->select('tx_ok_cookiebot_banner_script', 'tx_ok_cookiebot_declaration_script')
            ->from('sys_template')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($siteRootPid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAssociative();

        return $scripts;
    }

    /**
     * Saves the consent script to the first sys_template record
     *
     * @param string $script
     * @return void
     */
    protected function saveConsentScript(string $bannerScript, string $declarationScript): void
    {
        /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_template');

        $currentPageId = (int)GeneralUtility::_GP('id');
        $siteRootPid = $this->siteRootService->findNextSiteRoot($currentPageId);

        // Fetch the first sys_template record with pid=0
        $record = $queryBuilder
            ->select('uid')
            ->from('sys_template')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($siteRootPid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchFirstColumn();

        if ($record[0]) {
            // Update existing record
            $queryBuilder
                ->update('sys_template')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$record[0], \PDO::PARAM_INT))
                )
                ->set('tx_ok_cookiebot_banner_script', $bannerScript)
                ->set('tx_ok_cookiebot_declaration_script', $declarationScript)
                ->execute();
        }
    }

    /**
     * Retrieves the specified script from the active sys_template.
     *
     * @param string $content The current content (unused)
     * @param array $conf Configuration array, expecting 'type' => 'head' or 'body'
     * @return string The script content or an empty string if not set.
     */
    public function renderBannerScript($content, $conf): string
    {
        $scriptContent = '';

        // Determine which script to fetch based on 'type' parameter
        $type = isset($conf['type']) ? strtolower($conf['type']) : 'head';

        // Validate the type parameter
        if (!in_array($type, ['head', 'body'])) {
            // Invalid type specified
            return $scriptContent;
        }

        // Get scripts
        $scripts = $this->getConsentScripts();

        if ($type === 'head') {
            return $scripts['tx_ok_cookiebot_banner_script'] ?? '';
        } else {
            return $scripts['tx_ok_cookiebot_declaration_script'] ?? '';
        }
    }
}

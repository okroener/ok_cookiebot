<?php

declare(strict_types=1);

namespace OliverKroener\OkCookiebotCookieConsent\Controller;

use OliverKroener\Helpers\Service\SiteRootService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Context\Context;
use Exception;

#[AsController]
final class BackendController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly SiteRootService $siteRootService,
        protected readonly ConnectionPool $connectionPool,
        protected readonly Context $context
    ) 
    {
    }

    /**
     * Displays the form to edit the consent script
     */
    public function indexAction(): ResponseInterface
    {
        try {
            // Accessing the request object inside the action
            $moduleTemplate = $this->moduleTemplateFactory->create($this->request);


            $scripts = $this->getConsentScripts();

            if (!empty($scripts)) {
                // Assign data to the view
                $this->view->assign('tx_ok_cookiebot_banner_script', $scripts['tx_ok_cookiebot_banner_script']);
                $this->view->assign('tx_ok_cookiebot_declaration_script', $scripts['tx_ok_cookiebot_declaration_script']);
            } else {
                return $this->redirect('error');
            }

            //$moduleTemplate->setContent($this->view->render());
            return $this->htmlResponse($this->view->render());
        } catch (Exception $e) {
            return $this->htmlResponse();
        }
    }

    /**
     * Shows an error, when no site root exists
     */
    public function errorAction(): ResponseInterface
    {
        // Render the view and return the HTML response
        $content = $this->view->render();

        return $this->htmlResponse($content);
    }

    /**
     * Saves the consent script
     */
    public function saveAction(): ResponseInterface
    {
        $bannerScript = $this->request->getArgument('tx_ok_cookiebot_banner_script') ?? '';
        $declarationScript = $this->request->getArgument('tx_ok_cookiebot_declaration_script') ?? '';

        $this->saveConsentScript($bannerScript, $declarationScript);

        // Add a flash message
        $this->addFlashMessage(
            LocalizationUtility::translate('flash.message.success', 'ok_cookiebot'),
            '',
            ContextualFeedbackSeverity::OK
        );

        // Redirect back to index
        return $this->redirect('index');
    }

    /**
     * Retrieves the consent script from the first sys_template record
     *
     * @param bool $frontendMode
     * @return ?array
     */
    protected function getConsentScripts($frontendMode = false): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_template');

        if ($frontendMode) {
            // Retrieve the current page ID
            $currentPageId = (int)$this->context->getPropertyFromAspect('page', 'id');
        } else {
            // Fetch the page ID from request attributes
            $currentPageId = (int)$this->request->getQueryParams()['id'] ?? 0;
        }

        $siteRootPid = $this->siteRootService->findNextSiteRoot($currentPageId);

        // return null if no site root is found
        if (!$siteRootPid) return null;

        $scripts = $queryBuilder
            ->select('tx_ok_cookiebot_banner_script', 'tx_ok_cookiebot_declaration_script')
            ->from('sys_template')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($siteRootPid, Connection::PARAM_INT))
            )
            ->executeQuery()  // Changed from execute() to executeQuery()
            ->fetchAssociative();

        return $scripts;
    }

    /**
     * Saves the consent script to the first sys_template record
     *
     * @param string $bannerScript
     * @param string $declarationScript
     * @return void
     */
    protected function saveConsentScript(string $bannerScript, string $declarationScript): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_template');

        // Fetch the page ID from request attributes
        $currentPageId = (int)$this->request->getQueryParams()['id'] ?? 0;
        $siteRootPid = $this->siteRootService->findNextSiteRoot($currentPageId);

        // Fetch the first sys_template record with pid=0
        $record = $queryBuilder
            ->select('uid')
            ->from('sys_template')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($siteRootPid, Connection::PARAM_INT))
            )
            ->executeQuery()  // Changed from execute() to executeQuery()
            ->fetchAssociative();

        if ($record) {
            // Update existing record
            $uid = (int)$record['uid'] ?? 0;

            $queryBuilder
                ->update('sys_template')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
                )
                ->set('tx_ok_cookiebot_banner_script', $bannerScript)
                ->set('tx_ok_cookiebot_declaration_script', $declarationScript)
                ->executeStatement();  // Changed from execute() to executeStatement()
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
        $scripts = $this->getConsentScripts(true);

        if ($type === 'head') {
            return $scripts['tx_ok_cookiebot_banner_script'] ?? '';
        } else {
            return $scripts['tx_ok_cookiebot_declaration_script'] ?? '';
        }
    }
}

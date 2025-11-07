<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\AgencyPack\Blog\ViewHelpers\Link;

use Psr\Http\Message\ServerRequestInterface;
use T3G\AgencyPack\Blog\Domain\Model\Tag;
use TYPO3\CMS\Core\Routing\PageRouter;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use Webmozart\Assert\Assert;

class TagViewHelper extends AbstractTagBasedViewHelper
{
    public function __construct()
    {
        $this->tagName = 'a';
        parent::__construct();
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('tag', Tag::class, 'The tag to link to', true);
        $this->registerArgument('rss', 'bool', 'Link to rss version', false, false);
    }

    public function render(): string
    {
        $rssFormat = (bool)$this->arguments['rss'];
        /** @var Tag $tag */
        $tag = $this->arguments['tag'];
        $pageUid = (int)($this->getRequest()->getAttribute('frontend.typoscript')->getSetupTree()
            ->getChildByName('plugin')
            ?->getChildByName('tx_blog')
            ?->getChildByName('settings')
            ?->getChildByName('tagUid')
            ?->getValue() ?? 0);
        $arguments = [
            'tag' => $tag->getUid(),
        ];

        $typeNum = null;
        if ($rssFormat) {
            $typeNum = (int)(
                $this->getRequest()->getAttribute('frontend.typoscript')->getSetupTree()
                ->getChildByName('blog_rss_tag')
                ?->getChildByName('typeNum')
                ?->getValue() ?? 0
            );
        }

        $site = $this->getRequest()->getAttribute('site');
        Assert::notEmpty($site);

        /** @var PageRouter $router */
        $router = $site->getRouter();

        $uri = (string) $router->generateUri($pageUid, [
            'tx_blog_tag' => [
                ...$arguments,
                'controller' => 'Post',
                'action' => 'listPostsByTag',
            ],
            'type' => $typeNum,
        ]);

        if ($uri !== '') {
            $linkText = $this->renderChildren() ?? $tag->getTitle();
            $this->tag->addAttribute('href', $uri);
            $this->tag->setContent($linkText);
            $result = $this->tag->render();
        } else {
            $result = $this->renderChildren();
        }

        return (string)$result;
    }

    protected function getRequest(): ServerRequestInterface
    {
        $request = null;
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }
        $request ??= $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            throw new \RuntimeException(
                'ViewHelper blogvh:link.tag needs a request implementing ServerRequestInterface.',
                1729082936
            );
        }
        return $request;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\AgencyPack\Blog\Tests\Functional\ViewHelpers\Link\Be;

use T3G\AgencyPack\Blog\Domain\Model\Comment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class CommentViewHelperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/blog'
    ];

    public function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://test.typo3.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('normalizedParams', new NormalizedParams([], [], '', ''));
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, string $expected): void
    {
        $comment = new Comment();
        $comment->_setProperty('uid', 123);
        $comment->setComment('Lipsum');

        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource($template);

        $view = (new TemplateView($context));
        $view->assign('comment', $comment);

        self::assertSame($expected, $view->render());
    }

    public static function renderDataProvider(): array
    {
        $expectedReturnUrl = '/';
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            $expectedReturnUrl = '%2F';
        }

        return [
            'simple' => [
                '<blogvh:link.be.comment comment="{comment}" />',
                '<a href="/typo3/record/edit?token=dummyToken&amp;edit%5Btx_blog_domain_model_comment%5D%5B123%5D=edit&amp;returnUrl=' . $expectedReturnUrl . '">Lipsum</a>',
            ],
            'target' => [
                '<blogvh:link.be.comment comment="{comment}" target="_blank" />',
                '<a target="_blank" href="/typo3/record/edit?token=dummyToken&amp;edit%5Btx_blog_domain_model_comment%5D%5B123%5D=edit&amp;returnUrl=' . $expectedReturnUrl . '">Lipsum</a>',
            ],
            'itemprop' => [
                '<blogvh:link.be.comment comment="{comment}" itemprop="name" />',
                '<a itemprop="name" href="/typo3/record/edit?token=dummyToken&amp;edit%5Btx_blog_domain_model_comment%5D%5B123%5D=edit&amp;returnUrl=' . $expectedReturnUrl . '">Lipsum</a>',
            ],
            'rel' => [
                '<blogvh:link.be.comment comment="{comment}" rel="noreferrer" />',
                '<a rel="noreferrer" href="/typo3/record/edit?token=dummyToken&amp;edit%5Btx_blog_domain_model_comment%5D%5B123%5D=edit&amp;returnUrl=' . $expectedReturnUrl . '">Lipsum</a>',
            ],
            'returnUri' => [
                '<blogvh:link.be.comment comment="{comment}" returnUri="1" />',
                '/typo3/record/edit?token=dummyToken&amp;edit%5Btx_blog_domain_model_comment%5D%5B123%5D=edit&amp;returnUrl=' . $expectedReturnUrl . '',
            ],
            'content' => [
                '<blogvh:link.be.comment comment="{comment}">Hello</blogvh:link.be.comment>',
                '<a href="/typo3/record/edit?token=dummyToken&amp;edit%5Btx_blog_domain_model_comment%5D%5B123%5D=edit&amp;returnUrl=' . $expectedReturnUrl . '">Hello</a>',
            ],
            'class' => [
                '<blogvh:link.be.comment comment="{comment}" class="class" />',
                '<a class="class" href="/typo3/record/edit?token=dummyToken&amp;edit%5Btx_blog_domain_model_comment%5D%5B123%5D=edit&amp;returnUrl=' . $expectedReturnUrl . '">Lipsum</a>',
            ],
        ];
    }
}

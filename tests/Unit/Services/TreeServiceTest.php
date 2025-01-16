<?php

declare(strict_types=1);

// https://symfony.com/doc/current/testing.html#integration-tests
// The KernelTestCase also makes sure your kernel is rebooted for each test.
// This assures that each test is run independently from each other.

namespace App\Tests\Unit\Services;

use App\Services\TreeService;
use App\Services\TwigRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

// https://docs.phpunit.de/en/11.5/code-coverage.html#targeting-units-of-code

#[CoversClass(TreeService::class)]
class TreeServiceTest extends TestCase
{
    public function testTree(): void
    {
        $pages = [
            ['path' => 'news', 'title' => 'News'],
            ['path' => 'news/2024/post1', 'title' => 'Post1'],
            ['path' => 'news/2024/post2', 'title' => 'Post2'],
            ['path' => 'about', 'title' => 'About'],
        ];

        $treeService = new TreeService();
        $actual = $treeService->buildTree($pages);

        $expected = [
            [
                'path' => 'about',
                'name' => 'about',
                'title' => 'About',
                'children' => [],
            ],
            [
                'path' => 'news',
                'name' => 'news',
                'title' => 'News',
                'children' => [
                    [
                        'path' => 'news/2024',
                        'name' => '2024',
                        'title' => null,
                        'children' => [
                            [
                                'path' => 'news/2024/post1',
                                'name' => 'post1',
                                'title' => 'Post1',
                                'children' => [],
                            ],
                            [
                                'path' => 'news/2024/post2',
                                'name' => 'post2',
                                'title' => 'Post2',
                                'children' => [],
                            ],
                        ],
                    ]
                ]
            ]
        ];

        $this->assertJsonStringEqualsJsonString(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );
    }

    public function testTree2(): void
    {
        $pages = [
            ['path2' => 'news/2024/post1', 'title' => 'Post1'],
            ['path2' => 'news', 'title' => 'News'],
            ['path2' => 'news/2024/post2', 'title' => 'Post2'],
            ['path2' => 'about', 'title' => 'About'],
        ];

        $treeService = new TreeService();
        $actual = $treeService->buildTree($pages, 'path2', 'basename', 'child');

        $expected = [
            [
                'path2' => 'about',
                'basename' => 'about',
                'title' => 'About',
                'child' => [],
            ],
            [
                'path2' => 'news',
                'basename' => 'news',
                'title' => 'News',
                'child' => [
                    [
                        'path2' => 'news/2024',
                        'basename' => '2024',
                        'title' => null,
                        'child' => [
                            [
                                'path2' => 'news/2024/post1',
                                'basename' => 'post1',
                                'title' => 'Post1',
                                'child' => [],
                            ],
                            [
                                'path2' => 'news/2024/post2',
                                'basename' => 'post2',
                                'title' => 'Post2',
                                'child' => [],
                            ],
                        ],
                    ]
                ]
            ]
        ];

        $this->assertJsonStringEqualsJsonString(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );
    }

    public function testTreeShouldNotCreateParents(): void
    {
        $pages = [
            ['path' => 'news/2024/post1', 'title' => 'Post1'],
            ['path' => 'news/2024/post2', 'title' => 'Post2'],
            ['path' => 'news/2025/post3', 'title' => 'Post3'],
        ];

        $treeService = new TreeService();
        $actual = $treeService->buildTree($pages);

        // It will create the non-existing path 'news'
        $expected = [
            [
                'path' => 'news',
                'name' => 'news',
                'title' => null,
                'children' => [
                    [
                        'path' => 'news/2024',
                        'name' => '2024',
                        'title' => null,
                        'children' => [
                            [
                                'path' => 'news/2024/post1',
                                'name' => 'post1',
                                'title' => 'Post1',
                                'children' => [],
                            ],
                            [
                                'path' => 'news/2024/post2',
                                'name' => 'post2',
                                'title' => 'Post2',
                                'children' => [],
                            ],
                        ],
                    ],
                    [
                        'path' => 'news/2025',
                        'name' => '2025',
                        'title' => null,
                        'children' => [
                            [
                                'path' => 'news/2025/post3',
                                'name' => 'post3',
                                'title' => 'Post3',
                                'children' => [],
                            ],
                        ],
                    ]
                ],
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );
    }
}

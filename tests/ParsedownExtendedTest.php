<?php

namespace Leo980\ParsedownExtended\Tests;

use Leo980\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo\ParsedownExtended\ParsedownExtended
 */
class ParsedownExtendedTest extends TestCase
{
    public ParsedownExtended $pe;

    public function setUp(): void
    {
        $this->pe = new ParsedownExtended();
    }

    public function testSuperscript(): void
    {
        $this->assertSame('Cl<sup>-</sup>', $this->pe->line('Cl^-^'));
    }

    public function testSuperscriptWithEscape(): void
    {
        $this->assertSame(
            '<sup>^</sup>_<sup>^</sup>',
            $this->pe->line('^\^^_^\^^'),
        );
    }

    public function testSubscript(): void
    {
        $this->assertSame('H<sub>2</sub>O', $this->pe->line('H~2~O'));
    }

    public function testSubscriptWithEscape(): void
    {
        $this->assertSame(
            'Key<sub>0~5</sub>',
            $this->pe->line('Key~0\\~5~'),
        );
    }

    public function testKeyboard(): void
    {
        $this->assertSame(
            '<kbd>Enter</kbd>',
            $this->pe->line('[[Enter]]'),
        );
    }

    public function testKeyboardEscape(): void
    {
        $this->assertSame(
            'Bracket keys are: <kbd>[</kbd><kbd>]</kbd>',
            $this->pe->line('Bracket keys are: [[\\[]][[\\]]]'),
        );
    }

    public function testSpoiler(): void
    {
        $this->assertSame(
            '凶手是<span class="spoiler">沢木 公平</span>',
            $this->pe->line('凶手是{{沢木 公平}}'),
        );
    }

    public function testSpoilerWithEscape(): void
    {
        $this->assertSame(
            '<span class="spoiler">Spoiler with {{bracket}}!</span>',
            $this->pe->line('{{Spoiler with \{\{bracket\}\}!}}'),
        );
    }

    /**
     * @testdox Remove <code> flag in code block (<pre><code> => <pre>)
     */
    public function testBlockCode1(): void
    {
        $this->assertSame(
            '<pre>    Hello, world!</pre>',
            $this->pe->text("```\n\tHello, world!\n```"),
        );
    }

    /**
     * @testdox Add programming language identifier to code block
     */
    public function testBlockCode2(): void
    {
        $this->assertMatchesRegularExpression(
            '/data-enlighter-language="python"/',
            $this->pe->text("```python\nprint('Hello, world!')\n```\n")
        );
    }
}

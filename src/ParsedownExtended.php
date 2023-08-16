<?php

namespace Leo980\ParsedownExtended;

use Parsedown;

/**
 * An extended Parsedown markdown parser featuring:
 *  - Superscript: ^a^ => <sup>a</sup>
 *  - Subscript: ~b~ => <sub>b</sub>
 *  - Keyboard: [[Enter]] => <kbd></kbd>
 *
 *  - Remove <code> flag for block code (<pre>)
 *  - Enlighter (code highlighting) intergrated with <pre>
 *  - Language identification intergrated with <pre>
 */
class ParsedownExtended extends Parsedown
{
    public function __construct()
    {
        $this->InlineTypes['^'][] = 'Sup';
        $this->inlineMarkerList .= '^';

        $this->InlineTypes['~'][] = 'Sub';

        $this->InlineTypes['['][] = 'Kbd';
        $this->inlineMarkerList .= '[';

        $this->InlineTypes['{'][] = 'Spoiler';
        $this->inlineMarkerList .= '{';
    }

    protected function blockCode($Line, $Block = null)
    {
        if (isset($Block) && !isset($Block['type']) && !isset($Block['interrupted']))
            return;

        if ($Line['indent'] >= 4) {
            $text = substr($Line['body'], 4);

            $Block = [
                'element' => [
                    'name' => 'pre',
                    'handler' => 'element',
                    // Remove <code> flag for block code (<pre>)
                    'text' => $text
                ],
            ];

            return $Block;
        }
    }

    protected function blockFencedCode($Line)
    {
        if (preg_match('/^['.$Line['text'][0].']{3,}[ ]*([^`]+)?[ ]*$/', $Line['text'], $matches)) {
            $Block = [
                'char' => $Line['text'][0],
                'element' => [
                    'name' => 'pre',
                    'text' => '',
                ],
            ];

            if (isset($matches[1])) {
                /**
                 * https://www.w3.org/TR/2011/WD-html5-20110525/elements.html#classes
                 * Every HTML element may have a class attribute specified.
                 * The attribute, if specified, must have a value that is a set
                 * of space-separated tokens representing the various classes
                 * that the element belongs to.
                 * [...]
                 * The space characters, for the purposes of this specification,
                 * are U+0020 SPACE, U+0009 CHARACTER TABULATION (tab),
                 * U+000A LINE FEED (LF), U+000C FORM FEED (FF), and
                 * U+000D CARRIAGE RETURN (CR).
                 */
                $language = substr($matches[1], 0, strcspn($matches[1], " \t\n\f\r"));

                $class = 'language-'.$language;

                $Block['element']['attributes'] = [
                    'class' => $class,
                    'data-enlighter-language' => $language,
                ];
            }

            return $Block;
        }
    }

    protected function blockFencedCodeContinue($Line, $Block)
    {
        if (isset($Block['complete']))
            return;

        if (isset($Block['interrupted'])) {
            $Block['element']['text'] .= "\n";

            unset($Block['interrupted']);
        }

        if (preg_match('/^'.$Block['char'].'{3,}[ ]*$/', $Line['text'])) {
            $Block['element']['text'] = substr($Block['element']['text'], 1);
            $Block['complete'] = true;

            return $Block;
        }

        $Block['element']['text'] .= "\n".$Line['body'];

        return $Block;
    }

    protected function blockFencedCodeComplete($Block)
    {
        $text = $Block['element']['text'];
        $Block['element']['text'] = $text;

        return $Block;
    }

    protected function inlineKbd($Excerpt)
    {
        if (preg_match('/^\[\[(.*?)(?<!\\\\)\]\]/s', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'kbd',
                    'text' => str_replace(['\\[', '\\]'], ['[', ']'], $matches[1]),
                ],
            ];
        }
    }

    protected function inlineSpoiler($Excerpt)
    {
        if (preg_match('/^{{(.*?)(?<!\\\\)}}/s', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'span',
                    'text' => preg_replace(['/\\\\{/', '/\\\\}/'], ['{', '}'], $matches[1]),
                    'attributes' => [
                        'class' => 'spoiler'
                    ],
                ],
            ];
        }
    }

    protected function inlineSup($Excerpt)
    {
        if (preg_match(
            '/^[\^]((?:\\\\\^|[^\^]|[\^*]+)+?)[\^](?![\^])/s',
            $Excerpt['text'],
            $matches
        )) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'sup',
                    'text' => str_replace('\\^', '^', $matches[1]),
                    'function' => 'lineElements'
                ],
            ];
        }
    }

    protected function inlineSub($Excerpt)
    {
        if (preg_match(
            '/^[~]((?:\\\\~|[^~]|[^*]+)+?)[~](?![~])/s',
            $Excerpt['text'],
            $matches
        )) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'sub',
                    'text' => str_replace('\\~', '~', $matches[1]),
                    'function' => 'lineElements'
                ],
            ];
        }
    }
}
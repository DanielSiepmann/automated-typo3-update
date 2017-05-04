<?php

/*
 * Copyright (C) 2017  Daniel Siepmann <coding@daniel-siepmann.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use Typo3Update\CodeSniffer\Tokenizers\Fluid\NodeTokenizer;;

class PHP_CodeSniffer_Tokenizers_FLUID
{
    /**
     * If TRUE, files that appear to be minified will not be processed.
     *
     * @var boolean
     */
    public $skipMinified = false;

    /**
     * Creates an array of tokens when given some TypoScript code.
     *
     * @param string $string  The string to tokenize.
     * @param string $eolChar The EOL character to use for splitting strings.
     *
     * @return array
     */
    public function tokenizeString($string, $eolChar = "\n")
    {
        $tokens = [];
        $parser = new TemplateParser();
        $renderingContext = new RenderingContext(new TemplateView());
        $viewHelperResolver = new ViewHelperResolver();
        // TODO: Configure ... somehow? Autodetect?!
        $viewHelperResolver->addNamespaces([
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'TYPO3\CMS\Fluid\ViewHelpers',
            ],
            'vendor' => ['something'],
        ]);
        $renderingContext->setViewHelperResolver($viewHelperResolver);
        $parser->setRenderingContext($renderingContext);

        $this->tokenizeNode($parser->parse($string)->getRootNode(), $tokens);

        return $tokens;
    }

    protected function tokenizeNode(NodeInterface $node, array &$tokens)
    {
        if (($node instanceof SyntaxTree\RootNode) === false) {
            $tokens[] = $this->convertNodeToToken($node);
        }

        foreach ($node->getChildNodes() as $node) {
            $this->tokenizeNode($node, $tokens);
        }
    }

    protected function convertNodeToToken(NodeInterface $node)
    {
        // TODO: How to get line (e.g. closing tag of ViewHelper on another line?!)
        // TODO: How to get the real content, to allow phpcs determine real length for column?!
        switch (get_class($node)) {
            case SyntaxTree\EscapingNode::class:
                return $this->convertNodeToToken($node->getNode());
            case SyntaxTree\ArrayNode::class:
                return 'ArrayNode';
            case SyntaxTree\BooleanNode::class:
                return 'BooleanNode';
            case SyntaxTree\NumericNode::class:
                return 'NumericNode';
            case SyntaxTree\ObjectAccessorNode::class:
                return (new NodeTokenizer\ObjectPathNode($node))->getToken();
            case SyntaxTree\ViewHelperNode::class:
                return (new NodeTokenizer\ViewHelperNode($node))->getToken();
            case SyntaxTree\TextNode::class:
                return (new NodeTokenizer\TextNode($node))->getToken();
            default:
                throw new \Exception('Unknown node.', 1493905030);
        }
    }

    /**
     * Allow the tokenizer to do additional processing if required.
     *
     * @param array  $tokens  The array of tokens to process.
     * @param string $eolChar The EOL character to use for splitting strings.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) We need to match the signature.
     */
    public function processAdditional(&$tokens, $eolChar)
    {
        var_dump($tokens);
        die;
        return;
    }
}

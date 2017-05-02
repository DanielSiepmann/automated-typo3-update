<?php
namespace Typo3Update\CodeSniffer\Tokenizers;

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

/**
 * Update tokens with fully qualified object identifier.
 */
class FQObjectIdentifier
{
    /**
     * Key used to save identifier to token.
     * @var string
     */
    const IDENTIFIER = 'fqObjectIdentifier';

    /**
     * The fully qualified object identifier, dot separated.
     *
     * @var array
     */
    protected $fqPath = [];

    /**
     * Current "real" depth, count of opening braces.
     * @var int
     */
    protected $depth = 0;

    /**
     * Add token as path segment.
     * @param array $token
     */
    public function addPathSegment(array &$token)
    {
        $this->syncPath();

        $path = [];
        foreach (explode('.', $token['content']) as $pathSegment) {
            $path[] = [
                'content' => $pathSegment,
                'depth' => $this->getDepth(),
            ];
        }
        $this->fqPath = array_merge($this->fqPath, $path);

        $this->addFqObjectIdentifier($token);
    }

    /**
     * Sync path with current depth.
     */
    public function syncPath()
    {
        $this->fqPath = array_filter(
            $this->fqPath,
            function ($pathSegment) {
                return $pathSegment['depth'] < $this->depth;
            }
        );
    }

    /**
     * Respect opening brace internal.
     */
    public function handleOpeningBrace()
    {
        ++$this->depth;
    }

    /**
     * Respect closing brace internal.
     */
    public function handleClosingBrace()
    {
        --$this->depth;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $path = '';
        foreach ($this->fqPath as $pathSegment) {
            $path .= '.' . $pathSegment['content'];
        }

        return substr($path, 1);
    }

    /**
     * @param array $token
     */
    protected function addFqObjectIdentifier(array &$token)
    {
        $token[static::IDENTIFIER] = $this->getPath();
    }
}

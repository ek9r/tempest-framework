<?php
// draft

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\Elements\GenericElement;

final readonly class ElseIfAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        $previous = $element->getPrevious();

        if (
            !$previous instanceof GenericElement
            !$previous->hasAttribute('if') || !$previous->hasAttribute('elseif')
        ) {
            throw new Exception('No valid if statement found in preceding element');
        }

        $condition = $previous->getAttribute('elseif');

        if ($condition) {
            return $element;
        } else {
            return new EmptyElement;
        }
    }
}

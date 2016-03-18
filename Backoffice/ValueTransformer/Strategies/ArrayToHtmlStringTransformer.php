<?php

namespace OpenOrchestra\Backoffice\ValueTransformer\Strategies;

use OpenOrchestra\Backoffice\ValueTransformer\ValueTransformerInterface;

/**
 * Class ArrayToHtmlStringTransformer
 */
class ArrayToHtmlStringTransformer implements ValueTransformerInterface
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function transform($data)
    {
        $output = $data;
        if (is_array($data)) {
            $output = "<ul>";
            foreach ($data as $item) {
                $output .= "<li>" . $this->transform($item) . "</li>";
            }
            $output .= "</ul>";
        }

        return $output;
    }

    /**
     * @param string $fieldType
     * @param mixed  $value
     *
     * @return bool
     */
    public function support($fieldType, $value)
    {
        return gettype($value) == 'array' && ($fieldType == 'choice' || $fieldType == 'orchestra_media');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'array';
    }
}

<?php
namespace Yituo\Core\Contracts;

/**
 * Interface MediaInterface.
 *
 */
interface MediaInterface extends MessageInterface
{
    /**
     * @return string
     */
    public function getMediaId(): string;
}

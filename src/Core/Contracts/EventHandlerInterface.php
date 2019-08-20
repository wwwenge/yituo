<?php
namespace Yituo\Core\Contracts;

/**
 * Interface EventHandlerInterface.
 *
 */
interface EventHandlerInterface
{
    /**
     * @param mixed $payload
     */
    public function handle($payload = null);
}

<?php

declare(strict_types=1);

namespace PHPFastCGI\FastCGIDaemon\Driver\Userland\Connection;

use PHPFastCGI\FastCGIDaemon\Driver\Userland\Exception\ConnectionException;

/**
 * The default implementation of the ConnectionInterface using stream socket
 * resources.
 */
final class StreamSocketConnection implements ConnectionInterface
{
    /**
     * @var bool
     */
    private $closed;

    /**
     * @var resource
     */
    private $socket;

    /**
     * Constructor.
     *
     * @param resource $socket The stream socket to wrap
     */
    public function __construct($socket)
    {
        $this->closed = false;

        $this->socket = $socket;
    }

    /**
     * Creates a formatted exception from the last error that occurecd.
     *
     * @param string $function The function that failed
     *
     * @return ConnectionException
     */
    protected function createExceptionFromLastError(string $function): ConnectionException
    {
        $this->close();

        return new ConnectionException($function.' failed');
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length): string
    {
        if ($this->isClosed()) {
            throw new ConnectionException('Connection has been closed');
        }

        if (0 === $length) {
            return '';
        }

        $buffer = @fread($this->socket, $length);

        if (empty($buffer)) {
            throw $this->createExceptionFromLastError('fread');
        }

        return $buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $buffer): void
    {
        if ($this->isClosed()) {
            throw new ConnectionException('Connection has been closed');
        }

        if (false == @fwrite($this->socket, $buffer) && !empty($buffer)) {
            throw $this->createExceptionFromLastError('fwrite');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (!$this->isClosed()) {
            fclose($this->socket);

            $this->socket = null;
            $this->closed = true;
        }
    }
}

<?php

namespace Tests\Support;

use RuntimeException;

class ApiServer
{
    private $process;
    private $log;
    private string $address;

    public function __construct()
    {
        // Reserve an available port instead of relying on a fixed test port.
        $socket = stream_socket_server('tcp://127.0.0.1:0');

        if ($socket === false) {
            throw new RuntimeException('Could not reserve a port for the API tests.');
        }

        $this->address = stream_socket_get_name($socket, false);
        fclose($socket);

        $root = dirname(__DIR__, 2);
        $this->log = tmpfile();
        $this->process = proc_open(
            [PHP_BINARY, '-S', $this->address, '-t', $root.'/public', $root.'/public/index.php'],
            [0 => ['pipe', 'r'], 1 => $this->log, 2 => $this->log],
            $pipes,
            $root,
        );

        if (!is_resource($this->process)) {
            fclose($this->log);
            throw new RuntimeException('Could not start the API test server.');
        }

        fclose($pipes[0]);

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $connection = @stream_socket_client('tcp://'.$this->address, timeout: 0.05);

            if ($connection !== false) {
                fclose($connection);

                return;
            }

            if (!proc_get_status($this->process)['running']) {
                break;
            }

            usleep(50_000);
        }

        rewind($this->log);
        $output = stream_get_contents($this->log);
        $this->stop();

        throw new RuntimeException('API test server did not start: '.$output);
    }

    /** @return array{status: int, headers: array, body: array} */
    public function request(string $method, string $path, string $body): array
    {
        $context = stream_context_create(['http' => [
            'method' => $method,
            'header' => "Content-Type: application/json\r\nConnection: close",
            'content' => $body,
            'ignore_errors' => true,
            'timeout' => 5,
        ]]);
        $stream = fopen('http://'.$this->address.$path, 'r', false, $context);

        if ($stream === false) {
            throw new RuntimeException('API request failed.');
        }

        $headers = stream_get_meta_data($stream)['wrapper_data'];
        $content = stream_get_contents($stream);
        fclose($stream);

        return [
            'status' => (int) explode(' ', $headers[0])[1],
            'headers' => $headers,
            'body' => json_decode($content, true, flags: JSON_THROW_ON_ERROR),
        ];
    }

    public function stop(): void
    {
        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
        }

        if (is_resource($this->log)) {
            fclose($this->log);
        }
    }
}

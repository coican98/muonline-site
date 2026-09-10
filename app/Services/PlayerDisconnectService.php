<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlayerDisconnectService
{
    public function disconnect(string $username): void
    {
        $username = trim($username);
        if ($username === '' || !preg_match('/^[A-Za-z0-9_]+$/', $username)) {
            throw new RuntimeException('Conta inválida.');
        }

        $account = DB::table('MEMB_INFO')
            ->where('memb___id', $username)
            ->first(['memb___id']);

        if (!$account) {
            throw new RuntimeException('Conta não encontrada.');
        }

        $host = (string) config('services.join_server.host');
        $port = (int) config('services.join_server.port');
        if ($host === '' || $port < 1 || $port > 65535) {
            throw new RuntimeException('JoinServer não configurado.');
        }

        if (!extension_loaded('sockets') || !function_exists('socket_create')) {
            throw new RuntimeException('A extensão sockets do PHP não está habilitada. Ative extension=sockets no php.ini e reinicie o servidor.');
        }

        $socket = @\socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new RuntimeException('A extensão sockets do PHP não está habilitada.');
        }

        try {
            \socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);
            if (!@\socket_connect($socket, $host, $port)) {
                throw new RuntimeException("Não foi possível conectar ao JoinServer {$host}:{$port}.");
            }

            $packetTemplate = config('services.join_server.team') === 'xteam'
                ? 'C10E30{ACCOUNT(10)}00'
                : 'C11AA00000000000{ACCOUNT(20)}0000000000000000';
            preg_match('/\{ACCOUNT\((\d+)\)\}/', $packetTemplate, $match);
            $accountHex = str_pad(bin2hex($username), ((int) $match[1]) * 2, '0', STR_PAD_RIGHT);
            $packet = hex2bin(str_replace('{ACCOUNT(' . $match[1] . ')}', $accountHex, $packetTemplate));

            if ($packet === false || @\socket_write($socket, $packet, strlen($packet)) === false) {
                throw new RuntimeException('Não foi possível enviar o comando de desconexão.');
            }
        } finally {
            \socket_close($socket);
        }

        DB::statement('EXEC WZ_DISCONNECT_MEMB ?', [$username]);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            usleep(500000);
            $status = DB::table('MEMB_STAT')
                ->where('memb___id', $username)
                ->value('ConnectStat');

            if ((int) $status === 0) {
                return;
            }
        }

        throw new RuntimeException('O JoinServer aceitou o comando, mas a conta ainda aparece online em MEMB_STAT. Verifique o pacote do JoinServer e a procedure WZ_DISCONNECT_MEMB do LouisMu.');
    }
}